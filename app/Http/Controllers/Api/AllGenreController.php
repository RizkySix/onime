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
    public function show(Genre $genre_name , Request $request)
    {
        $fetchGenre = $genre_name->load(['anime_name' => function($query) use($request) {
            $query->select('id', 'anime_name' , 'slug' , 'total_episode' , 'rating' , 'studio' , 'author' , 'description' , 'released_date' , 'vip')->when(!$request->user()->tokenCan('vip-token') , function($subQuery) {
                $subQuery->where('vip' , false);
            });
        }]);

        return response()->json([
            'status' => true,
            'genre' => GenreResource::make($fetchGenre)
        ] , 200);
    }
}
