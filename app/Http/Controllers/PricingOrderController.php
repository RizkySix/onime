<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use App\Models\PricingOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PricingOrderController extends Controller
{
      /**
     * Midtrans make orders.
     */
    public function transaction_view(Pricing $pricing_name)
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
        $getDiscount = $pricing_name->price * ($pricing_name->discount / 100);
        $realPrice = $pricing_name->price - $getDiscount;

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
                    'id' => $pricing_name->pricing_name,
                    'price' => $realPrice,
                    'name' => $pricing_name->pricing_name . ' Member',
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
         
          PricingOrder::create([
            'user_id' => auth()->user()->id,
            'order_id' => $data_order['order_id'],
            'payment_type' => $vaPayment,
            'transaction_status' => $data_order['transaction_status'],
            'pricing_type' => $pricing_name->pricing_name,
            'pricing_duration_in_days' => $pricing_name->duration,
            'pricing_price' => $pricing_name->price,
            'gross_amount' => $data_order['gross_amount'],
            'payment_number' => $vaNumber,
            'transaction_time' => $data_order['transaction_time'],
          ]);

          $realPrice == $data_order['gross_amount'] ? (DB::commit()) . ($message = 'Payment Success') : (DB::rollBack()) . ($message = 'No Match Gross Amount');

          return redirect()->route('pricing.index')->with('payment-success' , $message);

       
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
    
        $status_code = ['401' , '412' , '404'];
        if(in_array($arrResponse['status_code'] , $status_code)){
            $message = 'Failed to cancel the order, still willing ? please wait until the order expires'  ;
        }
        
        if($arrResponse['status_code'] == '200'){
            $message = 'Success, order canceled'  ;
        }

        return back()->with('status' , $message);
      
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

        //perbarui status pada database
        if($request->transaction_status == 'settlement' || $request->transaction_status == 'capture'){
            if($order->transaction_status  == 'pending'){
                $order->transaction_status = $request->transaction_status;
                $order->save();
            
                 //update user
                 User::where('id' , $order->user_id)->update(['vip' => true , 'vip_duration' => Carbon::now()->addDays($order->pricing_duration_in_days)]);
               
                $order->gross_amount == $request->gross_amount ? DB::commit() : DB::rollBack();
            }
          
        }

        //hapus transaksi jika expired atau deny
        if($request->transaction_status == 'expire' || $request->transaction_status == 'deny'){
            $order->transaction_status == 'settlement' || $order->transaction_status == 'capture' ? :  $order->delete();
            
            //expiring order to midtrans dashboard
           Http::withBasicAuth(env('MIDTRANS_SERVERKEY') , '')
        ->withHeaders([
            'accept' => 'application/json'
        ])
        ->post('https://api.sandbox.midtrans.com/v2/' . $order->order_id . '/expire');

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
