<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenerateTokenTest extends TestCase
{
    use RefreshDatabase;
    private $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create([
            'admin' => false
        ]);
    }
    /**
     * @group dashboard-test
     */
    public function test_generate_token_only_once_per_24_hours(): void
    {
        //set waktu awal
        Carbon::setTestNow('2023-09-01 00:00:00');

        $view_dashboard = $this->actingAs($this->customer)->get(route('dashboard'))->assertStatus(200);
        $view_dashboard->assertSee('GENERATE');
        $view_dashboard->assertSee('Generate Token Tersedia');
        $view_dashboard->assertDontSee('Generate kembali dalam');
        
        //pastikan user belum memiliki token saat ini
        $this->assertEquals(null , $this->customer->token);

        //hit generate token
        $response = $this->actingAs($this->customer)->post(route('token-maker'))->assertStatus(302);
        $response->assertRedirectToRoute('dashboard');
        
        $view_dashboard = $this->actingAs($this->customer)->get(route('dashboard'))->assertStatus(200);
        $view_dashboard->assertDontSee('Generate Token Tersedia');
        $view_dashboard->assertSee('Generate kembali dalam');

        //user sudah memiliki token
        $token = $this->customer->token;
        $this->assertNotEquals(null , $token);
        $view_dashboard->assertSee($token);

        //coba hit kembali
        $response = $this->actingAs($this->customer)->post(route('token-maker'))->assertStatus(302);
        $response->assertRedirectToRoute('dashboard');
        $response->assertSessionHas('limit' , 'Generate Token Once Per Day!');

        $view_dashboard = $this->actingAs($this->customer)->get(route('dashboard'))->assertStatus(200);
        $view_dashboard->assertSee($token);//token tidak digenerate kembali

        //set waktu menjadi esok hari
        Carbon::setTestNow('2023-09-02 00:01:00');

         //coba hit kembali
         $response = $this->actingAs($this->customer)->post(route('token-maker'))->assertStatus(302); 
         $response->assertRedirectToRoute('dashboard');

         $view_dashboard = $this->actingAs($this->customer)->get(route('dashboard'))->assertStatus(200);
         $view_dashboard->assertDontSee($token);//token lama sudah tidak ada

         $newToken = $this->customer->token; //panggil token terbaru
         $view_dashboard->assertSee($newToken);//token baru tampil
         
      
        
    }
}
