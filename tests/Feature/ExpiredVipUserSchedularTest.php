<?php

namespace Tests\Feature;

use App\Jobs\UpdateExpiredVip;
use App\Models\Pricing;
use App\Models\User;
use App\Models\VipUser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use stdClass;
use Tests\TestCase;

class ExpiredVipUserSchedularTest extends TestCase
{
    use RefreshDatabase;
  
    protected function setUp(): void
    {
        parent::setUp();
       
    }
    /**
     * @group schedular-test
     */
    public function test_expired_vip_schedular_run_every_2_hours(): void
    {
        
     //test schedular dari pukul 12 malam sampai 12 malam (24jam)
        for($i = 0 ; $i <= 24; $i += 2){
            //NOTE--- Karena masih lokal atau belum production, scedular everyTwoHours() dijalankan secara default dari pukul 12 malam, 
            //Jika sudah production everyTwoHours() pada scedular akan dijalankan dimulai dari waktu aplikasi dipublish. SUMBER CHATGPT

            Carbon::setTestNow((string)$i . ':00:00');
            Queue::fake();
            $this->artisan('schedule:run'); //jalankan scheduler
            Queue::assertPushed(UpdateExpiredVip::class);
        }
       
    
    }

     /**
     * @group schedular-test
     */
    public function test_expired_vip_token_should_return_to_normal_token() : void
    {
        
        $yesterday = Carbon::now()->subDays(1); //set waktu kemarin
        $this->setToken(['vip-token'] , [
            'vip_power' => 'NORMAL',
            'vip_duration' => $yesterday
        ]);


        $this->assertDatabaseCount('pricings' , 1);
        $this->assertDatabaseHas('pricings' , ['vip_power' => 'normal']);
        $this->assertDatabaseCount('vip_users' , 1);
        $this->assertDatabaseHas('vip_users' , ['vip_duration' => $yesterday]);
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        $this->assertDatabaseHas('personal_access_tokens' , ['abilities' => json_encode(['vip-token'])]); // vip_power disimpan dalam format json di database

        //jalankan scedular case pukul 12 malam
        Carbon::setTestNow('00:00:00');
        $this->artisan('schedule:run'); //jalankan scheduler
       
        //seharusnya token menjadi normal-token karena vip_durationnya 1 hari dibawah hari ini
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        $this->assertDatabaseHas('personal_access_tokens' , ['abilities' => json_encode(['normal-token'])]);
        $this->assertDatabaseMissing('personal_access_tokens' , ['abilities' => json_encode(['vip-token'])]);
        

    }


     /**
     * @group schedular-test
     */
    public function test_expired_vip_token_with_super_vip_token_should_only_super_vip_token_left() : void
    {
        
        $yesterday = Carbon::now()->subDays(1); //set waktu kemarin
        $tomorrow = Carbon::now()->addDays(1);
        $this->setToken(['vip-token' , 'super-vip-token'] , [
            'vip_power' => 'NORMAL',
            'vip_duration' => $yesterday,
            'vip_duration_valid' => $tomorrow
        ]);


        $this->assertDatabaseCount('pricings' , 2);
        $this->assertDatabaseHas('pricings' , ['vip_power' => 'normal']);
        $this->assertDatabaseHas('pricings' , ['vip_power' => 'super']);
        $this->assertDatabaseCount('vip_users' , 2);
        $this->assertDatabaseHas('vip_users' , ['vip_duration' => $yesterday]);
        $this->assertDatabaseHas('vip_users' , ['vip_duration' => $tomorrow]);
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        $this->assertDatabaseHas('personal_access_tokens' , ['abilities' => json_encode(['vip-token' , 'super-vip-token'])]); // vip_power disimpan dalam format json di database

        //jalankan scedular case pukul 12 malam
        Carbon::setTestNow('00:00:00');
        $this->artisan('schedule:run'); //jalankan scheduler
       
        //seharusnya token menjadi normal-token karena vip_durationnya 1 hari dibawah hari ini
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        $this->assertDatabaseHas('personal_access_tokens' , ['abilities' => json_encode(['super-vip-token'])]);
        $this->assertDatabaseMissing('personal_access_tokens' , ['abilities' => json_encode(['vip-token' , 'super-vip-token'])]);
    

    }


