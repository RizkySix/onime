<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimeVideo\StoreAnimeVideoRequest;
use App\Http\Requests\AnimeVideo\UpdateAnimeVideoRequest;
use App\Jobs\ClipingShortAnime;
use App\Models\AnimeName;
use App\Models\AnimeVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ZipArchive;
use \FFMpeg\Coordinate\TimeCode;
use \FFMpeg\Filters\Video\ClipFilter;

class AnimeVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('anime-videos');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnimeVideoRequest $request)
    {
        $video_detail = $request->file('video');
       
        if($video_detail){

            //find data anime
            $findAnime = AnimeName::where('slug' , $request->anime_name_slug)->pluck('id' , 'anime_name');
            $animeName = $findAnime->keys()->first();
            $newAnime = $findAnime->values()->first();

            if ($animeName == null) {
                return back()->with('no-match', 'not match');
            }

            $directory = 'F-' . $animeName;

            $ffprobe = FFProbe::create([
                'ffmpeg.binaries' => env('FFMPEG_BINARIES'),
                'ffprobe.binaries' => env('FFPROBE_BINARIES'),
            ]);
            
            $videoPath = $video_detail->getPathname();
            
            $videoInfo = $ffprobe->streams($videoPath)->videos()->first();
            
            $video_name = $video_detail->getClientOriginalName();
            $video_duration = floor($videoInfo->get('duration') / 60);
            
          $disk =  Storage::disk('public')->putFileAs($directory , $video_detail , $video_name);
            $url = Storage::disk('public')->url($directory . '/' . $video_name);
    
         $newAnimeVideo = AnimeVideo::create([
                'anime_name_id' => $newAnime,
                'anime_eps' => $video_name,
                'resolution' => $videoInfo->get('height'),
                'duration' => $video_duration,
                'video_format' => $video_detail->getClientOriginalExtension(),
                'video_url' => $url
            ]);


            //call a job to make short video
            $short_data = [
                'disk' => $disk,
                'directory' => $animeName,
                'video_name' => $video_name,
                'anime_video_id' => $newAnimeVideo->id
            ];

            dispatch(new ClipingShortAnime($short_data));

            return back();

          
        }else{
            return back()->with('no-match', 'not match');
        }
    }

     /**
     * Store extracted zip.
     */

    public function extract_zip($zip , $directory , $newAnime)
    {
        $zipDetail = new ZipArchive;
        $shortDirectory = $directory;
        $directory = 'F-' . $directory;

        if($zipDetail->open($zip) === true ){
           $numFiles = $zipDetail->numFiles;

           //make DB transaction
           //5 = jika terjadi deadlock atau gagal akan dicoba kembali sebanyak 5x;
           DB::beginTransaction(5);

           
           Storage::createDirectory('tmp-dir');
           $zipDetail->extractTo('storage/tmp-dir');
           
           for($i = 0; $i < $numFiles; $i++){
            $idx = $zipDetail->statIndex($i);
            $full_name = $idx['name'];
            $array_name = explode('/' , $full_name);
            $video_name = end($array_name);

            //validasi extensi file
            $valid_format = ['mp4' , 'mov' , 'avi' , 'mkv'];
            $video_format = explode('.' , $video_name);
            $real_format = end($video_format);

            if(count($array_name) > 2){
                DB::rollBack();
                Storage::deleteDirectory('/tmp-dir');
                Storage::deleteDirectory($directory);
                Storage::disk('short_clip')->deleteDirectory('short-' . $shortDirectory);
                return false; /* response(['wrong-format' => 'Format File Not Allowed']); */
            }

            if(array_search($real_format , $valid_format) === false){
                DB::rollBack();
                Storage::deleteDirectory('/tmp-dir');
                Storage::deleteDirectory($directory);
                Storage::disk('short_clip')->deleteDirectory('short-' . $shortDirectory);
                return false; /* response(['wrong-format' => 'Format File Not Allowed']); */
            }

           $path = 'tmp-dir/' . $full_name;
           $target = $directory . '/' . $video_name;
          Storage::move($path, $target);

          //membaca detail video
          $ffprobe = FFProbe::create([
            'ffmpeg.binaries' => env('FFMPEG_BINARIES'),
            'ffprobe.binaries' => env('FFPROBE_BINARIES'),
        ]);

          $videoPath = public_path('storage/' . $target);
          $videoInfo = $ffprobe->streams($videoPath)->videos()->first();
          $video_duration = floor($videoInfo->get('duration') / 60);

          $url = Storage::disk('public')->url($target);

       $newAnimeVideo = AnimeVideo::create([
              'anime_name_id' => $newAnime,
              'anime_eps' => $video_name,
              'resolution' => $videoInfo->get('height'),
              'duration' => $video_duration,
              'video_format' => $real_format,
              'video_url' => $url
          ]);

           //call a job to make short video
           $short_data = [
            'disk' => $target,
            'directory' => $shortDirectory,
            'video_name' => $video_name,
            'anime_video_id' => $newAnimeVideo->id
        ];

     
       dispatch(new ClipingShortAnime($short_data));
           
        }

        Storage::deleteDirectory('/tmp-dir');
        $zipDetail->close();
        DB::commit();
        return true;
        
      
        }
    }


    

    /**
     * Display the specified resource.
     */
    public function show(AnimeVideo $animeVideo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnimeVideo $animeVideo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnimeVideoRequest $request, AnimeVideo $animeVideo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnimeVideo $animeVideo)
    {
        //
    }
}
