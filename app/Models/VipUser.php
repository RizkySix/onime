<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipUser extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts  = ['vip_duration' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pricing()
    {
        return $this->belongsTo(Pricing::class);
    }
}
