<?php

namespace App\Observers;

use App\Models\AnimeName;
use App\Models\AnimeRating;
use App\Models\AnimeVideo;
use App\Models\AnimeVideoShort;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnimeNameObserver
{
  public function __construct()
  {
     //forget semua cache -> Sementara menggunakan cache database, otw belajar redis
     DB::table('cache')->where('key' , 'LIKE'  , '%onime_cache_all-anime%' )->delete();
     
  }

    /**
     * Handle the AnimeName "created" event.
     */
    public function created(AnimeName $animeName): void
    {
        //buat rating untuk anim baru
        AnimeRating::create(['anime_name_id' => $animeName->id]);

    }

    /**
     * Handle the AnimeName "updated" event.
     */
    public function updated(AnimeName $animeName): void
    {
      
    }

     /**
     * Handle the AnimeName for no "updated" event.
     */

     public function no_event_updated(AnimeName $animeName , $oldName , $newName): void
     {
      
        //mengatur ulang url video dan storage relasi model AnimeName

        DB::table('anime_videos')
        ->where('anime_name_id' , $animeName->id)
        ->update(['video_url' => DB::raw("REGEXP_REPLACE(video_url, '" . 'F-' . $oldName . "', '" . 'F-' . $newName . "')")
        ]);

         if(Storage::exists('short_anime_clip/' . 'short-' . $oldName)){
          DB::table('anime_video_shorts')
          ->where('short_url', 'REGEXP', 'short-' . $oldName)
          ->update(['short_url' => DB::raw("REGEXP_REPLACE(short_url, '" . 'short-' . $oldName . "', '" . 'short-' . $newName . "')")
              ]);
      
              $oldPath = Storage::path('short_anime_clip/' . 'short-' . $oldName);
              $newPath = Storage::path('short_anime_clip/' . 'short-' . $newName);
              rename($oldPath, $newPath);
         }
     }

    /**
     * Handle the AnimeName "deleted" event.
     */
    public function deleted(AnimeName $animeName): void
    {
       
    }

    /**
     * Handle the AnimeName "restored" event.
     */
    public function restored(AnimeName $animeName): void
    {
        $getAllData = AnimeVideo::onlyTrashed()->where('anime_name_id' , $animeName->id)->pluck('id');
        
       AnimeVideo::onlyTrashed()->where('anime_name_id' , $animeName->id)->update(['deleted_at' => null]);

       //restore short video
       AnimeVideoShort::onlyTrashed()->whereIn('anime_video_id' , $getAllData->values()->all())->update(['deleted_at' => null]);
        
    
      
    }

    /**
     * Handle the AnimeName "force deleted" event.
     */
    public function forceDeleted(AnimeName $animeName): void
    {
        $getAllData = AnimeVideo::onlyTrashed()->where('anime_name_id' , $animeName->id)->pluck('id');
        
        $animeName->anime_video()->forceDelete();//force delete semua child relasi dari AnimeVideo

       //delete short video
      AnimeVideoShort::onlyTrashed()->whereIn('anime_video_id' , $getAllData->values()->all())->forceDelete();

        //delete pivot table
        $animeName->genres()->detach();

        //delete rating
        $animeName->rating()->delete();

      //remove directory
      Storage::deleteDirectory('F-' . $animeName->anime_name);
      Storage::disk('short_clip')->deleteDirectory('short-' . $animeName->anime_name);
    }
}
