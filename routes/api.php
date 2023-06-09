<?php

use App\Http\Controllers\Api\AllAnimeController;
use App\Http\Controllers\Api\AllGenreController;
use App\Http\Controllers\Api\AllVipAnimeController;
use App\Http\Controllers\Api\AnimeListController;
use App\Http\Controllers\PricingOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
  Route::middleware(['normal_token_limiter'])->group(function () {

    Route::get('/animes' , [AllAnimeController::class , 'get_all'])->name('api.all-anime');
    Route::get('/animes/{anime_name}' , [AllAnimeController::class , 'show'])->name('api.show-anime');

    Route::get('/genres' , [AllGenreController::class , 'all_genre'])->name('api.all-genre');
    Route::get('/genres/{genre_name}' , [AllGenreController::class , 'show'])->name('api.show-genre');

    Route::get('/anime-list' , [AnimeListController::class , 'anime_list'])->name('api.anime-list');

  });

    //vip
    Route::get('/animes-vip' , [AllVipAnimeController::class , 'all_vip_anime'])->name('api.all-vip-anime');

    //rating
    Route::put('/animes/{anime_name}/rating' , [AllAnimeController::class , 'rating'])->name('api.rating-anime');
});

//webhook for midtrans
    Route::post('/handling-payment-midtrans/webhook' , [PricingOrderController::class , 'webhook'])->name('api.webhook');