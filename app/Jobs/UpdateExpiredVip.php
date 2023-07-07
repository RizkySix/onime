<?php

namespace App\Jobs;

use App\Models\VipUser;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateExpiredVip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 5;
    public $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
       
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   

     $newAbilities = [];
     $query = null;

     VipUser::with(['pricing:id,vip_power'])->select('id' , 'user_id' , 'pricing_id' , 'vip_duration')
     ->where('vip_duration' , '>' , Carbon::now())
     ->chunk(50 , function($vipUser) use (&$newAbilities , &$query){//mendapatkan seluruh vip yang masih aktif dengan chunk

         foreach($vipUser as $vip)
         {
            //membuat ability baru berdasarkan POWER VIP pada pricing
            if($vip->pricing->vip_power == 'NORMAL'){
               $ability = 'vip-token';
            }else{
               $ability = strtolower($vip->pricing->vip_power . '-vip-token');
            }
            
            //menambahkan ke array user_id dan ability baru
            if(array_key_exists($vip->user_id , $newAbilities)){
               $newAbilities[$vip->user_id] .= ',"' . $ability . '"';
            }else{
               $newAbilities[$vip->user_id] = '"' . $ability . '"';
            }
         }

         //query update personal_access_tokens dengan CASE WHEN
         $query = "UPDATE personal_access_tokens SET abilities = CASE tokenable_id ";
            foreach($newAbilities as $user_id => $ability){
               $query .= "WHEN {$user_id} THEN '[{$ability}]' ";
            }
         $query .= "END ";
         $query .= "WHERE tokenable_id IN (" . implode(',' , array_keys($newAbilities)) . ")" ;

     }); 

    //query mentah seperti ini bisa di execute dengan DB::statement()
    $query == null ? :  DB::statement($query); 

     //update seluruh tooken yang tidak berlanggana  VIP menjadi normal token
     DB::table('personal_access_tokens')->whereNotIn('tokenable_id' , array_keys($newAbilities))->update(['abilities' => '["normal-token"]' ]);     
    }
}
