<?php

namespace Tests\Feature;

use App\Models\Pricing;
use App\Models\PricingOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreditCardPaymentMethodTest extends TestCase
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
            'price' => 100000
        ]);

        //buat customer
        $this->customer = User::factory()->create(['admin' => false]);

    }


     /**
     * @group order-test-card
     * */  
    public function test_input_order_credit_card_to_make_sure_only_capture_transaction_status_allowed(): void
    {
        $payload = $this->set_card_payload(201 , 'pending'); //harus gagal karena credit_card hanya menerima transaction status capture 

        //payload harus dirubah menjadi string json agar dapat diolah di controller
        $payload_str = json_encode($payload);

        $responseFail = $this->actingAs($this->customer)->post(route('transaction' , $this->pricing->pricing_name) , [
            'order' => $payload_str
        ]);

        $responseFail->assertStatus(412); //hrusnya menerima status code 412

        $this->assertDatabaseEmpty('pricing_orders'); //harusnya data order baru gagal ditambahkan
        $this->assertDatabaseMissing('pricing_orders' , [
            'order_id' => $payload['order_id'],
            'transaction_status' => $payload['transaction_status'],
            'payment_type' => 'credit_card',
            'gross_amount' => $payload['gross_amount']
        ]);


         //berikut adalah case jika berhasil
         $payload = $this->set_card_payload(200 , 'capture'); //harus berhasil karena credit_card hanya menerima transaction status capture 

         //payload harus dirubah menjadi string json agar dapat diolah di controller
         $payload_str = json_encode($payload);
 
         $responseSuccess = $this->actingAs($this->customer)->post(route('transaction' , $this->pricing->pricing_name) , [
             'order' => $payload_str
         ]);
 
         $responseSuccess->assertStatus(302); //hrusnya menerima status code 302 redirect
 
         $this->assertDatabaseCount('pricing_orders' , 1); //harusnya data order baru gagal ditambahkan
         $this->assertDatabaseHas('pricing_orders' , [
             'order_id' => $payload['order_id'],
             'transaction_status' => $payload['transaction_status'],
             'payment_type' => 'credit_card',
             'gross_amount' => $payload['gross_amount']
         ]);
     
       
    }





    /**
     * @group order-test-card
     * */  
    public function test_input_order_data_for_credit_card(): void
    {
        $payload = $this->set_card_payload(200 , 'capture');

        //payload harus dirubah menjadi string json agar dapat diolah di controller
        $payload_str = json_encode($payload);

        $response = $this->actingAs($this->customer)->post(route('transaction' , $this->pricing->pricing_name) , [
            'order' => $payload_str
        ]);

        $this->assertDatabaseCount('pricing_orders' , 1); //harusnya data order baru ditambahkan
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => $payload['order_id'],
            'transaction_status' => $payload['transaction_status'],
            'payment_type' => 'credit_card',
            'gross_amount' => $payload['gross_amount']
        ]);
    
        $response->assertStatus(302);
        $response->assertRedirect('/transaction-done/' . $payload['order_id']); //seharusnya diarahkan ke route berikut
        //cek konten pada halaman transaction done
       $view_trans_done = $this->actingAs($this->customer)->get(route('transaction-done' , $payload['order_id']))->assertStatus(200);
       $view_trans_done->assertSee(strtoupper($payload['transaction_status']));
       $view_trans_done->assertSee('CREDIT_CARD');
       $view_trans_done->assertDontSee('UBAH METODE PEMBAYARAN');//btn ubah metode bayar
       $view_trans_done->assertDontSee('CANCEL ORDER');//btn cancel order tidak ada pada view transaction done

       //cek harusnya order sudah ada pada view list user order
       $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
       $view_list_user_order->assertSee(strtoupper($payload['transaction_status']));
       $view_list_user_order->assertSee('CREDIT_CARD');
       $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');//btn ubah metode bayar
       $view_list_user_order->assertDontSee('CANCEL ORDER');//btn cancel order tidak tersedia pada list user order

       //karena status order otomatis capture user sudah harus mendapat hak akses VIP
       $this->assertDatabaseCount('vip_users' , 1);
       $this->assertDatabaseHas('vip_users' , [
        'pricing_id' => $this->pricing->id,
        'user_id' => $this->customer->id,
        'vip_duration' => Carbon::now()->addDays($this->pricing->duration)
       ]);
    }


    /**
     * @group order-test-card
     * */  
    public function test_webhook_captured_transaction_status_not_allowing_cancel() : void
    {
        $order = $this->set_data_order_factory('capture'); //status awal harus capture

        //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_card_payload('202' , 'cancel');// set status terbaru menjadi cancel

        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan harus gagal
        $response->assertStatus(412);
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status, //status masih sama capture
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $this->assertDatabaseMissing('pricing_orders' , [
            'transaction_status' => 'cancel' //status cancel tidak ditemukan
        ]);

    }



    /**
     * @group order-test-card
     * */  
    public function test_webhook_captured_transaction_status_not_allowing_expires() : void
    {
        $order = $this->set_data_order_factory('capture'); //status awal harus capture

        //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_card_payload('202' , 'expire');// set status terbaru menjadi expire

        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan harus gagal
        $response->assertStatus(412);
        $this->assertDatabaseCount('pricing_orders' , 1); //jumlah data masih 1
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status, //status masih sama capture
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

       
    }



    
     /**
     * @group order-test-card
     * */  
    public function test_webhook_captured_transaction_status_allowing_settlement() : void
    {
        $order = $this->set_data_order_factory('capture'); //status awal harus capture

        //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_card_payload('200' , 'settlement');// set status terbaru menjadi settlement

        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan harus berhasil
        $response->assertStatus(200);
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $payload['transaction_status'], //status sudah settlement
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => $payload['payment_type'],
            'pricing_type' => $this->pricing->pricing_name
        ]);

        $this->assertDatabaseMissing('pricing_orders' , [
            'transaction_status' => $order->transaction_status, //status capture sudah berubah menjadi settlement 
        ]);

         //cek harusnya order sudah ada pada view list user order
       $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
       $view_list_user_order->assertSee(strtoupper($payload['transaction_status'])); //status settlement
       $view_list_user_order->assertSee('CREDIT_CARD');
       $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');//btn ubah metode bayar
       $view_list_user_order->assertDontSee('CANCEL ORDER');//btn cancel order tidak tersedia pada list user order

       //karena status order tetap termasuk paid yaitu settlement, maka harusnya tidak ada perubah pada vip
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
            'pricing_type' => 'Mega vip', //harus sama dengan nama pricing_name
            'payment_type' => 'credit_card', 
            'pricing_price' => $this->pricing->price,
            'gross_amount' => $this->set_price()
        ]);
        
        return $order;
    }


    private function set_card_payload(string $status_code , string $transaction_status) : array
    {
        $combine_str = 'PRCZ43455934857' . $status_code . $this->set_price() . env('MIDTRANS_SERVERKEY');
        $signature_key = hash('SHA512' , $combine_str);

        $payload = [
            "transaction_time" => "2023-07-10 23:03:37",
            "transaction_status" => $transaction_status,
            "transaction_id" => "fe402f37-5002-44f7-aa59-fc08211499ee",
            "three_ds_version" => "2",
            "status_message" => "midtrans payment notification",
            "status_code" => $status_code,
            "signature_key" => $signature_key,
            "payment_type" => "credit_card",
            "order_id" => "PRCZ43455934857",
            "merchant_id" => "G035482679",
            "masked_card" => "48111111-1114",
            "gross_amount" => $this->set_price(),
            "fraud_status" => "accept",
            "expiry_time" => "2023-07-10 23:13:37",
            "eci" => "05",
            "currency" => "IDR",
            "channel_response_message" => "Approved",
            "channel_response_code" => "00",
            "card_type" => "credit",
            "bank" => "cimb",
            "approval_code" => "1689005048918"
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
