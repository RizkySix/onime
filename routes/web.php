<?php

use App\Http\Controllers\AnimeNameController;
use App\Http\Controllers\AnimeVideoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'otp_verified'])->group(function () {
    Route::get('/dashboard' , [DashboardController::class , 'view'])->name('dashboard');
    Route::post('/generate-token' , [DashboardController::class , 'generate_token'])->name('token-maker');
   Route::resource('/anime-name' , AnimeNameController::class);
   Route::post('/anime-name-zip' , [AnimeNameController::class , 'store_zip'])->name('anime-name.store.zip');
   Route::post('/anime-restore/{slug}' , [AnimeNameController::class , 'restore'])->name('anime-restore');
   Route::post('/anime-force-delete/{slug}' , [AnimeNameController::class , 'force_delete'])->name('anime-force-delete');
 
   Route::resource('/anime-videos' , AnimeVideoController::class);
   Route::post('/anime-videos-restore/{id}' , [AnimeVideoController::class , 'restore'])->name('anime-videos-restore');
   Route::post('/anime-videos-force-delete/{id}' , [AnimeVideoController::class , 'force_delete'])->name('anime-videos-force-delete');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
