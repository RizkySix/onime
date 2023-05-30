<?php

namespace App\Http\Controllers;

use App\Http\Requests\Genre\UpdateGenreRequest;
use App\Models\Genre;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allGenre = Genre::withTrashed()->with(['anime_name:anime_name'])->latest()->get();
        return view('genre.view' , [
            'genres' => $allGenre
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
     * Filtering Genre.
     */

     public function genreFilter($allGenre)
     {
      
        $arrGenre = explode(',' , $allGenre);
        $loop = count($arrGenre);
        
        for($i = 0; $i < $loop; $i++){
            $arrGenre[$i] = preg_replace('/[^\pL\s]/u', '', $arrGenre[$i]); //menghapus karakter selain huruf dan spasi
            $arrGenre[$i] = preg_replace('/\s+/', ' ', trim($arrGenre[$i])); //menghapus spasi berlebih antar kata (menyisaskan satu spasi)

           if($arrGenre[$i] == ' ' || $arrGenre[$i] == null){
            unset($arrGenre[$i]);
           }
        }
    
        $result = implode(',' , $arrGenre);
 
        return $result;
        
     }

    /**
     * Store a newly created resource in storage.
     */
    public function store($allGenre , $newAnime)
    {
        $allGenre = $this->genreFilter($allGenre);
        $allGenre = explode(',' , $allGenre);

        $getGenre = Genre::withTrashed()->pluck('genre_name');
        $availableGenre = $getGenre->values()->toArray();

        $loop = count($allGenre);
        $insertData = [];
        for($i = 0; $i < $loop; $i++){
            if(array_search(ucwords(strtolower($allGenre[$i])) , $availableGenre) === false){
                $insertData[] = [
                    'genre_name' => ucwords(strtolower($allGenre[$i])),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        }
     
        //create Genre 
        DB::table('genres')->insert($insertData);

        //create relation
        $getGenreId = Genre::withTrashed()->whereIn('genre_name' , $allGenre)->pluck('id');
        $genreId = $getGenreId->values()->toArray();
       
        $newAnime->genres()->attach($genreId);
    }

    /**
     * Display the specified resource.
     */
    public function show(Genre $genre)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Genre $genre)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $validatedData = $request->validated();

        $newGenreName = $this->genreFilter($validatedData['genre_name']);
        $findGenre = Genre::where('genre_name' , $newGenreName)->where('id' , '!=' , $genre->id)->pluck('genre_name');

        $findGenre->all() == null ? ( Genre::where('id' , $genre->id)->update(['genre_name' => ucwords(strtolower($newGenreName))])) . ($info = 'Success Update') : $info = 'Duplicate Genre Name';
       
        return back()->with('found-genre' , $info);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        Genre::destroy($genre->id);
        
        return back();
    }

      /**
     * Restore the specified resource from storage.
     */
    public function restore($genre_name)
    {
        $softDeleted = Genre::onlyTrashed()->where('genre_name' , $genre_name)->first();

        if($softDeleted){
            $softDeleted->restore();
        }

        return back();
    }

     /**
     * Restore the specified resource from storage.
     */
    public function force_delete($genre_name)
    {
        $forceDelete = Genre::onlyTrashed()->where('genre_name' , $genre_name)->first();

        if($forceDelete){
            $forceDelete->forceDelete();

            //delete pivot table
            $forceDelete->anime_name()->detach();
        }

        return back();
    }
}
