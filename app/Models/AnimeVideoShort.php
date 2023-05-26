<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimeVideoShort extends Model
{
    use HasFactory , SoftDeletes;

    protected $guarded = ['id'];
    public $timestamps = false;

    public function anime_video()
    {
        return $this->belongsTo(AnimeVideo::class);
    }
}
