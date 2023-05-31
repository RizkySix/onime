<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreAnimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'anime_name' => $this->anime_name,
            'slug' => $this->slug,
            'total_episode' => $this->total_episode,
            'rating' => $this->rating,
            'studio' => $this->studio,
            'author' => $this->author,
            'description' => $this->description,
        ];
    }
}
