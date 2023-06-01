<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimeRating extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $guarded = ['id'];

    public function anime_name()
    {
        return $this->belongsTo(AnimeName::class);
    }
}
