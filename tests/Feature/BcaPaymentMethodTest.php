<?php

namespace Tests\Feature;

use App\Models\Pricing;
use App\Models\PricingOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BcaPaymentMethodTest extends TestCase
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
     * @group order-test-bca
     * */  
    public function test_input_order_data_for_bca() : void
    {
        $payload = $this->set_bca_payload(201 , 'pending');

        //payload harus dirubah menjadi string json agar dapat diolah di controller
        $payload_str = json_encode($payload);

        $response = $this->actingAs($this->customer)->post(route('transaction' , $this->pricing->pricing_name) , [
            'order' => $payload_str
        ]);

        $this->assertDatabaseCount('pricing_orders' , 1); //harusnya data order baru ditambahkan
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => $payload['order_id'],
            'transaction_status' => $payload['transaction_status'],
            'payment_type' => 'bca',
            'gross_amount' => $payload['gross_amount']
        ]);
    
        $response->assertStatus(302);
        $response->assertRedirect('/transaction-done/' . $payload['order_id']); //seharusnya diarahkan ke route berikut
        //cek konten pada halaman transaction done
       $view_trans_done = $this->actingAs($this->customer)->get(route('transaction-done' , $payload['order_id']))->assertStatus(200);
       $view_trans_done->assertSee(strtoupper($payload['transaction_status']));
       $view_trans_done->assertSee('BCA');
       $view_trans_done->assertSee('UBAH METODE PEMBAYARAN');//btn ubah metode bayar
       $view_trans_done->assertDontSee('CANCEL ORDER');//btn cancel order tidak ada pada view transaction done

       //cek harusnya order sudah ada pada view list user order
       $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
       $view_list_user_order->assertSee(strtoupper($payload['transaction_status']));
       $view_list_user_order->assertSee('BCA');
       $view_list_user_order->assertSee('UBAH METODE PEMBAYARAN');//btn ubah metode bayar
       $view_list_user_order->assertSee('CANCEL ORDER');//btn cancel order tersedia pada list user order

       //karena status order masih pending harusnya user belum mendapatkan hak akses VIP
       $this->assertDatabaseCount('vip_users' , 0);
       $this->assertDatabaseMissing('vip_users' , [
        'pricing_id' => $this->pricing->id,
        'user_id' => $this->customer->id
       ]);
        
    }



    /**
     * @group order-test-bca
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

        //pada view list user order status order harusnya sudah settlement
        $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
        $view_list_user_order->assertSee(strtoupper($payload['transaction_status']));
        $view_list_user_order->assertSee('BCA');
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
     * @group order-test-bca
     * */ 
    public function test_webhook_settlement_order_failed_case() : void
    {
    
       $order = $this->set_data_order_factory('cancel'); //status awal cancel (fail case)

       //order sebelum diproses
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
       
        // Verifikasi bahwa permintaan harus gagal
        $response->assertStatus(412);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status, //status masih sama cancel
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'bca',
            'pricing_type' => 'Mega vip'
        ]);

          //pada view list user order status order harusnya masih cancel
          $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
          $view_list_user_order->assertSee(strtoupper($order->transaction_status));
          $view_list_user_order->assertSee('BCA');
          //cancel order dan ubah metode pembayaran harusnya tidak tersedia karena status pesanan saat ini cancel
          $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');
          $view_list_user_order->assertDontSee('CANCEL ORDER');

    }

    
    /**
     * @group order-test-bca
     * */ 
    public function test_webhook_cancel_order() : void
    {
        $order = $this->set_data_order_factory('pending'); //status awal pending

        //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_bca_payload('202' , 'cancel');// set status terbaru menjadi cancel
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan berhasil dengan respons 200 OK
        $response->assertStatus(200);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $payload['transaction_status'], //harus menjadi cancel
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'bca',
            'pricing_type' => 'Mega vip'
        ]);

        $this->assertDatabaseMissing('pricing_orders', [
            'transaction_status' => $order->transaction_status, //status pending harus berubah
        ]);

        //pada view list user order status order harusnya menjadi cancel
        $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
        $view_list_user_order->assertSee(strtoupper($payload['transaction_status']));
        $view_list_user_order->assertSee('BCA');
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
     * @group order-test-bca
     * */ 
    public function test_webhook_cancel_order_failed_case() : void
    {
    
       $order = $this->set_data_order_factory('settlement'); //status awal sudah settlement (fail case)

       //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_bca_payload('202' , 'cancel');// set status terbaru menjadi cancel
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan harus gagal
        $response->assertStatus(412);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status, //status masih sama settlement
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'bca',
            'pricing_type' => 'Mega vip'
        ]);

         //pada view list user order status order harusnya masih settlement
         $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
         $view_list_user_order->assertSee(strtoupper($order->transaction_status));
         $view_list_user_order->assertSee('BCA');
         //cancel order dan ubah metode pembayaran harusnya tidak tersedia karena status pesanan saat ini settlement
         $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');
         $view_list_user_order->assertDontSee('CANCEL ORDER');

    }

    
     /**
     * @group order-test-bca
     * */ 
    public function test_webhook_expired_order() : void
    {
        $order = $this->set_data_order_factory('pending'); //status awal pending

        //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_bca_payload('202' , 'expire');// set status terbaru menjadi expire
       
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
        $view_list_user_order->assertDontSee('BCA');
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
     * @group order-test-bca
     * */ 
    public function test_webhook_expired_order_failed_case() : void
    {
    
       $order = $this->set_data_order_factory('settlement'); //status awal sudah settlement (fail case)

       //order sebelum diproses
        $this->assertDatabaseCount('pricing_orders' , 1);
        $this->assertDatabaseHas('pricing_orders' , [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status,
            'gross_amount' => $order->gross_amount,
            'payment_type' => $order->payment_type,
            'pricing_type' => $order->pricing_type
        ]);

        $payload = $this->set_bca_payload('202' , 'expire');// set status terbaru menjadi expire
       
        // Kirim permintaan POST ke endpoint webhook dengan payload
        $response = $this->post(route('api.webhook'), $payload);
       
        // Verifikasi bahwa permintaan harus gagal
        $response->assertStatus(412);
     
        // Verifikasi bahwa aplikasi menangani callback/webhook dengan benar
        $this->assertDatabaseHas('pricing_orders', [
            'order_id' => 'PRCZ43455934857',
            'transaction_status' => $order->transaction_status, //status masih sama settlement
            'gross_amount' => $payload['gross_amount'],
            'payment_type' => 'bca',
            'pricing_type' => 'Mega vip'
        ]);

         //pada view list user order status order harusnya masih settlement
         $view_list_user_order = $this->actingAs($this->customer)->get(route('user.orders'))->assertStatus(200);
         $view_list_user_order->assertSee(strtoupper($order->transaction_status));
         $view_list_user_order->assertSee('BCA');
         //cancel order dan ubah metode pembayaran harusnya tidak tersedia karena status pesanan saat ini settlement
         $view_list_user_order->assertDontSee('UBAH METODE PEMBAYARAN');
         $view_list_user_order->assertDontSee('CANCEL ORDER');

    }



    private function set_data_order_factory(string $transaction_status) : object
    {
        $order = PricingOrder::factory()->create([
            'user_id' => $this->customer->id,
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
