<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use App\Models\PricingOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PricingOrderController extends Controller
{
      /**
     * Midtrans make orders.
     */
    public function transaction_view(Pricing $pricing_name)
    {
        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = 'SB-Mid-server-WWkllVEE1pYMJ_n8sjrgyoS1';
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = false;
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;

        //generate random 15 character order ID
        $min = 1241573291;
        $max = 9999999999;
        $number = mt_rand($min , $max);
        $number = sprintf('%010d' , $number);
        $orderId = strtoupper(Str::random(5)) . $number;
        
        //gross amount berdasarkan diskon
        $getDiscount = $pricing_name->price * ($pricing_name->discount / 100);
        $realPrice = $pricing_name->price - $getDiscount;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => 100,
            ],
            'item_details' => [
                [
                    'id' => $pricing_name->pricing_name,
                    'price' => $realPrice,
                    'name' => $pricing_name->pricing_name . ' Member',
                    'quantity' => 1
                ]
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => '08111222333',
            ],
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
        }

          //validate Price
          $getDiscount = $pricing_name->price * ($pricing_name->discount / 100);
          $realPrice = $pricing_name->price - $getDiscount;

          DB::beginTransaction();
          $realPrice != $data_order['gross_amount'] ? : DB::rollBack();

          PricingOrder::create([
            'user_id' => auth()->user()->id,
            'order_id' => $data_order['order_id'],
            'payment_type' => $vaPayment,
            'transaction_status' => $data_order['transaction_status'],
            'pricing_type' => $pricing_name->pricing_name,
            'pricing_price' => $pricing_name->price,
            'gross_amount' => $data_order['gross_amount'],
            'payment_number' => $vaNumber,
            'transaction_time' => $data_order['transaction_time'],
          ]);

          DB::commit();

          return redirect()->route('pricing.index')->with('payment-success' , 'Payment Success');

       
    }
}
