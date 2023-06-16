<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\Auth\QueuedResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
       'id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    //menimpa pemanggilan sendPasswordResetNotification pada CanResetPassword agar menjadi queueable
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new QueuedResetPassword($token));
    }

    public function order()
    {
        return $this->hasMany(PricingOrder::class);
    }

    public function vip()
    {
        return $this->hasMany(VipUser::class);
    }
}
