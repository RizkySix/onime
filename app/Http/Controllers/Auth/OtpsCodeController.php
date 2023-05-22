<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
use App\Models\User;
use App\Observers\RegisterObserver;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtpsCodeController extends Controller
{

    public function __invoke(Request $request)
    {
      
       return  $this->send($request) ? redirect()->intended(RouteServiceProvider::HOME) : redirect(route('view-verify-otp'));
    }


    public function view()
    {
        return view('auth.verify-otp-email');
    }

    public function resend()
    {
        $regenerateOtp = new RegisterObserver;
        $regenerateOtp->created(auth()->user());

        return back()->with('status' , 'resend-otp');
    }

    public function send(Request $request)
    {
      
        if(!$request->otp_direct_verified){
            $validatedData = $request->validate([
                'otp_code' => 'required|min:8|max:8'
            ]);
    
        }

       $findOtp = DB::table('otps_codes')->where('otp_code' , $request->otp_code)->where('user_id' , $request->id)->where('expired_time' , '>' , Carbon::now())->first();

        if($findOtp){
            User::where('id' , auth()->user()->id)->update(['email_verified_at' => Carbon::now()]);
            return redirect()->intended(RouteServiceProvider::HOME);
        }else{
            return back()->with('invalid' , 'invalid otp code');
        }
    }
}
