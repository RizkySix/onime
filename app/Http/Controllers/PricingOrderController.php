<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use App\Models\PricingOrder;
use App\Models\User;
use App\Models\VipUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\FuncCall;

class PricingOrderController extends Controller
{

    /**
     * Midtrans make orders.
     */
    public function set_midtrans_param($pricing_name , $pricing_discount , $pricing_price)
    {
         // Set your Merchant Server Key
         \Midtrans\Config::$serverKey = env('MIDTRANS_SERVERKEY');
         // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
         \Midtrans\Config::$isProduction = env('MIDTRANS_PRODUCTION');
         // Set sanitization on (default)
         \Midtrans\Config::$isSanitized = env('MIDTRANS_SANITIZED');
         // Set 3DS transaction for credit card to true
         \Midtrans\Config::$is3ds = env('MIDTRANS_IS3DS');
 
         //generate random 15 character order ID
         $min = 1241573291;
         $max = 9999999999;
         $number = mt_rand($min , $max);
         $number = sprintf('%010d' , $number);
         $orderId = strtoupper(Str::random(5)) . $number;
         
         //gross amount berdasarkan diskon
         $getDiscount = $pricing_price * ($pricing_discount / 100);
         $realPrice = $pricing_price - $getDiscount;
 
         $enabled_payments = [
             'bca_va',
             'bni_va',
             'bri_va',
             'indomaret',
             'credit_card'
         ];
 
         $params = [
             'transaction_details' => [
                 'order_id' => $orderId,
                 'gross_amount' => 100,
             ],
             'credit_card' => [
                 'secure' => true
             ],
             'item_details' => [
                 [
                     'id' => $pricing_name,
                     'price' => $realPrice,
                     'name' => $pricing_name . ' Member',
                     'quantity' => 1,
                     'category' => 'VIP MEMBER'
                 ]
             ],
             'customer_details' => [
                 'first_name' => auth()->user()->name,
                 'email' => auth()->user()->email,
                 'phone' => '08111222333',
             ],
             'expiry' => [
                 'start_time' => Carbon::now() . '+0700',
                 'unit' => 'hours',
                 'duration' => 24
             ],
             
             'enabled_payments' => $enabled_payments,
             
         ];

         return $params;
    }


      /**
     * Midtrans make orders.
     */
    public function transaction_view(Pricing $pricing_name)
    {
       
        $params = $this->set_midtrans_param($pricing_name->pricing_name , $pricing_name->discount , $pricing_name->price);
        
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return view('pricing-order.transaction-view' , [
            'snapToken' => $snapToken,
            'pricing' => $pricing_name
        ]);
    }

      /**
     * Midtrans make orders.
     */
    public function transaction(Request $request , Pricing $pricing_name)
    {
        $data_order = json_decode($request->order , true);
      
        $vaNumber = 'unknown';
        $vaPayment = 'unknown';

        if(isset($data_order['va_numbers'])){
            $vaNumber = $data_order['va_numbers'][0]['va_number'];
            $vaPayment = $data_order['va_numbers'][0]['bank'];
        }elseif(isset($data_order['payment_code']) && !isset($data_order['va_numbers'])){
            $vaNumber = $data_order['payment_code'];
            $vaPayment = $data_order['payment_type'];
        }elseif(isset($data_order['payment_type'])){
            $vaPayment = $data_order['payment_type'];
        }

          //validate Price
          $getDiscount = $pricing_name->price * ($pricing_name->discount / 100);
          $realPrice = $pricing_name->price - $getDiscount;
         
          DB::beginTransaction();

          //mendapat transaction status settlement jika metode bayar dengan INDOMARET
          if($data_order['payment_type'] == 'cstore'){
                $response = $this->get_transaction_status($data_order['order_id']);

                if($response['status_code'] == '200' && $response['transaction_status'] == 'settlement'){
                    $data_order['transaction_status'] = $response['transaction_status'];
                }
          }
         
        $pricing_order =  PricingOrder::create([
            'user_id' => auth()->user()->id,
            'order_id' => $data_order['order_id'],
            'payment_type' => $vaPayment,
            'transaction_status' => $data_order['transaction_status'],
            'pricing_type' => $pricing_name->pricing_name,
            'pricing_duration_in_days' => $pricing_name->duration,
            'pricing_price' => $pricing_name->price,
            'gross_amount' => $data_order['gross_amount'],
            'pricing_discount' => $pricing_name->discount,
            'payment_number' => $vaNumber,
            'transaction_time' => $data_order['transaction_time'],
          ]);

          if($data_order['transaction_status'] == 'settlement' || $data_order['transaction_status'] == 'capture'){
            $this->vip_user($pricing_order);
          }

          $realPrice == $data_order['gross_amount'] ? (DB::commit()) . ($message = 'Payment Success') : (DB::rollBack()) . ($message = 'No Match Gross Amount');

          return redirect()->route('transaction-done' , $pricing_order)->with('payment-success' , $message);

       
    }

