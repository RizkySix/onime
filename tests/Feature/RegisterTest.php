<?php

namespace Tests\Feature;

use App\Mail\OtpSendMail;
use App\Models\OtpsCode;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
   
    /**
     * @group register-test
     */
    public function test_register_view_can_be_rendered(): void
    {
       $this->get(route('register'))->assertStatus(200);
    }

    /**
     * @group register-test
     */
    public function test_new_user_can_register_and_otp_mail_queued(): void
    {
    
        Mail::fake();
       $response = $this->post(route('register') , [
        'name' => 'Leah Notty',
        'email' => 'leah@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
       ]);

       $this->assertAuthenticated();
       $response->assertRedirect(RouteServiceProvider::HOME);

       $this->assertDatabaseHas('users' , ['name' => 'Leah Notty']);
     
       Mail::assertQueued(OtpSendMail::class , function ($mail) {
        return $mail->hasTo('leah@gmail.com');
     }); //karena email menggunakan qeueable maka assertSent tidak dapat digunakan

     //pastikan kode otp sudah ada
     $this->assertDatabaseCount('otps_codes' , 1);
     $this->assertDatabaseHas('otps_codes' , ['user_id' => User::first()->id]);

    }


      /**
     * @group register-test
     */
    public function test_user_can_access_verify_otp_view() : void
    {
     
        $user =  User::factory()->create([
            'email_verified_at' => null
        ]);
        $this->actingAs($user)->get(route('view-verif-otp'))->assertStatus(200);
       
    }

    /**
     * @group register-test
     */
    public function test_new_user_can_resend_otp_mail(): void
    {

        //akan otomatis membuat default otp karena menggunakan observer
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        
        //pastikan default otp ada
        $default_otp = OtpsCode::first();
        $this->assertDatabaseCount('otps_codes' , 1);
        $this->assertDatabaseHas('otps_codes' , [
            'user_id' => $user->id,
            'otp_code' => $default_otp->otp_code
        ]);
        
        Mail::fake();
        
        //resend-otp
        $this->actingAs($user)->post(route('resend-otp'))->assertStatus(302);
        Mail::assertQueued(OtpSendMail::class , function ($mail) use($user){
            return $mail->hasTo($user->email);
         }); //karena email menggunakan qeueable maka assertSent tidak dapat digunakan
    

        //pastikan jumlah otp masih satu tetapi kode otp sudah berubah
        $this->assertDatabaseCount('otps_codes' , 1);
        $this->assertDatabaseMissing('otps_codes' , [
            'user_id' => $user->id,
            'otp_code' => $default_otp->otp_code
        ]);

        //ambil kembali otp
        $new_otp = OtpsCode::first();
        $this->assertDatabaseHas('otps_codes' , [
            'user_id' => $user->id,
            'otp_code' => $new_otp->otp_code
        ]);
        

    }


     /**
     * @group register-test
     */
    public function test_otp_code_expired_in_60_minutes() : void
    {
        //akan otomatis membuat default otp karena menggunakan observer
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        //pastikan default otp ada
        $default_otp = OtpsCode::first();

        //travel waktu ke 60 menit kedepan
        Carbon::setTestNow(Carbon::now()->addMinutes(60));

        $response = $this->actingAs($user)->post(route('send-otp' , ['id' => $user->id]) , [
            'otp_code' => $default_otp->otp_code
        ])->assertStatus(302);
        $response->assertSessionHas('invalid' , 'invalid otp code');
       
         //pastikan user email belum terverifikasi
         $this->assertEquals(null ,  User::first()->email_verified_at);
    }



     /**
     * @group register-test
     */
    public function test_otp_code_should_only_for_one_user() : void
    {
        //akan otomatis membuat default otp karena menggunakan observer
        $user1 = User::factory()->create([
            'email_verified_at' => null
        ]);

        $user2 = User::factory()->create([
            'email_verified_at' => null
        ]);

        //ambil otp user1
        $first_otp_user = OtpsCode::first();
    
        //mengirim id dari user2 
        $response = $this->actingAs($user2)->post(route('send-otp' , ['id' => $user2->id]) , [
            'otp_code' => $first_otp_user->otp_code
        ])->assertStatus(302);
        $response->assertSessionHas('invalid' , 'invalid otp code');

         //pastikan user email belum terverifikasi
         $this->assertEquals(null ,  User::latest()->first()->email_verified_at);
       
    }


    /**
     * @group register-test
     */
    public function test_invalid_length_otp_code() : void
    {
        //akan otomatis membuat default otp karena menggunakan observer
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

    
        $response = $this->actingAs($user)->post(route('send-otp' , ['id' => $user->id]) , [
            'otp_code' => '1234567' //invalid 7 digit otp code, harusnya 8 digit
        ])->assertStatus(302);
        $response->assertInvalid('otp_code');

        //pastikan user email belum terverifikasi
        $this->assertEquals(null ,  User::first()->email_verified_at);
    }


      /**
     * @group register-test
     */
    public function test_valid_otp_code_email_should_verified() : void
    {
        //akan otomatis membuat default otp karena menggunakan observer
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        //pastikan default otp ada
        $default_otp = OtpsCode::first();

        $response = $this->actingAs($user)->post(route('send-otp' , ['id' => $user->id]) , [
            'otp_code' => $default_otp->otp_code
        ])->assertStatus(302);
        $response->assertRedirect(route('dashboard'));

        //pastikan email user sudah terverifikasi
        $expected_format = Carbon::now()->format('Y-m-d');
        $actual_format = User::first()->email_verified_at->format('Y-m-d'); //email_verified_at sudah dalam bentuk carbon sehingga dapt langsung menggunakan method format()
        $this->assertEquals($expected_format , $actual_format);
    }



     /**
     * @group register-test
     */
    public function test_invalid_direct_otp_verification_with_expired_otp() : void
    {
        //akan otomatis membuat default otp karena menggunakan observer
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        //pastikan default otp ada
        $default_otp = OtpsCode::first();

         //travel waktu ke 60 menit kedepan
         Carbon::setTestNow(Carbon::now()->addMinutes(60));

       $response = $this->actingAs($user)->get(route('otp-direct-verified' , [
            'id' => $user->id , 
            'otp_code' => $default_otp->otp_code ,  
            'otp_direct_verified' => 'true'
        ]))->assertStatus(302);
        $response->assertSessionHas('invalid' , 'invalid otp code');
      
         //pastikan email user belum terverifikasi
         $this->assertEquals(null , User::first()->email_verified_at);
    }


    /**
     * @group register-test
     */
    public function test_valid_direct_otp_verification() : void
    {
        //akan otomatis membuat default otp karena menggunakan observer
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        //pastikan default otp ada
        $default_otp = OtpsCode::first();

       $response = $this->actingAs($user)->get(route('otp-direct-verified' , [
            'id' => $user->id , 
            'otp_code' => $default_otp->otp_code ,  
            'otp_direct_verified' => 'true'
        ]))->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
      
         //pastikan email user sudah terverifikasi
         $expected_format = Carbon::now()->format('Y-m-d');
         $actual_format = User::first()->email_verified_at->format('Y-m-d'); //email_verified_at sudah dalam bentuk carbon sehingga dapt langsung menggunakan method format()
         $this->assertEquals($expected_format , $actual_format);

       
    }
    
}
