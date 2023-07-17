<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimeVideo\StoreAnimeVideoRequest;
use App\Http\Requests\AnimeVideo\UpdateAnimeVideoRequest;
use App\Jobs\ClipingShortAnime;
use App\Models\AnimeName;
use App\Models\AnimeVideo;
use App\Observers\AnimeVideoObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ZipArchive;
use \FFMpeg\Coordinate\TimeCode;
use \FFMpeg\Filters\Video\ClipFilter;
use Illuminate\Support\Facades\Request;

class AnimeVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       
    }

    /**
     * Display a listing anime name with video of the resource.
     */
    public function show_video(AnimeName $anime_name)
    {
       return view('anime.show-anime-video' , [
        'anime_name' => $anime_name->load(['anime_video' => function ($query) {
            $query->select('anime_eps', 'id', 'anime_name_id' , 'deleted_at');
        }])
       ]);
    }

    /**
     * Display a listing anime name with video of the resource.
     */
    public function show_video_trashed(AnimeName $anime_name)
    {
       return view('anime.show-anime-video-trashed' , [
        'anime_name' => $anime_name->load(['anime_video' => function ($query) {
            $query->onlyTrashed()->select('anime_eps', 'id', 'anime_name_id' , 'deleted_at');
        }])
       ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $findAnimeSlug = AnimeName::where('slug' , request('anime-slug'))->count();
        $findAnimeSlug == 1 ? : abort(404);
        
        return view('anime.add-anime-video');
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
            $animeName = $findAnime->keys()->first(); //anime_name
            $newAnime = $findAnime->values()->first(); //id
         
            if ($animeName == null) {
                return back()->with('no-match', 'Anime Not Found');
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

            //find for duplicate anime video name
            $findDuplicate = AnimeVideo::where('anime_eps' , $video_name)->where('anime_name_id' , $newAnime)->pluck('anime_eps');
            
            if($findDuplicate->values()->first() == null){

                //save to storage
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
                return back()->with('success' , 'Video Added');
            }else{
                return back()->with('no-match' , 'Duplicate Video');
            }
        
           
          
        }else{
            return back()->with('no-match', 'Anime Not Found');
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
           $zipDetail->extractTo(public_path('storage/tmp-dir'));
           
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
    public function show(AnimeVideo $anime_video)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnimeVideo $anime_video)
    {
        //
    }

     /**
     * remove dot and slash.
     */

    public function remove_dot($animeVidName , $format)
    {
        //call remove space
        $call = new AnimeNameController;
        $clearVidName = $call->remove_white_space($animeVidName);
     
        $clearVidName = explode('.' , $clearVidName);
        $loop = count($clearVidName);

        for($i = 0; $i < $loop; $i++){

            $lenStr = strlen($clearVidName[$i]);
            if($clearVidName[$i] == null || $clearVidName[$i] == ' '){
                unset($clearVidName[$i]);
            }elseif($clearVidName[$i][$lenStr - 1] == ' '){
                $clearVidName[$i] = str_replace(' ' , '' , $clearVidName[$i]);
            }
     
        }

        end($clearVidName) === $format ? : array_push($clearVidName , $format);
        $result = implode('.' , $clearVidName);

        return $result;

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnimeVideoRequest $request, AnimeVideo $anime_video)
    {
        $validatedData = $request->validated();
        $allDataAnime = $anime_video->load('anime_name:id,anime_name');
        
        $format = $anime_video->video_format;
        $clearVidName = $this->remove_dot($validatedData['anime_eps'] , $format);

        $findDuplicate = AnimeVideo::withTrashed()
                                    ->where('id' , '!=' , $anime_video->id)
                                    ->where('anime_name_id' , '=' , $anime_video->anime_name_id)
                                    ->where('anime_eps' , $clearVidName)
                                    ->pluck('anime_eps');

        if($findDuplicate->values()->first() != null){
            return back()->with('info' , 'Duplicate Name Found');
        }
        
          //call observer
          if($anime_video->anime_eps !== $validatedData['anime_eps']){
          
            $oldPath = Storage::path('F-' . $allDataAnime->anime_name->anime_name . '/' . $anime_video->anime_eps);
            $newPath = Storage::path('F-' . $allDataAnime->anime_name->anime_name . '/' . $clearVidName);
            rename($oldPath, $newPath);

            $animeVideoObserver = new AnimeVideoObserver;
            $animeVideoObserver->no_event_updated($anime_video , $allDataAnime->anime_name->anime_name , $clearVidName);
        }

        DB::table('anime_videos')
        ->where('id' , $anime_video->id)
        ->update(['video_url' => DB::raw("REGEXP_REPLACE(video_url, '" . '/' . $anime_video->anime_eps . "', '" . 
                                                                        '/' . $clearVidName . "')")
         , 'anime_eps' => $clearVidName]);

        return back()->with('info' , 'Success Updating');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnimeVideo $anime_video)
    {
        AnimeVideo::destroy($anime_video->id);

        return back();
        
    }

     /**
     * Restore the specified resource from storage.
     */

     public function restore($id)
     {
        $softDeleted = AnimeVideo::onlyTrashed()->findOrFail($id);

        if($softDeleted){
            $softDeleted->restore();
        }

        return back()->with('info' , 'Success Untrash');
     }

     /**
     * Force delete the specified resource from storage.
     */

     public function force_delete($id)
     {
        $forceDelete = AnimeVideo::onlyTrashed()->findOrFail($id);

        if($forceDelete){
            $forceDelete->forceDelete();
        }

        return back()->with('info' , 'Success Permanent Delete');
     }
}
