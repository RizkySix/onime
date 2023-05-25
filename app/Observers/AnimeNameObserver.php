<?php

namespace App\Observers;

use App\Models\AnimeName;
use App\Models\AnimeVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnimeNameObserver
{
    /**
     * Handle the AnimeName "created" event.
     */
    public function created(AnimeName $animeName): void
    {
        //
    }

    /**
     * Handle the AnimeName "updated" event.
     */
    public function updated(AnimeName $animeName , $oldName , $newName): void
    {
         DB::table('anime_videos')
            ->where('anime_name_id' , $animeName->id)
            ->update(['video_url' => DB::raw("REGEXP_REPLACE(video_url, '" . 'F-' . $oldName . "', '" . 'F-' . $newName . "')")
            ]);

            DB::table('anime_video_shorts')
            ->where('short_url', 'REGEXP', 'short-' . $oldName)
            ->update(['short_url' => DB::raw("REGEXP_REPLACE(short_url, '" . 'short-' . $oldName . "', '" . 'short-' . $newName . "')")
                ]);
        
                $oldPath = Storage::path('short_anime_clip/' . 'short-' . $oldName);
                $newPath = Storage::path('short_anime_clip/' . 'short-' . $newName);
                rename($oldPath, $newPath);
    }

    /**
     * Handle the AnimeName "deleted" event.
     */
    public function deleted(AnimeName $animeName): void
    {
        //
    }

    /**
     * Handle the AnimeName "restored" event.
     */
    public function restored(AnimeName $animeName): void
    {
        //
    }

    /**
     * Handle the AnimeName "force deleted" event.
     */
    public function forceDeleted(AnimeName $animeName): void
    {
        //
    }
}
