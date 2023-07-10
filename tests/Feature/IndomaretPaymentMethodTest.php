<?php

namespace Tests\Feature;

use App\Models\Pricing;
use App\Models\PricingOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndomaretPaymentMethodTest extends TestCase
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
     * @group order-test-indomaret
     * */  
    public function test_input_order_data_for_indomaret() : void
    {
        $payload = $this->set_indomaret_payload(201 , 'pending');
       
        //payload harus dirubah menjadi string json agar dapat diolah di controller
        $payload_str = json_encode($payload);

        $response = $this->actingAs($this->customer)->post(route('transaction' , $this->pricing->pricing_name) , [
            'order' => $payload_str
        ]);
        
        $this->assertDatabaseCount('pricing_orders' , 1); //harusnya data order baru ditambahkan
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => $payload['order_id'],
            'transaction_status' => $payload['transaction_status'],
            'payment_type' => 'cstore',
            'gross_amount' => $payload['gross_amount']
        ]);
    
        $response->assertStatus(302);
        $response->assertRedirect('/transaction-done/' . $payload['order_id']); //seharusnya diarahkan ke route berikut
        //cek konten pada halaman transaction done
       $view_trans_done = $this->actingAs($this->customer)->get(route('transaction-done' , $payload['order_id']))->assertStatus(200);
       $view_trans_done->assertSee(strtoupper($payload['transaction_status']));
       $view_trans_done->assertSee('CSTORE');
       $view_trans_done->assertSee('Indomaret');
       $view_trans_done->assertSee('UBAH METODE PEMBAYARAN');//btn ubah metode bayar
       $view_trans_done->assertDontSee('CANCEL ORDER');//btn cancel order tidak ada pada view transaction done

       //cek harusnya order sudah ada pada view list user order
       $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
       $view_list_user_order->assertSee(strtoupper($payload['transaction_status']));
       $view_list_user_order->assertSee('CSTORE');
       $view_list_user_order->assertSee('Indomaret');
       $view_list_user_order->assertSee('UBAH METODE PEMBAYARAN');//btn ubah metode bayar
       $view_list_user_order->assertSee('CANCEL ORDER');//btn cancel order tersedia pada list user order

       //karena status order masih pending harusnya user belum mendapatkan hak akses VIP
       $this->assertDatabaseCount('vip_users' , 0);
       $this->assertDatabaseMissing('vip_users' , [
        'pricing_id' => $this->pricing->id,
        'user_id' => $this->customer->id,
        'vip_duration' => Carbon::now()->addDays($this->pricing->duration)
       ]);
        
    }



    /**
     * @group order-test-indomaret
     * */ 
    public function test_webhook_settlement_order() : void
    {
    
       $order = $this->set_data_order_factory('pending'); //status awal pending

        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_indomaret_payload('200' , 'settlement');// set status terbaru menjadi settlement
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan berhasil dengan respons 200 OK
        $response->assertStatus(200);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $payload['transaction_status'], //harus menjadi settlement
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'cstore',
            'pricing_type' => 'Mega vip'
        ]);

        $this->assertDatabaseMissing('pricing_orders', [
            'transaction_status' => $order->transaction_status, //status pending harus berubah
        ]);

        //pada view list user order status order harusnya sudah settlement
        $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
        $view_list_user_order->assertSee(strtoupper($payload['transaction_status']));
        $view_list_user_order->assertSee('CSTORE');
        $view_list_user_order->assertSee('Indomaret');
        //cancel order dan ubah metode bayar seharusnya sudah tidak tersedia lagi
        $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');
        $view_list_user_order->assertDontSee('CANCEL ORDER');

        //user sekarang sudah memiliki hak akses vip
        $this->assertDatabaseCount('vip_users' , 1);
        $this->assertDatabaseHas('vip_users' , [
            'user_id' => $this->customer->id,
            'pricing_id' => $this->pricing->id,
            'vip_duration' => Carbon::now()->addDays($this->pricing->duration)
        ]);

    }

     /**
     * @group order-test-indomaret
     * */ 
    public function test_webhook_settlement_order_failed_case() : void
    {
    
       $order = $this->set_data_order_factory('cancel'); //status awal cancel (fail case)

       //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_indomaret_payload('200' , 'settlement');// set status terbaru menjadi settlement
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan harus gagal
        $response->assertStatus(412);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $order->transaction_status, //status masih sama cancel
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'cstore',
            'pricing_type' => 'Mega vip'
        ]);

          //pada view list user order status order harusnya masih cancel
          $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
          $view_list_user_order->assertSee(strtoupper($order->transaction_status));
          $view_list_user_order->assertSee('CSTORE');
          $view_list_user_order->assertSee('Indomaret');
          //cancel order dan ubah metode pembayaran harusnya tidak tersedia karena status pesanan saat ini cancel
          $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');
          $view_list_user_order->assertDontSee('CANCEL ORDER');

    }

    
    /**
     * @group order-test-indomaret
     * */ 
    public function test_webhook_cancel_order() : void
    {
        $order = $this->set_data_order_factory('pending'); //status awal pending

        //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_indomaret_payload('202' , 'cancel');// set status terbaru menjadi cancel
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan berhasil dengan respons 200 OK
        $response->assertStatus(200);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $payload['transaction_status'], //harus menjadi cancel
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'cstore',
            'pricing_type' => 'Mega vip'
        ]);

        $this->assertDatabaseMissing('pricing_orders', [
            'transaction_status' => $order->transaction_status, //status pending harus berubah
        ]);

        //pada view list user order status order harusnya menjadi cancel
        $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
        $view_list_user_order->assertSee(strtoupper($payload['transaction_status']));
        $view_list_user_order->assertSee('CSTORE');
        $view_list_user_order->assertSee('Indomaret');
        //cancel order dan ubah metode bayar seharusnya sudah tidak tersedia lagi
        $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');
        $view_list_user_order->assertDontSee('CANCEL ORDER');

        //user tidak memiliki hak akses vip
        $this->assertDatabaseCount('vip_users' , 0);
        $this->assertDatabaseMissing('vip_users' , [
            'user_id' => $this->customer->id,
            'pricing_id' => $this->pricing->id,
            'vip_duration' => Carbon::now()->addDays($this->pricing->duration)
        ]);

    }


     /**
     * @group order-test-indomaret
     * */ 
    public function test_webhook_cancel_order_failed_case() : void
    {
    
       $order = $this->set_data_order_factory('settlement'); //status awal sudah settlement (fail case)

       //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_indomaret_payload('202' , 'cancel');// set status terbaru menjadi cancel
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan harus gagal
        $response->assertStatus(412);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $order->transaction_status, //status masih sama settlement
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'cstore',
            'pricing_type' => 'Mega vip'
        ]);

         //pada view list user order status order harusnya masih settlement
         $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
         $view_list_user_order->assertSee(strtoupper($order->transaction_status));
         $view_list_user_order->assertSee('CSTORE');
         $view_list_user_order->assertSee('Indomaret');
         //cancel order dan ubah metode pembayaran harusnya tidak tersedia karena status pesanan saat ini settlement
         $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');
         $view_list_user_order->assertDontSee('CANCEL ORDER');

    }

    
     /**
     * @group order-test-indomaret
     * */ 
    public function test_webhook_expired_order() : void
    {
        $order = $this->set_data_order_factory('pending'); //status awal pending

        //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_indomaret_payload('202' , 'expire');// set status terbaru menjadi expire
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan berhasil dengan respons 200 OK
        $response->assertStatus(200);
     
        $this->assertDatabaseEmpty('pricing_orders');//pastikan bahwa order yang expired sudah otomatis terhapus pada database
        $this->assertDatabaseMissing('pricing_orders', [
            'order_id' => $order->order_id, //order yang dibuat tadi sudah terhapus
        ]);

        //pada view list user order, order yang sudah expired tidak akan ada
        $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
        $view_list_user_order->assertDontSee(strtoupper($payload['transaction_status']));
        $view_list_user_order->assertDontSee('CSTORE');
        $view_list_user_order->assertDontSee('Indomaret');
        $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');
        $view_list_user_order->assertDontSee('CANCEL ORDER');

        //user tidak memiliki hak akses vip
        $this->assertDatabaseCount('vip_users' , 0);
        $this->assertDatabaseMissing('vip_users' , [
            'user_id' => $this->customer->id,
            'pricing_id' => $this->pricing->id,
            'vip_duration' => Carbon::now()->addDays($this->pricing->duration)
        ]);
 
    }


     /**
     * @group order-test-indomaret
     * */ 
    public function test_webhook_expired_order_failed_case() : void
    {
    
       $order = $this->set_data_order_factory('settlement'); //status awal sudah settlement (fail case)

       //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_indomaret_payload('202' , 'expire');// set status terbaru menjadi expire
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan harus gagal
        $response->assertStatus(412);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934666',
            'transaction_status' => $order->transaction_status, //status masih sama settlement
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'cstore',
            'pricing_type' => 'Mega vip'
        ]);

         //pada view list user order status order harusnya masih settlement
         $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
         $view_list_user_order->assertSee(strtoupper($order->transaction_status));
         $view_list_user_order->assertSee('CSTORE');
         $view_list_user_order->assertSee('Indomaret');
         //cancel order dan ubah metode pembayaran harusnya tidak tersedia karena status pesanan saat ini settlement
         $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');
         $view_list_user_order->assertDontSee('CANCEL ORDER');

    }



    private function set_data_order_factory(string $transaction_status) : object
    {
        $order = PricingOrder::factory()->create([
            'user_id' => $this->customer->id,
            'order_id' => 'PRCZ43455934666', 
            'transaction_status' => $transaction_status,
            'pricing_type' => 'Mega vip', //harus sama dengan nama pricing_name
            'payment_type' => 'cstore', //cstore merepresentasikan indomaret
            'pricing_price' => $this->pricing->price,
            'gross_amount' => $this->set_price()
        ]);
        
        return $order;
    }


    private function set_indomaret_payload(string $status_code , string $transaction_status) : array
    {
        $combine_str = 'PRCZ43455934666' . $status_code . $this->set_price() . env('MIDTRANS_SERVERKEY');
        $signature_key = hash('SHA512' , $combine_str);

        $payload = [
            "transaction_time" => "2023-07-10 21:49:45",
            "transaction_status" => $transaction_status,
            "transaction_id" => "cea97bbd-c062-4d9d-82f6-7a007750e52f",
            "store" => "indomaret",
            "status_message" => "midtrans payment notification",
            "status_code" => $status_code,
            "signature_key" => $signature_key,
            "settlement_time" => "2023-07-10 21:50:14",
            "payment_type" => "cstore",
            "payment_code" => "758111222333",
            "order_id" => "PRCZ43455934666",
            "merchant_id" => "G035482679",
            "gross_amount" => $this->set_price(),
            "expiry_time" => "2023-07-11 21:49:36",
            "currency" => "IDR",
            "approval_code" => $transaction_status == 'settlement' ? "92042307022233326343" : ""
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
