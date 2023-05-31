<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GetAllAnimeResource;
use App\Models\AnimeName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllAnimeController extends Controller
{
     /**
     * Show all anime or specific anime to api response.
     */
    public function get_all(Request $request)
    {

        //query awal
        $fetchAnime = AnimeName::with(['genres:genre_name'])
        ->select('id', 'anime_name' , 'slug' , 'total_episode' , 'rating' , 'studio' , 'author' , 'description')
        ->when($request->rating == true , function($query) {
            $query->orderBy('rating' , 'DESC');
        }, function($query) {
            $query->latest();
        });

        //memberikan response error jika permintaan paginasi lebih dari 10 page
        if($request->page > 10){
            return response()->json([
                'status' => false,
                'max_page' => 10,
                'message' => "Can't reach page " . $request->page . ' maximum page are 10' 
            ] , 404);
        }
        
       if(!$request->find_anime){
       
         $allAnime = $fetchAnime->simplePaginate(10);
       }

        //jika ada request spesifik mencari nama anime
        if($request->find_anime){
            
         $allAnime = $fetchAnime->where('anime_name' , 'LIKE' , '%' . $request->find_anime . '%')  
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

     /**
     * Show single anime by retrivieng slug and send to api response.
     */
     public function show(AnimeName $anime_name)
     {
        //query awal
        $anime_name->load(['anime_video' => function($query){
            $query->with(['anime_short' => function($shortQuery){
                $shortQuery->select('anime_video_id' , 'short_name' , 'duration' , 'short_url');
            }])->select('anime_name_id' , 'anime_eps' , 'resolution' , 'duration' , 'video_format' , 'video_url' , 'id');
        } , 'genres:genre_name,id']);

        //membuat rekomendasi anime sejenis
        $genreId = [];
        foreach($anime_name->genres as $genre){
            $genreId[] = $genre->id;
        }

       $relatedAnimes = AnimeName::with('genres:genre_name')
       ->select('id', 'anime_name' , 'slug' , 'total_episode' , 'rating' , 'studio' , 'author' , 'description')->whereHas('genres' , function($query) use($genreId , $anime_name) {
            $query->whereIn('genres_id' , $genreId)->where('anime_names_id' , '!=' , $anime_name->id);
       })
       ->take(5)
       ->get();  

        return response()->json([
            'status' => true,
            'message' => 'On single anime page',
            'anime' => GetAllAnimeResource::make($anime_name),
            'related_animes' => GetAllAnimeResource::collection($relatedAnimes),
        ] , 200);

     }
}
