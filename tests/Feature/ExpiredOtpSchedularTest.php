<?php

namespace Tests\Feature;

use App\Jobs\ExpiredOtpDelete;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ExpiredOtpSchedularTest extends TestCase
{
   use RefreshDatabase;

    private $otpInvalid;
    private $otpValid;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('01:00:00');
        $otp = [
            [
            'user_id' => 1,
            'otp_code' => rand(100000,999999),
            'expired_time' => Carbon::now()->subDays(1) //invalid
            ],
            [
            'user_id' => 2,
            'otp_code' => rand(100000,999999),
            'expired_time' => Carbon::now()->addMinutes(2) //valid
            ]
        ];

        DB::table('otps_codes')->insert($otp);
        $this->otpInvalid = $otp[0];
        $this->otpValid = $otp[1];

    }

       /**
     * @group schedular-test
     */
    public function test_schedular_delete_expired_otp_code_job_should_work_in_1_am(): void
    {
        Carbon::setTestNow('01:00:00'); //set jam 1 pagi ,  Carbon::now() juga otomatis akan menjadi jam 1 pagi

        $this->assertDatabaseCount('otps_codes' , 2); //harusnya data otp ada 2
      
       Queue::fake();//karena menggunakan Queue:fake(), aksi sesungguhnya pada JOB tidak akan dijalankan sehingga tidak bisa assert atau memastikan expired otp sudah terhapus atau belum, untuk memastikannya dilakukan pada func test setelah ini
        $this->artisan('schedule:run'); //jalankan scheduler
       
        Queue::assertPushed(ExpiredOtpDelete::class);

    }


     /**
     * @group schedular-test
     */
    public function test_schedular_delete_expired_otp_code_job_should_work_in_13_pm(): void
    {
        Carbon::setTestNow('13:00:00'); //set jam 1 pagi ,  Carbon::now() juga otomatis akan menjadi jam 1 pagi

        $this->assertDatabaseCount('otps_codes' , 2); //harusnya data otp ada 2
      
       Queue::fake();//karena menggunakan Queue:fake(), aksi sesungguhnya pada JOB tidak akan dijalankan sehingga tidak bisa assert atau memastikan expired otp sudah terhapus atau belum, untuk memastikannya dilakukan pada func test setelah ini
        $this->artisan('schedule:run'); //jalankan scheduler
       
        Queue::assertPushed(ExpiredOtpDelete::class);

    }


 
      /**
     * @group schedular-test
     */
    public function test_expired_otp_should_deleted_after_scheduler_run() :void
    {
       Carbon::setTestNow('01:00:00'); //set jam 1 pagi ,  Carbon::now() juga otomatis akan menjadi jam 1 pagi

        $this->assertDatabaseCount('otps_codes' , 2); //harusnya data otp ada 2
      
        $this->artisan('schedule:run'); //jalankan scheduler
      
        //cek database
        $this->assertDatabaseCount('otps_codes' , 1);
        $this->assertDatabaseMissing('otps_codes' , $this->otpInvalid);
        $this->assertDatabaseHas('otps_codes' , $this->otpValid);
    }

    
}
