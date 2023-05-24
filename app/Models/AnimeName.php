<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimeName extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function anime_video()
    {
        return $this->hasMany(AnimeVideo::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
