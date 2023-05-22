<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
   public function view(Request $request)
   {

  
     return view('dashboard' , [
       
     ]);
   }
   

   public function generate_token()
   {
      $user = request()->user();
      $user->tokens()->delete();

      $tokenName = 'onime-api-' . $user->name;
      $ability = $user->vip == false ? ['normal-token'] : ['vip-token'];

      $token = $user->createToken($tokenName , $ability)->plainTextToken;

      $user->token = $token;
      $user->save();
      
      return redirect('dashboard');
   }
}
