<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pricing extends Model
{
    use HasFactory , SoftDeletes;

    protected $guarded = ['id'];

    public function getRouteKeyName()
    {
        return 'pricing_name';
    }

    public function vip()
    {
        return $this->hasMany(VipUser::class);
    }
}
