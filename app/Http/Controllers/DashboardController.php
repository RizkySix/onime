<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use App\Models\PricingOrder;
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
      if($tokenCreated->values()->first() !== null && Carbon::now() < Carbon::parse($tokenCreated->values()->first())->addHours(24)){
        return back()->with('limit' , 'Generate Token Once Per Day!');
      }

      $user->tokens()->delete();

      $tokenName = 'onime-api-' . $user->name;
      $all_vip_user = VipUser::where('user_id' , $user->id)->where('vip_duration' , '>' , Carbon::now())->get();
   
      if($all_vip_user->isEmpty()){ 
         $token = $user->createToken($tokenName , ['normal-token'])->plainTextToken;
      }else{
         
         //cari id pricing dan dapatkan power dari pricing vip
         $vip_user =  $all_vip_user->implode('pricing_id' , ',');
         $vip_user = explode(',' , $vip_user);
         $findPricing = Pricing::withTrashed()->whereIn('id' , $vip_user)->pluck('vip_power');

         $abilities = [];
         foreach($findPricing as $vip_power){
            //membuat abilities untuk vip token berdasarkan power pricing user
            if(strtoupper($vip_power) == 'NORMAL'){
              in_array('vip-token' , $abilities) ? : $abilities[] = 'vip-token';
            }elseif(strtoupper($vip_power) == 'SUPER'){
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
      return view('admin.dashboard' , [
         'pricings' => Pricing::select('id' , 'pricing_name')->with(['vip:id,pricing_id'])->withCount('vip as vip_total')->get(),
         'orders' => PricingOrder::whereMonth('created_at' , Carbon::now()->month)
                                    ->whereYear('created_at' , Carbon::now()->year)
                                    ->count(),
         'paid_orders' => PricingOrder::whereIn('transaction_status' , ['capture' , 'settlement'])
                                    ->whereMonth('created_at' , Carbon::now()->month)
                                    ->whereYear('created_at' , Carbon::now()->year)
                                    ->count()
      ]);
   }


   public function user_order()
   {
      $orders =  PricingOrder::with(['user:id,name,email'])->where('user_id' , auth()->user()->id)->latest()->get();
      return view('user.user-order' , [
         'orders' => $orders
      ]);
   }
   
}
