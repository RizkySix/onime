<?php

namespace Tests\Feature;

use App\Models\Pricing;
use App\Models\PricingOrder;
use App\Models\User;
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
            'price' => 100000
        ]);

        $this->customer = User::factory()->create();
    }
  
    /**
     * @group order-test
     * */  
    public function test_customer_can_access_pricing_list(): void
    {
       $response = $this->actingAs($this->customer)->get(route('pricing.index'))->assertStatus(200);
       $response->assertSee('Mega vip');
       $response->assertSee('Rp. 50,000'); 
       $response->assertSee('PURCHASE'); 
    }


    /**
     * @group order-test
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
     * @group order-test
     * */  
    /* public function test_payload_must_be_an_json_string() : void
    {
        $sampleData = [
            'order_id' => 666,
            'pricing_name' => $this->pricing->pricing_name,
            'transaction_status' => 'pending' 
        ];

        $json = json_encode($sampleData);
      
        $response = $this->actingAs($this->customer)->post(route('transaction' , $this->pricing->pricing_name) , [
            'order' => $json
        ])->assertStatus(200);
    } */
}
