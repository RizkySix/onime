<?php

namespace Tests\Feature;

use App\Models\Pricing;
use App\Models\PricingOrder;
use App\Models\User;
use App\Models\VipUser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PhpParser\Node\Expr\FuncCall;
use Tests\TestCase;

class OrderTest extends TestCase
{
   use RefreshDatabase;
    private $customer;
    private $pricing;

    protected function setUp(): void
    {
        parent::setUp();

        //buat satu pricing
       $this->pricing = Pricing::factory()->create([
            'pricing_name' => 'Mega vip',
            'discount' => 50,
            'price' => 100000,
            'duration' => 90
        ]);

        $this->customer = User::factory()->create();
    }
  
    /**
     * @group order-test-customer
     * */  
    public function test_customer_can_access_pricing_list(): void
    {
       $response = $this->actingAs($this->customer)->get(route('pricing.index'))->assertStatus(200);
       $response->assertSee('Mega vip');
       $response->assertSee('Rp. 50,000'); 
       $response->assertSee('PURCHASE'); 
    }


    /**
     * @group order-test-customer
     * */  
    public function test_customer_can_purchase_pricing() : void
    {
        $response = $this->actingAs($this->customer)->get(route('transaction-view' , $this->pricing->pricing_name))->assertStatus(200);
        $response->assertSee('Mega vip');
        $response->assertSee('Rp. 50,000'); 
        $response->assertSee('METODE PEMBAYARAN'); 
        $response->assertSee(env('MIDTRANS_CLIENTKEY')); 
        $response->assertDontSee(env('MIDTRANS_SERVERKEY')); 
    }

     
     /**
     * @group order-test-customer
     * */  
    public function test_payload_must_be_an_json_string() : void
    {

      $payload = $this->set_payload(201 , 'pending'); //set status awal pending

        $responseFail = $this->actingAs($this->customer)->post(route('transaction' , $this->pricing->pricing_name) , [
            'order' => $payload
        ])->assertStatus(500); //jika yang dikirim bukan string json, maka akan muncul error

        //payload harus dirubah menjadi string json, agar dapat diolah di controller
        $payload = json_encode($payload);

        $responseSuccess = $this->actingAs($this->customer)->post(route('transaction' , $this->pricing->pricing_name) , [
            'order' => $payload
        ])->assertStatus(302); //redirect
    }


     /**
     * @group order-test-customer
     * */  
    public function test_customer_vip_duration_expanded_when_pricing_already_available() : void
    {
        //buat vip
        $vip = VipUser::factory()->create([
            'pricing_id' => $this->pricing->id,
            'user_id' => $this->customer->id,
            'vip_duration' => Carbon::now()->addDays($this->pricing->duration)
        ]);

        $this->assertDatabaseCount('vip_users' , 1);
        $this->assertDatabaseHas('vip_users' , [
            'pricing_id' => $this->pricing->id,
            'user_id' => $this->customer->id,
            'vip_duration' => Carbon::now()->addDays($this->pricing->duration)
        ]);

        //lakukan transaksi dengan pricing yang sama
        $order = PricingOrder::factory()->create([ //set transaksi awal menjadi pending
            'order_id' => 'PRCZ43455934857',
            'user_id' => $this->customer->id,
            'pricing_type' => $this->pricing->pricing_name,
            'gross_amount' => 50000,
            'payment_type' => 'bca'
        ]);
        $payload = $this->set_payload('200' , 'settlement');// set status terbaru menjadi settlement
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);

        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'user_id' => $this->customer->id,
            'pricing_type' => $this->pricing->pricing_name,
            'gross_amount' => 50000,
            'transaction_status' => 'settlement'
        ]);

        //seharusnya vip duration diperpanjang menjadi lebih lama 90 hari dari sebelumnya
        $this->assertDatabaseCount('vip_users' , 1);
       $this->assertDatabaseHas('vip_users' , [
            'pricing_id' => $this->pricing->id,
            'user_id' => $this->customer->id,
            'vip_duration' => Carbon::parse($vip->vip_duration)->addDays(90)
        ]);

        $this->assertDatabaseMissing('vip_users' , [
            'vip_duration' => $vip->vip_duration
        ]);
    }



    private function set_payload(string $status_code , string $transaction_status) : array
    {
        $combine_str = 'PRCZ43455934857' . $status_code . 50000 . env('MIDTRANS_SERVERKEY');
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
            "gross_amount" => 50000, 
            "fraud_status" => "accept",
            "expiry_time" => "2023-07-09 12:36:24",
            "currency" => "IDR"
        ];

        return $payload;
    }

}
