<?php

namespace App\Observers;

use App\Models\AnimeVideo;
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
    public function updated(AnimeVideo $animeVideo , $folder , $newName): void
    {
         //for short video
         $shortOldPath = Storage::path('short_anime_clip/' . 'short-' . $folder . '/' . 'clip-' . $animeVideo->anime_eps);
         $shortNewPath = Storage::path('short_anime_clip/' . 'short-' . $folder . '/' . 'clip-' . $newName);
         rename($shortOldPath, $shortNewPath);

         DB::table('anime_video_shorts')
         ->where('anime_video_id' , $animeVideo->id)
         ->update(['short_url' => DB::raw("REGEXP_REPLACE(short_url, '" . 'clip-' . $animeVideo->anime_eps . "', '" . 
                                                                         'clip-' . $newName . "')")
          , 'short_name' => 'clip-' . $newName]);
    }

    /**
     * Handle the AnimeVideo "deleted" event.
     */
    public function deleted(AnimeVideo $animeVideo): void
    {
        //
    }

    /**
     * Handle the AnimeVideo "restored" event.
     */
    public function restored(AnimeVideo $animeVideo): void
    {
        //
    }

    /**
     * Handle the AnimeVideo "force deleted" event.
     */
    public function forceDeleted(AnimeVideo $animeVideo): void
    {
        //
    }
}
