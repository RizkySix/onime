<?php

namespace Tests\Feature;

use App\Models\Pricing;
use App\Models\PricingOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BcaPaymentMethodTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();

        //buat satu pricing
      Pricing::factory()->create([
            'pricing_name' => 'Mega vip',
            'discount' => 50,
            'price' => 100000
        ]);

    }

    /**
     * @group order-test
     * */ 
    public function test_webhook_settlement_order() : void
    {
       $order = $this->set_data_order_factory('pending'); //status awal pending

        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_bca_payload('200' , 'settlement');// set status terbaru menjadi settlement
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan berhasil dengan respons 200 OK
        $response->assertStatus(200);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $payload['transaction_status'], //harus menjadi settlement
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'bca',
            'pricing_type' => 'Mega vip'
        ]);

        $this->assertDatabaseMissing('pricing_orders', [
            'transaction_status' => $order->transaction_status, //status pending harus berubah
        ]);
    }


    private function set_data_order_factory(string $transaction_status) : object
    {
        $order = PricingOrder::factory()->create([
            'order_id' => 'PRCZ43455934857', 
            'transaction_status' => $transaction_status,
            'pricing_type' => 'Mega vip', //harus sama dengan nama pricing_name
            'payment_type' => 'bca',
        ]);
        
        return $order;
    }


    private function set_bca_payload(string $status_code , string $transaction_status) : array
    {
        $combine_str = 'PRCZ43455934857' . $status_code . '50000.00' . env('MIDTRANS_SERVERKEY');
        $signature_key = hash('SHA512' , $combine_str);

        $payload = [
            "va_numbers" => [
                [
                    "va_number" => "82679920479",
                    "bank" => "bca"
                ]
            ],
            "transaction_time" => "2023-07-08 12:58:14",
            "transaction_status" => $transaction_status,
            "transaction_id" => "f73eba60-f968-402c-b438-6e8c6d2712a9",
            "status_message" => "midtrans payment notification",
            "status_code" => $status_code,
            "signature_key" => $signature_key,
            "payment_type" => "bank_transfer",
            "payment_amounts" => [

            ],
            "order_id" => "PRCZ43455934857",
            "merchant_id" => "G035482679",
            "gross_amount" => "50000.00", //gross amount harus sama dengan gross amount pada factory
            "fraud_status" => "accept",
            "expiry_time" => "2023-07-09 12:36:24",
            "currency" => "IDR"
        ];

        return $payload;
    }

}