     /**
     * View Transaction Done
     */
    public function transaction_done(PricingOrder $pricing_order)
    {
        return view('pricing-order.transaction-done' , [
            'order' => $pricing_order
        ]);
    }

      /**
     * Midtrans api cancel order.
     */
     public function cancel_order(PricingOrder $pricing_order)
     {  
        if($pricing_order->transaction_status != 'pending'){
            return back()->with('status' , 'Failed to cancel the order, still willing ? please wait until the order expires'  );
        }

        //canceling order to midtrans dashboard
        $response = Http::withBasicAuth(env('MIDTRANS_SERVERKEY') , '')
        ->withHeaders([
            'accept' => 'application/json'
        ])
        ->post('https://api.sandbox.midtrans.com/v2/' . $pricing_order->order_id . '/cancel');
        
        $arrResponse = json_decode($response , true);
    
        $status_code = ['401' , '412' , '404']; //status code untuk error response
        if(in_array($arrResponse['status_code'] , $status_code)){
            $message = 'Failed to cancel the order, still willing ? please wait until the order expires'  ;
        }
        
        if($arrResponse['status_code'] == '200'){
            $message = 'Success, order canceled'  ;
        }

        return back()->with('status' , $message);
      
    }

     /**
     * Midtrans api cancel order.
     */
    public function delete_cancel_order(PricingOrder $pricing_order)
    {
        PricingOrder::destroy($pricing_order->id);
        return back()->with('status' , 'Canceled Order Deleted');
    }

     /**
     * Midtrans get transaction status.
     */
    public function get_transaction_status($order_id)
    {
        $response = Http::withBasicAuth(env('MIDTRANS_SERVERKEY') , '')
        ->withHeaders([
            'accept' => 'application/json'
        ])
        ->get('https://api.sandbox.midtrans.com/v2/' . $order_id . '/status');

        $arrResponse = json_decode($response , true);
        return $arrResponse;
    }


     /**
     * Midtrans api expire order.
     */
    public function expiring_order($order_id)
    {
       $response = Http::withBasicAuth(env('MIDTRANS_SERVERKEY') , '')
        ->withHeaders([
            'accept' => 'application/json'
        ])
        ->post('https://api.sandbox.midtrans.com/v2/' . $order_id . '/expire');

        $arrResponse = json_decode($response , true);
        return $arrResponse;
    }


     /**
     * Make VIP for user.
     */
    public function vip_user(PricingOrder $pricing_order) : void
    {
         //get pricing ID 
         $getPricingId = Pricing::withTrashed()->where('pricing_name' , $pricing_order->pricing_type)->pluck('id');

         //cek apakah data vip yang sama sudah ada 
        $findVip = VipUser::where('user_id' , $pricing_order->user_id)
                              ->where('pricing_id' , $getPricingId->values()->first())
                              ->first();
         
         if(!$findVip){
            //buat VIP untuk user
            VipUser::create([
                'user_id' => $pricing_order->user_id,
                'pricing_id' => $getPricingId->values()->first(),
                'vip_duration' => Carbon::now()->addDays($pricing_order->pricing_duration_in_days)
            ]);
         }else{
             //update tanggal vip
            $findVip->vip_duration = Carbon::parse($findVip->vip_duration)
                                            ->addDays($pricing_order->pricing_duration_in_days); 
             $findVip->save();
         }
    }

     /**
     * Midtrans api change payment method .
     */
    public function change_payment_method_view(PricingOrder $pricing_order)
    { 
           
        if($pricing_order->transaction_status != 'pending'){
            return back()->with('status' , 'Failed to change payment method');
        }

        $params = $this->set_midtrans_param($pricing_order->pricing_type , $pricing_order->pricing_discount , $pricing_order->pricing_price);
        
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return view('pricing-order.change-payment-method-view' , [
            'snapToken' => $snapToken,
            'pricing_order' => $pricing_order
        ]);
    }


