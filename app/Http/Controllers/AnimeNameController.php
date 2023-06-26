<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimeName\StoreAnimeNameRequest;
use App\Http\Requests\AnimeName\UpdateAnimeNameRequest;
use App\Models\AnimeName;
use App\Models\AnimeVideo;
use App\Observers\AnimeNameObserver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnimeNameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $animes = AnimeName::with(['genres:genre_name'])->withCount('anime_video as total_video');

        if($request->order_anime_name == 'vip'){
            $animes = $animes->orderBy('vip' , 'DESC')->paginate(10)->withQueryString();
        }elseif($request->order_anime_name == 'non-vip'){
            $animes = $animes->orderBy('vip' , 'ASC')->paginate(10)->withQueryString();
        }else{
            $animes = $animes->paginate(10)->withQueryString();
        }

        return view('anime.all-anime-view' , [
            'anime_name' => $animes
        ]);
    }

     /**
     * Display a trashed anime listing of the resource.
     */
    public function trashed_anime(Request $request)
    {

        $animes = AnimeName::with(['genres:genre_name' , 'anime_video' => function($query){
            $query->onlyTrashed()->select('id' , 'anime_name_id');
        }])->onlyTrashed();

        if($request->order_anime_name == 'vip'){
            $animes = $animes->orderBy('vip' , 'DESC')->paginate(10)->withQueryString();
        }elseif($request->order_anime_name == 'non-vip'){
            $animes = $animes->orderBy('vip' , 'ASC')->paginate(10)->withQueryString();
        }else{
            $animes = $animes->paginate(10)->withQueryString();
        }

        return view('anime.all-trashed-anime-view' , [
            'anime_name' => $animes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('anime.create');
    }

    /**
     * Show the form for creating zip a new resource.
     */
    public function create_zip()
    {
        return view('anime.create-zip');
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
           
            for($k = 0; $k < $loopStr; $k++){
                !in_array($arrAnime[$i][$k] , array_keys($arrMap)) ? $arrMap[$arrAnime[$i][$k]] = 1 : $arrMap[$arrAnime[$i][$k]] += 1;
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
        
        $validatedData = $request->validated();
        $request->vip ? $vip = 1 : $vip = 0;
        $request->released_date ? : $validatedData['released_date'] = 'Unknown';

     //call removed space and backslash
      $clearAnimeName = $this->remove_white_space($validatedData['anime_name']);

      //validasi anime name
      $findCloneAnimeName = AnimeName::withTrashed()->where('anime_name' , $clearAnimeName)->pluck('anime_name');
     
      if($findCloneAnimeName->values()->first()){
        if($from_zip_method != null){
            return 1;
        }

        return back()->with('found-clone' , 'clone-found');
      }

      $slug = $this->slug_maker($clearAnimeName);

       $newAnime = AnimeName::create([
        'anime_name' => $clearAnimeName,
        'slug' => $slug,
        'total_episode' => $validatedData['total_episode'],
        'author' => $validatedData['author'],
        'studio' => $validatedData['studio'],
        'description' => $validatedData['description'],
        'released_date' => $validatedData['released_date'],
        'vip' => $vip
       ]);

       //make genre
       $genreStore = new GenreController;
       $genreStore->store($request->genre , $newAnime);

       Storage::makeDirectory('F-' . $clearAnimeName);

       if($from_zip_method != null){
          return $newAnime;
       }

       return redirect()->route('anime-videos.create' , ['anime-name' => $clearAnimeName , 'anime-slug' => $slug]);

       
    }

     /**
     * Store a newly anime video from zip.
     */

     public function store_zip(StoreAnimeNameRequest $request)
     {
        $validate = $request->validate([
            'zip' => 'required|file|mimes:zip|max:900000'
        ]);
        
        $validatedData = $request->validated();

        DB::beginTransaction();
       $response = $this->store($request , "here is zip method");
        $clearAnimeName = $this->remove_white_space($validatedData['anime_name']);

        //call method extract zip
        if($request->file('zip') && $response !== 1){
           
            $zipMethod = new AnimeVideoController;
           $resultExtract = $zipMethod->extract_zip($request->file('zip') , $clearAnimeName , $response->id);

            $resultExtract === false ? (DB::rollBack()) . ( $info = "Fail Extracting Zip Invalid") : ( DB::commit()) . ($info = "Success Extracting");

        }else{
            DB::rollBack();
            return back()->with('info' , 'Duplikat, Anime Sudah Terdaftar');
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
            'anime_name' => $anime_name->load(['genres' => function ($query) {
                $query->select('genre_name' , 'id');
            }])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnimeNameRequest $request, AnimeName $anime_name)
    {
       $validatedData = $request->validated();
       $request->vip ? $vip = 1 : $vip = 0;
       $request->released_date ? : $validatedData['released_date'] = 'Unknown';

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
            'released_date' => $validatedData['released_date'],
            'vip' => $vip
       ]);
      
       //update pivot table
       //$validatedData['genre'] = array_filter($validatedData['genre']);//kode disamping sementara sampai UI dibuat
       //$anime_name->genres()->sync($validatedData['genre']);

        $genreStore = new GenreController;
        $genreStore->store($validatedData['genre'] , $anime_name , 'from anime update');

       return back();

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnimeName $anime_name)
    {
        AnimeName::destroy($anime_name->id);

        return back()->with('info' , 'Trashed');
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

        return back()->with('info','Success Untrash');
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

        return back()->with('info' , 'Permanent Deleted');
    }
    
}
