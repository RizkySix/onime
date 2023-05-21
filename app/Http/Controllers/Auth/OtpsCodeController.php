<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Observers\RegisterObserver;
use Illuminate\Http\Request;

class OtpsCodeController extends Controller
{
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
}
