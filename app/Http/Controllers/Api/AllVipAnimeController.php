<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GetAllAnimeResource;
use App\Models\AnimeName;
use Illuminate\Http\Request;

class AllVipAnimeController extends Controller
{
    /**
     * Show all vip anime to api response.
     */
    public function all_vip_anime(Request $request)
    {

        if($request->user()->tokenCan('vip-token')){

         //query awal
         $fetchAnime = AnimeName::with(['genres:genre_name'])
         ->select('id', 'anime_name' , 'slug' , 'total_episode' , 'rating' , 'studio' , 'author' , 'description' , 'released_date' , 'vip')
         ->where('vip' , true)
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

            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Only VIP user can access this Url'
                ] , 401) ;
            }
    }
}
