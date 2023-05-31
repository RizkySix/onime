<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GetAllAnimeResource;
use App\Models\AnimeName;
use Illuminate\Http\Request;

class AllAnimeController extends Controller
{
    public function get_all(Request $request)
    {

        //query awal
        $fetchAnime = AnimeName::with(['anime_video' => function($query){
            $query->with(['anime_short' => function($shortQuery){
                $shortQuery->select('anime_video_id' , 'short_name' , 'duration' , 'short_url');
            }])->select('anime_name_id' , 'anime_eps' , 'resolution' , 'duration' , 'video_format' , 'video_url' , 'id');
        } , 'genres:genre_name']);

        //memberikan response error jika permintaan paginasi lebih dari 10 page
        if($request->page > 10){
            return response()->json([
                'status' => false,
                'max_page' => 10,
                'message' => "Cant't reach page " . $request->page . ' maximun page are 10' 
            ] , 404);
        }
        
       if(!$request->anime_name){
       
         $allAnime = $fetchAnime->orderBy('rating' , 'DESC')->simplePaginate(10);
       }

        //jika ada request spesifik mencari nama anime
        if($request->anime_name){
            
         $allAnime = $fetchAnime->where('anime_name' , 'LIKE' , '%' . $request->anime_name . '%')
                                ->orderBy('rating' , 'DESC')
                                ->simplePaginate(10);
        }

        return response()->json([
            'status' => true,
            'total_result_found' => $allAnime->count(),
            'paginate' => [
                        'result_limit' => 100,
                        'page' => 10,
                        'current_page' => $allAnime->currentPage(),
                        'data_per_page' => $allAnime->perPage(),
            ],
            'animes' => GetAllAnimeResource::collection($allAnime)
        ] , 200);
    }
}
