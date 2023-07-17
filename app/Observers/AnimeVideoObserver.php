<?php

namespace App\Observers;

use App\Models\AnimeVideo;
use App\Models\AnimeVideoShort;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnimeVideoObserver
{
    /**
     * Handle the AnimeVideo "created" event.
     */
    public function created(AnimeVideo $animeVideo): void
    {
        //
    }

    /**
     * Handle the AnimeVideo "updated" event.
     */
    public function updated(AnimeVideo $animeVideo): void
    {
        
    }
    
    /**
     * Handle the AnimeVideo for no "updated" event.
     */

     public function no_event_updated(AnimeVideo $animeVideo , $folder , $newName): void
     {
          //mengatur ulang url dan storage dari relasi AnimevVideo
          if(Storage::exists('short_anime_clip/' . 'short-' . $folder . '/' . 'clip-' . $animeVideo->anime_eps)){
            $shortOldPath = Storage::path('short_anime_clip/' . 'short-' . $folder . '/' . 'clip-' . $animeVideo->anime_eps);
            $shortNewPath = Storage::path('short_anime_clip/' . 'short-' . $folder . '/' . 'clip-' . $newName);
            rename($shortOldPath, $shortNewPath);
   
            DB::table('anime_video_shorts')
            ->where('anime_video_id' , $animeVideo->id)
            ->update(['short_url' => DB::raw("REGEXP_REPLACE(short_url, '" . 'clip-' . $animeVideo->anime_eps . "', '" . 
                                                                            'clip-' . $newName . "')")
             , 'short_name' => 'clip-' . $newName]);
          }
          
     }

    /**
     * Handle the AnimeVideo "deleted" event.
     */
    public function deleted(AnimeVideo $animeVideo): void
    {
      
    }

    /**
     * Handle the AnimeVideo "restored" event.
     */
    public function restored(AnimeVideo $animeVideo): void
    {
       
        $animeVideo->anime_short()->restore();

    }

    /**
     * Handle the AnimeVideo "force deleted" event.
     */
    public function forceDeleted(AnimeVideo $animeVideo): void
    {
        $animeVideo->anime_short()->forceDelete();

        //remove file video
        Storage::delete('F-' . $animeVideo->anime_name->anime_name . '/' . $animeVideo->anime_eps);
        Storage::disk('short_clip')->delete('short-' . $animeVideo->anime_name->anime_name . '/' . 'clip-' . $animeVideo->anime_eps);
    }
}
