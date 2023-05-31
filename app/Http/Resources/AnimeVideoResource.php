<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimeVideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->anime_eps,
            'resolution' => $this->resolution . 'p',
            'duration' => $this->duration . ' minute',
            'video_format' => $this->video_format ,
            'video_url' => $this->video_url,
            'short_clip' => AnimeShortVideoResource::make($this->anime_short)
        ];
    }
}