     /**
     * Midtrans api change payment method .
     */
    public function change_payment_method(Request $request, PricingOrder $pricing_order)
    {
     
        $data_order = json_decode($request->order , true);

        $vaNumber = 'unknown';
        $vaPayment = 'unknown';

        if(isset($data_order['va_numbers'])){
            $vaNumber = $data_order['va_numbers'][0]['va_number'];
            $vaPayment = $data_order['va_numbers'][0]['bank'];
        }elseif(isset($data_order['payment_code']) && !isset($data_order['va_numbers'])){
            $vaNumber = $data_order['payment_code'];
            $vaPayment = $data_order['payment_type'];
        }elseif(isset($data_order['payment_type'])){
            $vaPayment = $data_order['payment_type'];
        }

        $message = '';

        //lock menghindari race condition
        Cache::lock('change-payment-method')->block(10 , function() 
        use($pricing_order , $data_order , $vaNumber , $vaPayment , &$message)
        {
           DB::beginTransaction();

            DB::table('pricing_orders')->where('order_id' , $pricing_order->order_id)->lockForUpdate()->get();
            if($data_order['transaction_status'] == 'pending' || $data_order['transaction_status'] == 'capture'){
                PricingOrder::where('order_id' , $pricing_order->order_id)->update([
                    'transaction_status' => $data_order['transaction_status'],
                    'order_id' => $data_order['order_id'],
                    'payment_type' => $vaPayment,
                    'payment_number' => $vaNumber
                ]);
                
                if($data_order['transaction_status']  == 'capture'){
                    //buat vip untuk user
                    $this->vip_user($pricing_order);
                }

                    //expiring old order 
                    $this->expiring_order($pricing_order->order_id);
                    DB::commit();

                $message = 'Success to change payment method';
                
            }elseif($data_order['transaction_status'] == 'settlement'){
                PricingOrder::where('order_id' , $pricing_order->order_id)->update([
                    'transaction_status' => $data_order['transaction_status'],
                    'order_id' => $data_order['order_id'],
                    'payment_type' => $vaPayment,
                    'payment_number' => $vaNumber
                ]);

                //buat vip untuk user
                $this->vip_user($pricing_order);

                //expiring old order 
                $this->expiring_order($pricing_order->order_id);
                DB::commit();

                $message = 'Success to change payment method';
            }else{
                DB::rollBack();
                $message = 'Failed to change payment method';
        }
        });

       return redirect()->route('transaction-done' , $data_order['order_id'])->with('status' , $message);

    }


      /**
     * Midtrans api webhook.
     */
    public function webhook(Request $request)
    {
      
     try {
    
        $combineStr = $request->order_id . $request->status_code . $request->gross_amount . env('MIDTRANS_SERVERKEY');
        $validationResponseKey = hash('SHA512' , $combineStr);

        DB::beginTransaction();

       DB::table('pricing_orders')->where('order_id' , $request->order_id)->lockForUpdate()->get();
       $order =  PricingOrder::where('order_id' , $request->order_id)->first();
        if($request->signature_key !== $validationResponseKey){
            DB::rollBack();
           return redirect()->route('pricing.index')->with('failure' , 'Something Wrong , Your Transaction on Pending');
        }

        //perbarui status pesanan selesai pada database
        if($request->transaction_status == 'settlement' || $request->transaction_status == 'capture'){
            if($order->transaction_status  == 'pending' || $order->transaction_status == 'capture'){
                $order->transaction_status = $request->transaction_status;
                $order->save();
                
                //buat vip untuk user
                $this->vip_user($order);
                
                $order->gross_amount == $request->gross_amount ? DB::commit() : DB::rollBack();
            }
          
        }

        //hapus transaksi jika expired atau deny
        if($request->transaction_status == 'expire' || $request->transaction_status == 'deny'){
          if($order->transaction_status == 'pending'){
            $order->delete();
          }
            
            $order->gross_amount == $request->gross_amount ? DB::commit() : DB::rollBack();
         }

         //perbarui status transaksi jika di cancel
         if($request->transaction_status == 'cancel' && $order->transaction_status == 'pending'){
            $order->transaction_status = $request->transaction_status;
            $order->save();
            DB::commit();
            
         }

     } catch (\Exception $e) {
        return redirect()->route('pricing.index')->with('failure' , 'Something Wrong , Your Transaction on Pending');
     }
     
    }
}
