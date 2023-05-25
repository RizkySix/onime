<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimeVideo extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function anime_name()
    {
        return $this->belongsTo(AnimeName::class);
    }

    public function anime_short()
    {
        return $this->hasOne(AnimeVideoShort::class);
    }
}
