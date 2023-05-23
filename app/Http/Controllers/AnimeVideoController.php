<?php

namespace App\Http\Controllers;


use App\Http\Requests\AnimeVideo\UpdateAnimeVideoRequest;
use App\Models\AnimeVideo;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AnimeVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($video_detail , $newAnime , $directory)
    {
        $ffprobe = FFProbe::create([
            'ffmpeg.binaries' => env('FFMPEG_BINARIES'),
            'ffprobe.binaries' => env('FFPROBE_BINARIES'),
        ]);
        
        $videoPath = $video_detail->getPathname();
        
        $videoInfo = $ffprobe->streams($videoPath)->videos()->first();
        
        $video_name = $video_detail->getClientOriginalName();
        $video_duration = floor($videoInfo->get('duration') / 60);
        
        Storage::disk('public')->putFileAs($directory , $video_detail , $video_name);
        $url = Storage::disk('public')->url($directory . '/' . $video_name);

        AnimeVideo::create([
            'anime_name_id' => $newAnime,
            'anime_eps' => $video_name,
            'resolution' => $videoInfo->get('height'),
            'duration' => $video_duration,
            'video_format' => $video_detail->getClientOriginalExtension(),
            'video_url' => $url
        ]);

        dd($url);
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
