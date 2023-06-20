<?php

use App\Http\Controllers\AnimeNameController;
use App\Http\Controllers\AnimeVideoController;
use App\Http\Controllers\ApiDocumentationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\PricingOrderController;
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

    //admin
Route::middleware(['admin'])->group(function () {
    Route::get('/dashboard-admin' , [DashboardController::class , 'view_admin'])->name('dashboard.admin');
    
    Route::resource('/anime-name' , AnimeNameController::class);
    Route::post('/anime-name-zip' , [AnimeNameController::class , 'store_zip'])->name('anime-name.store.zip');
    Route::post('/anime-restore/{slug}' , [AnimeNameController::class , 'restore'])->name('anime-restore');
    Route::post('/anime-force-delete/{slug}' , [AnimeNameController::class , 'force_delete'])->name('anime-force-delete');
  
    Route::resource('/anime-videos' , AnimeVideoController::class);
    Route::post('/anime-videos-restore/{id}' , [AnimeVideoController::class , 'restore'])->name('anime-videos-restore');
    Route::post('/anime-videos-force-delete/{id}' , [AnimeVideoController::class , 'force_delete'])->name('anime-videos-force-delete');
 
    Route::resource('/genre', GenreController::class);
    Route::post('/genre-restore/{genre_name}' , [GenreController::class , 'restore'])->name('genre-restore');
    Route::post('/genre-force-delete/{genre_name}' , [GenreController::class , 'force_delete'])->name('genre-force-delete');
});

    Route::resource('/pricing', PricingController::class);
    Route::post('/pricing-restore/{pricing_name}', [PricingController::class , 'restore'])->name('pricing.restore')->middleware('can:restore,App\Models\Pricing');
    Route::post('/pricing-force-delete/{pricing_name}', [PricingController::class , 'force_delete'])->name('pricing.force-delete')->middleware('can:forceDelete,App\Models\Pricing');


   //Payment
Route::middleware(['customer'])->group(function() {
    Route::get('/dashboard' , [DashboardController::class , 'view'])->name('dashboard');
    Route::post('/generate-token' , [DashboardController::class , 'generate_token'])->name('token-maker');

    Route::get('/transaction-view/{pricing_name}' , [PricingOrderController::class , 'transaction_view'])->name('transaction-view');
   Route::post('/transaction/{pricing_name}' , [PricingOrderController::class , 'transaction'])->name('transaction');
   Route::post('/cancel-order/{pricing_order}' , [PricingOrderController::class , 'cancel_order'])->name('cancel-order');
   Route::get('/change-payment-method/{pricing_order}/edit' , [PricingOrderController::class , 'change_payment_method_view'])->name('change-payment-method-view');
   Route::put('/change-payment-method/{pricing_order}' , [PricingOrderController::class , 'change_payment_method'])->name('change-payment-method');
});

});



//route API documentaion
Route::prefix('doc')->group(function () {
    Route::get('/get-all-anime' , [ApiDocumentationController::class , 'doc_get_all'])->name('doc.get-all');
    Route::get('/integration-guide' , [ApiDocumentationController::class , 'doc_guide'])->name('doc.guide');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
