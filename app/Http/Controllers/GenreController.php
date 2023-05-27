<?php

namespace App\Http\Controllers;

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
     * Filtering Genre.
     */

     public function genreFilter($allGenre)
     {
        $callRemoveSpace = new AnimeNameController;
        $allGenre = $callRemoveSpace->remove_white_space($allGenre);
        
        $arrGenre = explode(',' , $allGenre);
        $loop = count($arrGenre);
        
        for($i = 0; $i < $loop; $i++){
            $arrGenre[$i] = preg_replace('/\PL/u', '', $arrGenre[$i]);

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
    public function store($allGenre)
    {
        $allGenre = $this->genreFilter($allGenre);
        $allGenre = explode(',' , $allGenre);

        $getGenre = Genre::all()->pluck('genre_name');
        $availableGenre = $getGenre->values()->toArray();

        $loop = count($allGenre);
        $insertData = [];
        for($i = 0; $i < $loop; $i++){
            if(array_search($allGenre[$i] , $availableGenre) !== false){
                unset($allGenre[$i]);
            }else{
                $insertData[] = [
                    'genre_name' => $allGenre[$i],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        }
     
        //create Genre 
        DB::table('genres')->insert($insertData);
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
    public function update(Request $request, Genre $genre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        //
    }
}
