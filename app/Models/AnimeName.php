<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimeName extends Model
{
    use HasFactory , SoftDeletes , CascadeSoftDeletes;

    protected $guarded = ['id'];
    protected $cascadeDeletes = ['anime_video'];

    public function anime_video()
    {
        return $this->hasMany(AnimeVideo::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
