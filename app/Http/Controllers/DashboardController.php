<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
   public function view(Request $request)
   {

    $token = $request->user()->createToken('onime-api-' . $request->user()->name ,['normal-token'])->plainTextToken;

     return view('dashboard' , [
        'token_key' => $token,
     ]);
   }
}
