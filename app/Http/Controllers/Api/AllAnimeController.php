<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnimeName;
use Illuminate\Http\Request;

class AllAnimeController extends Controller
{
    public function get_all()
    {
        $allAnime = AnimeName::with(['anime_video' , 'genres'])->orderBy('rating' , 'DESC')->get();
        return response()->json($allAnime , 200);
    }
}
