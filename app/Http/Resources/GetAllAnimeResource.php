<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetAllAnimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->vip == 1 ? $vip = true : $vip = false;
        return [
            'anime_name' => $this->anime_name,
            'slug' => $this->slug,
            'total_episode' => $this->total_episode,
            'rating' => $this->rating,
            'released_date' => $this->released_date,
            'studio' => $this->studio,
            'author' => $this->author,
            'vip' => $vip,
            'description' => $this->description,
            'genre' => $this->genres->implode('genre_name' , ', '),
            'anime_videos' => AnimeVideoResource::collection($this->when($request->anime_name , $this->whenLoaded('anime_video')))
        ];
    }
}
