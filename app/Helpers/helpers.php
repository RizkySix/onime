<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

if(!function_exists('delete_expired_otp')){
    function delete_expired_otp()
    {
        Cache::remember('delete_otp' , 60*60*24 , function(){
            return DB::table('otps_codes')->where('expired_time' , '<=' , Carbon::now())->delete();
        });
       
    }
}