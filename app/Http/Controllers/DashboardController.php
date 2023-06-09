<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateExpiredVip;
use App\Models\Pricing;
use App\Models\User;
use App\Models\VipUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class DashboardController extends Controller
{
   public function view(Request $request)
   {
 

     return view('dashboard' , [
         'user' => User::all()
     ]);
   }
   

   public function generate_token()
   {
      $user = auth()->user();
      $user->tokens()->delete();

      $tokenName = 'onime-api-' . $user->name;
   
      if($user->vip->all() == null){
         $token = $user->createToken($tokenName , ['normal-token'])->plainTextToken;
      }else{
         
         //cari id pricing dan dapatkan power darii pricing vip
         $vip_user = $user->vip->pluck('pricing_id')->toArray();
         $findPricing = Pricing::withTrashed()->whereIn('id' , $vip_user)->pluck('vip_power');

         $abilities = [];
         foreach($findPricing as $vip_power){
            //membuat abilities untuk vip token berdasarkan power pricing user
            if($vip_power == 'NORMAL'){
              in_array('vip-token' , $abilities) ? : $abilities[] = 'vip-token';
            }elseif($vip_power == 'SUPER'){
               in_array('super-vip-token' , $abilities) ? : $abilities[] = 'super-vip-token';
            }
         }

         $token = $user->createToken($tokenName , $abilities)->plainTextToken;

      }
      
      $user->token = $token;
      $user->save();
      
     return redirect('dashboard');
   }

   
}
