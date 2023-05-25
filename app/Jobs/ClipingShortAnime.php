<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use \FFMpeg\Coordinate\TimeCode;
use \FFMpeg\Filters\Video\ClipFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ClipingShortAnime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $short_data = [];
    public $tries = 3;//menentukan berapa kali mail akan coba dikirim jika terjadi kegagalan
    public $backoff = 10;//menentukan berapa lama waktu delay yang dibutuhkan untuk mengirin kembali mail saat gagal

    /**
     * Create a new job instance.
     */
    public function __construct($short_data)
    {
        $this->short_data = $short_data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
          //make short video
          $start = TimeCode::fromSeconds(900);
          $duration = TimeCode::fromSeconds(10);
          $short_name = 'clip-' . $this->short_data['video_name'];
          $short_directory = 'short-' . $this->short_data['directory'] . '/' . $short_name;
          $clipFilter = new ClipFilter($start , $duration);

          FFMpeg::fromDisk('public')
              ->open($this->short_data['disk'])
              ->addFilter($clipFilter)
              ->export()
              ->toDisk('short_clip')
              ->inFormat(new \FFMpeg\Format\Video\X264)
              ->save($short_directory);
          
        $short_url = Storage::disk('short_clip')->url('short_anime_clip/' . $short_directory);

      DB::table('anime_video_shorts')->insert([
             'anime_video_id' => $this->short_data['anime_video_id'],
            'short_name' => $short_name,
            'short_url' => $short_url
      ]);


     
    }
}
