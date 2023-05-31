<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;

class AllGenreController extends Controller
{
     /**
     * Show all genre anime to api response.
     */
    public function all_genre()
    {
        $fetchGenre = Genre::select('genre_name')->withCount('anime_name as anime_result')->orderBy('anime_result' , 'desc')->get();

        return response()->json([
            'status' => true,
            'message' => 'All anime genres',
            'genres' => $fetchGenre
        ] , 200);
    }

     /**
     * Show specific genre anime and their anaimes to api response.
     */
    public function show(Genre $genre_name)
    {
        $fetchGenre = $genre_name->load(['anime_name:anime_name,slug,rating,total_episode,studio,author,description']);

        return response()->json([
            'status' => true,
            'genre' => GenreResource::make($fetchGenre)
        ] , 200);
    }
}
