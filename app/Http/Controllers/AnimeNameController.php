<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimeName\StoreAnimeNameRequest;
use App\Http\Requests\AnimeName\UpdateAnimeNameRequest;
use App\Models\AnimeName;
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
        //
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
            if($arrAnime[$i] == null){
                unset($arrAnime[$i]);
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
    
     //call slug maker
      $clearAnimeName = $this->remove_white_space($request->anime_name);

      //validasi anime name
      $findCloneAnimeName = AnimeName::where('anime_name' , $clearAnimeName)->pluck('anime_name');
     
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

       Storage::makeDirectory($clearAnimeName);

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
    public function show(AnimeName $animeName)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnimeName $animeName)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnimeNameRequest $request, AnimeName $animeName)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnimeName $animeName)
    {
        //
    }
}
