<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnimeVideo extends Model
{
    use HasFactory , SoftDeletes , CascadeSoftDeletes;

    protected $guarded = ['id'];
    protected $cascadeDeletes = ['anime_short'];

    public function anime_name()
    {
        return $this->belongsTo(AnimeName::class);
    }

    public function anime_short()
    {
        return $this->hasOne(AnimeVideoShort::class);
    }
}
