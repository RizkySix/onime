<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use HasFactory ,  SoftDeletes;
    protected $guaraded = ['id'];

    public function getRouteKeyName()
    {
        return 'genre_name';
    }

    public function anime_name()
    {
        return $this->belongsToMany(AnimeName::class ,  'anime_genre' , 'genres_id' , 'anime_names_id');
    }
}