    /**
     * @group schedular-test
     */
    public function test_normal_token_with_active_vip_should_upgrade_to_vip_token() : void
    {
        
        $tomorrow = Carbon::now()->addDays(1); //set waktu besok
        $this->setToken(['normal-token'] , [
            'vip_power' => 'NORMAL',
            'vip_duration' => $tomorrow
        ]);


        $this->assertDatabaseCount('pricings' , 1);
        $this->assertDatabaseHas('pricings' , ['vip_power' => 'normal']);
        $this->assertDatabaseCount('vip_users' , 1);
        $this->assertDatabaseHas('vip_users' , ['vip_duration' => $tomorrow]);
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        $this->assertDatabaseHas('personal_access_tokens' , ['abilities' => json_encode(['normal-token'])]); // vip_power disimpan dalam format json di database

        //jalankan scedular case pukul 12 malam
        Carbon::setTestNow('00:00:00');
        $this->artisan('schedule:run'); //jalankan scheduler
       
        //seharusnya token menjadi normal-token karena vip_durationnya 1 hari dibawah hari ini
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        $this->assertDatabaseHas('personal_access_tokens' , ['abilities' => json_encode(['vip-token'])]);
        $this->assertDatabaseMissing('personal_access_tokens' , ['abilities' => json_encode(['normal-token'])]);
        

    }


     /**
     * @group schedular-test
     */
    public function test_vip_token_with_super_vip_token_with_active_vip_nothing_should_change() : void
    {
        
        $tomorrow = Carbon::now()->addDays(1);
        $this->setToken(['vip-token' , 'super-vip-token'] , [
            'vip_power' => 'NORMAL',
            'vip_duration' => $tomorrow,
            'vip_duration_valid' => $tomorrow
        ]);


        $this->assertDatabaseCount('pricings' , 2);
        $this->assertDatabaseHas('pricings' , ['vip_power' => 'normal']);
        $this->assertDatabaseHas('pricings' , ['vip_power' => 'super']);
        $this->assertDatabaseCount('vip_users' , 2);
        $this->assertDatabaseHas('vip_users' , ['vip_duration' => $tomorrow]);
       
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        $this->assertDatabaseHas('personal_access_tokens' , ['abilities' => json_encode(['vip-token' , 'super-vip-token'])]); // vip_power disimpan dalam format json di database

        //jalankan scedular case pukul 12 malam
        Carbon::setTestNow('00:00:00');
        $this->artisan('schedule:run'); //jalankan scheduler
       
        //seharusnya token menjadi normal-token karena vip_durationnya 1 hari dibawah hari ini
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        $this->assertDatabaseHas('personal_access_tokens' , ['abilities' => json_encode(['vip-token' , 'super-vip-token'])]);
       
    }


    

    private function setToken(array $tokenAbilitiy, array $data) : void
    {
        $user = User::factory()->create();
        $user->createToken('onime-test-token' , $tokenAbilitiy);

        //buat pricing
        $pricing = Pricing::factory()->create([
            'vip_power' => $data['vip_power']
        ]);

        //buat VipUser
        VipUser::create([
            'user_id' => $user->id,
            'pricing_id' => $pricing->id,
            'vip_duration' => $data['vip_duration']
        ]);

        //jika ada vip_duraton_valid
        if(isset($data['vip_duration_valid'])){
            $pricing2 = Pricing::factory()->create([
                'vip_power' => 'SUPER'
            ]);
            
              //buat VipUser
            VipUser::create([
                'user_id' => $user->id,
                'pricing_id' => $pricing2->id,
                'vip_duration' => $data['vip_duration_valid']
            ]);
        }

    }

   
}
