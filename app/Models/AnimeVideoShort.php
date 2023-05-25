<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimeVideoShort extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public $timestamps = false;

    public function anime_video()
    {
        return $this->belongsTo(AnimeVideo::class);
    }
}
