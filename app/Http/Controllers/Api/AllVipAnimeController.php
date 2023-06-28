<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GetAllAnimeResource;
use App\Models\AnimeName;
use App\Models\AnimeRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AllVipAnimeController extends Controller
{
    /**
     * Show all vip anime to api response.
     */
    public function all_vip_anime(Request $request)
    {

        if($request->user()->tokenCan('vip-token') || $request->user()->tokenCan('super-vip-token')){

         //query awal
         $fetchAnime = AnimeName::with(['genres:genre_name' , 'rating:rating,anime_name_id'])
         ->select('id', 'anime_name' , 'slug' , 'total_episode'  , 'studio' , 'author' , 'description' , 'released_date' , 'vip')
         ->where('vip' , true);
 
         //memberikan response error jika permintaan paginasi lebih dari 10 page
         if($request->page > 10){
             return response()->json([
                 'status' => false,
                 'max_page' => 10,
                 'message' => "Can't reach page " . $request->page . ' maximum page are 10' 
             ] , 404);
         }
         
        //jika ada rating dan find_anime
       if($request->rating == 'true' && $request->find_anime){
            $queryAnime = $fetchAnime->where('anime_name' , 'LIKE' , '%' . $request->find_anime . '%')
            ->orderByDesc(AnimeRating::select('rating')->whereColumn('anime_ratings.anime_name_id' , 'anime_names.id'));

             $request->page ?
                $allAnime = Cache::remember('all-anime-vip-find-rating-page' . $request->find_anime . $request->page , 60*60*24 , function() use($queryAnime) {
                    return $queryAnime->simplePaginate(10);
                })  :
                $allAnime = Cache::remember('all-anime-vip-find-rating' . $request->find_anime , 60*60*24 , function() use($queryAnime) {
                    return $queryAnime->simplePaginate(10);
                });
        
        }elseif($request->rating == 'true'){ //jika dicari rating tertinggi
                $queryAnime = $fetchAnime->orderByDesc(AnimeRating::select('rating')->whereColumn('anime_ratings.anime_name_id' , 'anime_names.id'));

                $request->page ?
                    $allAnime = Cache::remember('all-anime-vip-rating-page' . $request->page , 60*60*24 , function() use($queryAnime) {
                        return $queryAnime->simplePaginate(10);
                    })  :
                    $allAnime = Cache::remember('all-anime-vip-rating' , 60*60*24 , function() use($queryAnime) {
                        return $queryAnime->simplePaginate(10);
                    });
        }elseif($request->find_anime){ // jika dicari dari nama anime
                $queryAnime = $fetchAnime->where('anime_name' , 'LIKE' , '%' . $request->find_anime . '%')
                ->latest();

            $request->page ?
                    $allAnime = Cache::remember('all-anime-vip-find-page' . $request->find_anime . $request->page , 60*60*24 , function() use($queryAnime) {
                        return $queryAnime->simplePaginate(10);
                    })  :
                    $allAnime = Cache::remember('all-anime-vip-find' . $request->find_anime , 60*60*24 , function() use($queryAnime) {
                        return $queryAnime->simplePaginate(10);
            });
            }else{ //jika tidak ada query parameter
                $queryAnime = $fetchAnime->latest();
                
                $request->page ?
                    $allAnime =  Cache::remember('all-anime-vip-no-query-page' . $request->page , 60*60*24 , function () use($queryAnime) {
                        return $queryAnime->simplePaginate(10);
                    }) :
                    $allAnime =  Cache::remember('all-anime-vip-no-query', 60*60*24 , function () use($queryAnime) {
                        return $queryAnime->simplePaginate(10);
                    });
                
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

         //exception
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Only VIP user can access this endpoint'
                ] , 403) ;
            }
    }
}
