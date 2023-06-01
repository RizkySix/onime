<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnimeName;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnimeListController extends Controller
{
     /**
     * Show anime-list within only their anime_name and slug to api response.
     */
    public function anime_list(Request $request)
    {
        $fetchAnime = AnimeName::select('anime_name' , 'slug')->when(!$request->user()->tokenCan('vip-token') , function($query) {
            $query->where('vip' ,  false);
        });

        //fetch anime yang dipublish 30 hari kebelakang
        $month = Carbon::now()->subDays(30);

        $request->list && strlen($request->list) == 1 ? $allAnime = $fetchAnime->where('anime_name' , 'LIKE' , $request->list . '%')->latest()->get() : $allAnime = $fetchAnime->where('created_at' , '>=' , $month)->get();

        return response([
            'status' => true,
            'message' => 'Find anime list by send ABCD or so on',
            'animes' => $allAnime
        ], 200);
        
    }
}
