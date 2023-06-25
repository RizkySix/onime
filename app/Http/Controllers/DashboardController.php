<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class DashboardController extends Controller
{
   public function view(Request $request)
   {
 
     return view('user.dashboard' , [
         'auth' => auth()->user()->load(['vip' => function($query) {
               $query->with(['pricing' => function($subQuery) {
                  $subQuery->withTrashed()->select('id' , 'pricing_name' , 'vip_power');
               }]);
         }])
     ]);
   }
   

   public function generate_token()
   {

      $user = auth()->user();

      $tokenCreated = DB::table('personal_access_tokens')->where('tokenable_id' , $user->id)->pluck('created_at');
     
      //membatasi supaya user hanya bisa generate token hanya 1x24 jam
      if(Carbon::now() < Carbon::parse($tokenCreated->values()->first())->addHours(24)){
         return back()->with('limit' , 'Generate Token Once Per Day!');
      }

      $user->tokens()->delete();

      $tokenName = 'onime-api-' . $user->name;
   
      if($user->vip->all() == null || $user->vip->count() <= 1 && $user->vip[0]->vip_duration < Carbon::now()){
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



   public function view_admin()
   {
      return view('admin.dashboard');
   }
   
}
