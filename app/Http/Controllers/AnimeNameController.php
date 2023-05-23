<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimeName\StoreAnimeNameRequest;
use App\Http\Requests\AnimeName\UpdateAnimeNameRequest;
use App\Models\AnimeName;
use Illuminate\Support\Facades\Storage;

class AnimeNameController extends Controller
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
    public function store(StoreAnimeNameRequest $request)
    {
       $newAnime = AnimeName::create([
        'anime_name' => $request->anime_name,
        'total_episode' => $request->total_episode,
        'author' => $request->author,
        'studio' => $request->studio,
        'description' => $request->description,
       ]);

       Storage::makeDirectory($request->anime_name);

       $anime_video = new AnimeVideoController;
       $anime_video->store($request->file('video') , $newAnime->id , $request->anime_name);
       
    }

    /**
     * Display the specified resource.
     */
    public function show(AnimeName $animeName)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnimeName $animeName)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnimeNameRequest $request, AnimeName $animeName)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnimeName $animeName)
    {
        //
    }
}
