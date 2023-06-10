<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'genre_name' => $this->genre_name,
            'animes' => GenreAnimeResource::collection($this->whenLoaded('anime_name' , 
            $this->anime_name->load('genres')))
        ];
    }
}
