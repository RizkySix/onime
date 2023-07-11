<?php

namespace Tests\Feature;

use App\Models\Pricing;
use App\Models\PricingOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChangePaymentMethodTest extends TestCase
{
    use RefreshDatabase;
    private $pricing;
    private $customer;

    protected function setUp(): void
    {
        parent::setUp();

        //buat satu pricing
     $this->pricing = Pricing::factory()->create([
        'pricing_name' => 'Mega vip',
        'discount' => 50,
        'price' => 100000
    ]);

    //buat customer
    $this->customer = User::factory()->create(['admin' => false]);

    }

    /**
     * @group change-payment-test
     */
    public function test_change_payment_method_available_only_for_pending_transaction(): void
    {
       //buat data order dengan status settlement (fail case)
       $order = $this->set_data_order_factory('settelement');

       $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
       $view_list_user_order->assertSee(strtoupper($order->trasanction_status)); //settlement
       $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');//btn ubah metode bayar seharusnya tidak muncul karena status sudah settlement
       
       //jika user mencoba akses ubah metode pembayaran tanpa button atau langsung melalui url akan tetap gagal
       $responseFail = $this->actingAs($this->customer)->get(route('change-payment-method-view' , $order->order_id));
       $responseFail->assertStatus(302);
       $responseFail->assertSessionHas('status' , 'Failed to change payment method');

    }


    /**
     * @group change-payment-test
     */
    public function test_customer_can_access_change_payment_method_view_if_transaction_status_pending(): void
    {
        //order dengan status pending (correct case)
        $order = $this->set_data_order_factory('pending');

        $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
        $view_list_user_order->assertSee(strtoupper($order->trasanction_status)); //pending
        $view_list_user_order->assertSee('UBAH METODE PEMBAYARAN');//btn ubah metode bayar muncul karena status masih pending
 
        //seharusnya view change payment method dapat diakses
        $responseSuccess = $this->actingAs($this->customer)->get(route('change-payment-method-view' , $order->order_id));
        $responseSuccess->assertStatus(200);
        $responseSuccess->assertSessionDoesntHaveErrors('status' , 'Failed to change payment method');
        $responseSuccess->assertSee('METODE PEMBAYARAN'); 
        $responseSuccess->assertSee(env('MIDTRANS_CLIENTKEY')); 
        $responseSuccess->assertDontSee(env('MIDTRANS_SERVERKEY')); 

    }

    /**
     * @group change-payment-test
     */
    public function test_payload_must_be_an_json_string() : void
    {

     //buat satu order dengan order_id random
      $order = PricingOrder::factory()->create([
        'transaction_status' => 'pending', //harus pending
        'user_id' => $this->customer->id,
      ]);

      $payload = $this->set_payload(201 , 'pending'); //set status awal pending

        $responseFail = $this->actingAs($this->customer)->put(route('change-payment-method' , $order->order_id) , [
            'order' => $payload
        ])->assertStatus(500); //jika yang dikirim bukan string json, maka akan muncul error

        //payload harus dirubah menjadi string json, agar dapat diolah di controller
        $payload = json_encode($payload);

        $responseSuccess = $this->actingAs($this->customer)->put(route('change-payment-method' , $order->order_id) , [
            'order' => $payload
        ])->assertStatus(302); //redirect
    }



     /**
     * @group change-payment-test
     */
    public function test_order_status_must_be_a_pending_and_user_id_match_with_authenticated_user() : void
    {

     //buat satu order dengan order_id random
      $order = PricingOrder::factory()->create([
        'transaction_status' => 'settlement', //case gagal karena sttatus harus pending
        'user_id' => $this->customer->id + 2 //case gagal karena customer id nya berbeda
      ]);

      $payload = $this->set_payload(201 , 'pending'); //set status awal pending

        //payload harus dirubah menjadi string json, agar dapat diolah di controller
        $payload = json_encode($payload);

        $responseFail = $this->actingAs($this->customer)->put(route('change-payment-method' , $order->order_id) , [
            'order' => $payload
        ])->assertStatus(403); //abort forbiden
    }


      /**
     * @group change-payment-test
     */
    public function test_only_valid_payment_method_allowed() : void
    {
         //buat satu order dengan order_id random
      $order = PricingOrder::factory()->create([
        'transaction_status' => 'pending', //harus pending
        'user_id' => $this->customer->id,
      ]);


      $this->assertDatabaseCount('pricing_orders' , 1);
      $this->assertDatabaseHas('pricing_orders' , [
        'id' => $order->id,
        'order_id' => $order->order_id
      ]);

        $payload = $this->set_payload(200 , 'pending' , 'shopeepay'); //payment method yang tidak sesuai (failed_case) 
        $payload = json_encode($payload);

        $responseFail = $this->actingAs($this->customer)->put(route('change-payment-method' , $order->order_id) , [
            'order' => $payload
        ])->assertStatus(400); //akan mendapat status code 400 bad request


        //gagal karena transaction status yang harus diterima antara pending,capture,dan settlement
        $payloadBca = $this->set_payload(202 , 'cancel' , 'bca'); //valid bca,bni,bri,cstore,credit_card
      
        $payloadBca = json_encode($payloadBca);
       
        $this->actingAs($this->customer)->put(route('change-payment-method' , $order->order_id) , [
            'order' => $payloadBca
        ])->assertStatus(302)->assertSessionHas('status' , 'Failed to change payment method'); //redirect fail
      
        //order_id belum berubah
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
          'id' => $order->id,
          'order_id' => $order->order_id
        ]);

        //success payload
        $payloadBca = $this->set_payload(200 , 'settlement' , 'bca'); //valid bca,bni,bri,cstore,credit_card
        
        $payloadBcaStr = json_encode($payloadBca);
       
        $this->actingAs($this->customer)->put(route('change-payment-method' , $order->order_id) , [
            'order' => $payloadBcaStr
        ])->assertStatus(302); //redirect

        //order_id untuk data id order yang sama telah berubah
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
          'id' => $order->id,
          'order_id' => $payloadBca['order_id']
        ]);

        //order_id lama harusnya sudah berubah
        $this->assertDatabaseMissing('pricing_orders' , [
            'id' => $order->id,
            'order_id' => $order->order_id
          ]);
    }


    /**
     * @group change-payment-test
     */
    public function test_ensure_cache_lock_throw_429_status_code() : void
    {

    $lock = Cache::lock('change-payment-method', 11); //kunci dipegang dulu selama 11 detik

    $lock->get();
    
    $order = PricingOrder::factory()->create([
        'transaction_status' => 'pending', //harus pending
        'user_id' => $this->customer->id,
      ]);

        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => $order->order_id
        ]);
        
       $payloadBca = $this->set_payload(200 , 'settlement' , 'bca'); 
      
       $payloadBca = json_encode($payloadBca);

    $response = $this->actingAs($this->customer)->put(route('change-payment-method' , $order->order_id) , [
        'order' => $payloadBca
     ])->assertStatus(429); //akan mendapat status 429 karena key sudah dipegang selama 30 detik

     $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => $order->order_id
        ]);//order id masih sama 
        

    $lock->release();
    
    }
     
     /**
     * @group change-payment-test
     */
    public function test_settlement_changed_payment_method() : void
    {
        $order = PricingOrder::factory()->create([
            'transaction_status' => 'pending',
            'user_id' => $this->customer->id
        ]);

        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'transaction_status' => $order->transaction_status,
            'user_id' => $this->customer->id,
            'id' => $order->id,
            'order_id' => $order->order_id
        ]);

        //set valid payload
        $payload = $this->set_payload(201 ,'pending'); //set payload pending
        $payloadStr = json_encode($payload);
        $response = $this->actingAs($this->customer)->put(route('change-payment-method' , $order->order_id) , [
            'order' => $payloadStr
        ])->assertStatus(302);

        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'transaction_status' => $payload['transaction_status'], //pending
            'user_id' => $this->customer->id,
            'id' => $order->id,
            'order_id' => $payload['order_id']
        ]);


        $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
        $view_list_user_order->assertSee($payload['order_id']);
        $view_list_user_order->assertDontSee($order->order_id);

        //user belum memiliki vip
        $this->assertDatabaseEmpty('vip_users');

        //simulasi settlement ke webhook
        $payload = $this->set_payload(200 ,'settlement'); //set payload settlement
        $response = $this->post(route('api.webhook'), $payload)->assertStatus(200);

        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'transaction_status' => $payload['transaction_status'], //settlement
            'user_id' => $this->customer->id,
            'id' => $order->id,
            'order_id' => $payload['order_id']
        ]);

        //user sudah memiliki hak akses vip
         $this->assertDatabaseCount('vip_users' , 1);
         $this->assertDatabaseHas('vip_users' , [
              'pricing_id' => $this->pricing->id,
              'user_id' => $this->customer->id,
              'vip_duration' => Carbon::now()->addDays($this->pricing->duration)
          ]);

    }


    private function set_data_order_factory(string $transaction_status) : object
    {
        $order = PricingOrder::factory()->create([
            'user_id' => $this->customer->id,
            'order_id' => 'PRCZ43455934857', 
            'transaction_status' => $transaction_status,
            'pricing_type' => $this->pricing->pricing_name, //harus sama dengan nama pricing_name
            'pricing_price' => $this->pricing->price,
            'gross_amount' => $this->set_price()
        ]);
        
        return $order;
    }



    private function set_payload(string $status_code , string $transaction_status , string $payment = 'bca') : array
    {
        $combine_str = 'PRCZ43455934857' . $status_code . 50000 . env('MIDTRANS_SERVERKEY');
        $signature_key = hash('SHA512' , $combine_str);

        $payload = [
            "va_numbers" => [
                [
                    "va_number" => "82679920479",
                    "bank" => $payment
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
            "gross_amount" => $this->set_price(), 
            "fraud_status" => "accept",
            "expiry_time" => "2023-07-09 12:36:24",
            "currency" => "IDR"
        ];

        return $payload;
    }


    private function set_price() 
    {
        $price = $this->pricing->price * ($this->pricing->discount / 100);
        $price = $this->pricing->price - $price;
        return $price;
    }
}
