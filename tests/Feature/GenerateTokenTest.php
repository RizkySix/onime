<?php

namespace Tests\Feature;

use App\Http\Middleware\Customer;
use App\Models\Pricing;
use App\Models\User;
use App\Models\VipUser;
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
        
        //pastikan user belum memiliki token saat ini
        $this->assertEquals(null , $this->customer->token);

        //hit generate token
        $response = $this->actingAs($this->customer)->post(route('token-maker'))->assertStatus(302);
        $response->assertRedirectToRoute('dashboard');
        
        $view_dashboard = $this->actingAs($this->customer)->get(route('dashboard'))->assertStatus(200);

        //user sudah memiliki token
        $this->assertDatabaseCount('personal_access_tokens' , 1);
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
         $this->assertDatabaseCount('personal_access_tokens' , 1);//pastikan jumlah token masih 1 menandakan token lama telah diganti
         
    }

    /**
     * @group dashboard-test
     */
    public function test_normal_user_should_generate_normal_token() : void
    {
        //hit generate token
        $response = $this->actingAs($this->customer)->post(route('token-maker'))->assertStatus(302);
        $response->assertRedirectToRoute('dashboard');
        $this->assertNotEquals(null , $this->customer->token);

        //pastikan token user memiliki ability normal-token
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        $this->assertDatabaseHas('personal_access_tokens' , [
            'tokenable_id' => $this->customer->id,
            'abilities' => json_encode(['normal-token']) // ability disimpan dalam string json
        ]);
      
    }


    /**
     * @group dashboard-test
     */
    public function test_vip_user_should_generate_vip_token() : void
    {
        //set pricing power
        $pricing = $this->set_pricing('NORMAL'); //set NORMAL VIP
        //buat vip
        $this->set_vip($pricing);

        $this->assertDatabaseCount('vip_users' , 1);
        $this->assertDatabaseHas('vip_users' , [
            'pricing_id' => $pricing->id,
            'user_id' => $this->customer->id,
        ]);

      //hit generate token
      $response = $this->actingAs($this->customer)->post(route('token-maker'))->assertStatus(302);
      $response->assertRedirectToRoute('dashboard');
      $this->assertNotEquals(null , $this->customer->token);

      //pastikan token user memiliki ability normal-token
      $this->assertDatabaseCount('personal_access_tokens' , 1);
      $this->assertDatabaseHas('personal_access_tokens' , [
          'tokenable_id' => $this->customer->id,
          'abilities' => json_encode(['vip-token']) // ability disimpan dalam string json
      ]);

    }




     /**
     * @group dashboard-test
     */
    public function test_multiple_vip_user_should_generate_multiple_vip_token() : void
    {
        //set pricing power
        $pricing_normal_vip = $this->set_pricing('NORMAL'); //set NORMAL VIP
        $pricing_super_vip = $this->set_pricing('SUPER'); //set SUPER VIP
        //buat vip
        $this->set_vip($pricing_normal_vip);
        $this->set_vip($pricing_super_vip);

        $this->assertDatabaseCount('vip_users' , 2);
        $this->assertDatabaseHas('vip_users' , [
            'pricing_id' => $pricing_normal_vip->id,
            'user_id' => $this->customer->id,
        ]);
        $this->assertDatabaseHas('vip_users' , [
            'pricing_id' => $pricing_super_vip->id,
            'user_id' => $this->customer->id,
        ]);

      //hit generate token
      $response = $this->actingAs($this->customer)->post(route('token-maker'))->assertStatus(302);
      $response->assertRedirectToRoute('dashboard');
      $this->assertNotEquals(null , $this->customer->token);

      //pastikan token user memiliki ability vip-token dan super-vip-token
      $this->assertDatabaseCount('personal_access_tokens' , 1);
      $this->assertDatabaseHas('personal_access_tokens' , [
          'tokenable_id' => $this->customer->id,
          'abilities' => json_encode(['vip-token' , 'super-vip-token']) // ability disimpan dalam string json
      ]);

    }



     /**
     * @group dashboard-test
     */
    public function test_expired_vip_user_should_generate_down_grade_token() : void
    {
        //set pricing power
        $pricing_normal_vip = $this->set_pricing('NORMAL'); //set NORMAL VIP
        $pricing_super_vip = $this->set_pricing('SUPER'); //set SUPER VIP
        //buat vip
        $this->set_vip($pricing_normal_vip);
        $this->set_vip($pricing_super_vip);

        $this->assertDatabaseCount('vip_users' , 2);
        $this->assertDatabaseHas('vip_users' , [
            'pricing_id' => $pricing_normal_vip->id,
            'user_id' => $this->customer->id,
        ]);
        $this->assertDatabaseHas('vip_users' , [
            'pricing_id' => $pricing_super_vip->id,
            'user_id' => $this->customer->id,
        ]);

        //manual create token
        $this->customer->createToken('onime-test-token' , ['vip-token' , 'super-vip-token']);

      //pastikan token user memiliki ability vip-token dan super-vip-token
      $this->assertDatabaseCount('personal_access_tokens' , 1);
      $this->assertDatabaseHas('personal_access_tokens' , [
          'tokenable_id' => $this->customer->id,
          'abilities' => json_encode(['vip-token' , 'super-vip-token']) // ability disimpan dalam string json
      ]);


       //set waktu pertama
        Carbon::setTestNow('2023-09-01 00:00:00');

      
      //expiredkan super vip
      VipUser::where('pricing_id' , $pricing_super_vip->id)
            ->where('user_id' , $this->customer->id)
            ->update(['vip_duration' => Carbon::now()->subDays(1)]);//turunkan menjadi hari kemarin

    //cek vip user
    $this->assertDatabaseHas('vip_users' , [
        'pricing_id' => $pricing_super_vip->id,
        'user_id' => $this->customer->id,
        'vip_duration' => Carbon::now()->subDays(1)
    ]);

      //hit generate token
      $this->actingAs($this->customer)->post(route('token-maker'))->assertStatus(302);
  
      //pastikan token user hanya menyisakan memiliki ability vip-token 
      $this->assertDatabaseCount('personal_access_tokens' , 1);
      $this->assertDatabaseHas('personal_access_tokens' , [
          'tokenable_id' => $this->customer->id,
          'abilities' => json_encode(['vip-token']) // ability disimpan dalam string json
      ]);



       //set waktu kedua
       Carbon::setTestNow('2023-09-02 00:01:00');

      
       //expiredkan normal vip
       VipUser::where('pricing_id' , $pricing_normal_vip->id)
             ->where('user_id' , $this->customer->id)
             ->update(['vip_duration' => Carbon::now()->subDays(1)]);//turunkan menjadi hari kemarin
 
     //cek vip user
     $this->assertDatabaseHas('vip_users' , [
         'pricing_id' => $pricing_normal_vip->id,
         'user_id' => $this->customer->id,
         'vip_duration' => Carbon::now()->subDays(1)
     ]);
 
       //hit generate token
       $this->actingAs($this->customer)->post(route('token-maker'))->assertStatus(302);
   
       //pastikan token user hanya menyisakan memiliki ability vip-token 
       $this->assertDatabaseCount('personal_access_tokens' , 1);
       $this->assertDatabaseHas('personal_access_tokens' , [
           'tokenable_id' => $this->customer->id,
           'abilities' => json_encode(['normal-token']) // ability disimpan dalam string json
       ]);

    }


    private function set_pricing(string $power) : object 
    {
        $pricing = Pricing::factory()->create([
            'vip_power' => $power
        ]); 

        return $pricing;
    }

    private function set_vip(Pricing $pricing) : void
    {
        VipUser::factory()->create([
            'pricing_id' => $pricing->id,
            'user_id' => $this->customer->id,
            'vip_duration' => Carbon::now()->addDay($pricing->duration)
        ]);
    }   
}
