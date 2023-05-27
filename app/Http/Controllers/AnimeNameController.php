<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimeName\StoreAnimeNameRequest;
use App\Http\Requests\AnimeName\UpdateAnimeNameRequest;
use App\Models\AnimeName;
use App\Models\AnimeVideo;
use App\Observers\AnimeNameObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnimeNameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('anime.all-anime-view' , [
            'anime_name' => AnimeName::withTrashed()->with(['anime_video:video_url,anime_name_id'])->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

      /**
     * Slug maker .
     */

     public function slug_maker($animeName)
     {
        $slug = explode(' ' , $animeName);
        $slug = strtolower(implode('-' , $slug));
    
        $checkSlug = AnimeName::where('slug' , $slug)->pluck('id');
        if($checkSlug->values()->first() != null){
            $animeName = $animeName . ' ' . Str::random(5);
            return $this->slug_maker($animeName);
        }

        return $slug;
       
     }
     
       /**
     * Remove White Space .
     */

     public function remove_white_space($animeName)
     {
        $arrAnime = explode(' ' , $animeName);
        $loop = count($arrAnime);

        for($i = 0; $i < $loop; $i++){
            //hashmap
            $arrMap = [];
            $loopStr = strlen($arrAnime[$i]);
            $plus = 1;
            for($k = 0; $k < $loopStr; $k++){
                $arrMap[$arrAnime[$i][$k]] = $plus;
                $plus++;
            }

            if($arrAnime[$i] == null || count($arrMap) === 1 && isset($arrMap['/'])){
                unset($arrAnime[$i]);
            }elseif(strpos($arrAnime[$i] , '/') !== false){
                $arrAnime[$i] = str_replace('/', '', $arrAnime[$i]);
            }

            
        }

        $result = implode(' ' , $arrAnime);
        
        return $result;

     }  
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnimeNameRequest $request , $from_zip_method = null)
    {
    
     //call removed space and backslash
      $clearAnimeName = $this->remove_white_space($request->anime_name);

      //validasi anime name
      $findCloneAnimeName = AnimeName::withTrashed()->where('anime_name' , $clearAnimeName)->pluck('anime_name');
     
      if($findCloneAnimeName->values()->first()){
        if($from_zip_method !== null){
            return 1;
        }

        return back()->with('found-clone' , 'clone-found');
      }

      $slug = $this->slug_maker($clearAnimeName);

       $newAnime = AnimeName::create([
        'anime_name' => $clearAnimeName,
        'slug' => $slug,
        'total_episode' => $request->total_episode,
        'author' => $request->author,
        'studio' => $request->studio,
        'description' => $request->description,
       ]);

       //make genre
       $genreStore = new GenreController;
       $genreStore->store($request->genre);

       Storage::makeDirectory('F-' . $clearAnimeName);

       if($from_zip_method !== null){
          return $newAnime;
       }

       return redirect()->route('anime-videos.create' , ['anime-name' => $slug]);

       
    }

     /**
     * Store a newly anime video from zip.
     */

     public function store_zip(StoreAnimeNameRequest $request)
     {
        $validatedData = $request->validate([
            'zip' => 'required|file|mimes:zip'
        ]);

        DB::beginTransaction();
       $response = $this->store($request , "here is zip method");
        $clearAnimeName = $this->remove_white_space($request->anime_name);

        //call method extract zip
        if($request->file('zip') && $response !== 1){
           
            $zipMethod = new AnimeVideoController;
           $resultExtract = $zipMethod->extract_zip($request->file('zip') , $clearAnimeName , $response->id);

            $resultExtract === false ? (DB::rollBack()) . ( $info = "Fail Extracting") : ( DB::commit()) . ($info = "Success Extracting");

        }else{
            DB::rollBack();
            return back();
        }

        
        return back()->with('info' , $info);
      


     }

    /**
     * Display the specified resource.
     */
    public function show(AnimeName $anime_name)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnimeName $anime_name)
    {
        return view('anime.edit-anime' , [
            'anime_name' => $anime_name->load(['anime_video' => function ($query) {
                $query->withTrashed()->select('anime_eps', 'id', 'anime_name_id' , 'deleted_at');
            }])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnimeNameRequest $request, AnimeName $anime_name)
    {
       $validatedData = $request->validated();
        
       $slug = $anime_name->slug;
       $clearAnimeName = $anime_name->anime_name;
       if($validatedData['anime_name'] != $anime_name->anime_name){
            $clearAnimeName = $this->remove_white_space($validatedData['anime_name']);
            $slug = $this->slug_maker($clearAnimeName);


            $oldPath = Storage::path('F-' . $anime_name->anime_name);
            $newPath = Storage::path('F-' . $clearAnimeName);
            rename($oldPath, $newPath);

            $AnimeNameObserver = new AnimeNameObserver;
            $AnimeNameObserver->no_event_updated($anime_name , $anime_name->anime_name , $clearAnimeName);
       }
       
       $updatedAnime = AnimeName::where('id' , $anime_name->id)->update([
            'anime_name' =>  $clearAnimeName,
            'slug' => $slug,
            'total_episode' => $validatedData['total_episode'],
            'studio' => $validatedData['studio'],
            'author' => $validatedData['author'],
            'description' => $validatedData['description'],
       ]);
      

       return redirect()->route('anime-name.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnimeName $anime_name)
    {
        AnimeName::destroy($anime_name->id);

        return back();
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($slug)
    {
       $softDeleted = AnimeName::onlyTrashed()->where('slug' , $slug)->first();

        if($softDeleted){
            $softDeleted->restore();
        }

        return back();
    }

     /**
     * Force delete the specified resource from storage.
     */

    public function force_delete($slug)
    {
        $forceDelete = AnimeName::onlyTrashed()->where('slug' , $slug)->first();

        if($forceDelete){
            $forceDelete->forceDelete();
        }

        return back();
    }
    
}
