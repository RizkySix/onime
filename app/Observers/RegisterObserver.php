<?php

namespace App\Observers;

use App\Mail\OtpSendMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        DB::table('otps_codes')->where('user_id' , $user->id)->delete();
        $otp_code  = mt_rand(13254131 , 99999999);

        DB::table('otps_codes')->insert([
            'user_id' => $user->id,
            'otp_code' => $otp_code,
            'expired_time' => Carbon::now()->addMinutes(60),
           
        ]);

      Mail::to($user->email)->send(new OtpSendMail($otp_code));
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
