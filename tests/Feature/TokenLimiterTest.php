<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TokenLimiterTest extends TestCase
{
    use RefreshDatabase;

    private $normalToken;
    private $vipToken;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->normalToken = $this->user->createToken('onime-test-token' , ['normal-token'])->plainTextToken;
        $this->vipToken = $this->user->createToken('onime-test-token' , ['vip-token'])->plainTextToken;
    
    }

    /**
     * @group test-limiter-endpoint
     */
    public function test_per_endpoint_limiter_work_for_normal_token(): void
    {
       //studi case hit ke endpoint normal anime
        $firstHit = $this->getJson('http://onime.test/api/ver1/animes' , [
            'Authorization' => 'Bearer ' . $this->normalToken
        ])->assertStatus(200);

        $cacheKey = 'normal-token-limiter:' . '/api/ver1/animes' . ':' . $this->user->id; //membuat key cache berdasarkan pathname dan id user

        Cache::put($cacheKey , 5001); // manipulasi hit menjadi 5001
     
        //hit kedua seharusnya mendapat status code 429
        $secondHit = $this->getJson('http://onime.test/api/ver1/animes' , [
            'Authorization' => 'Bearer ' . $this->normalToken
        ])->assertStatus(429);

        $secondHit->assertTooManyRequests();

    }

    /**
     * @group test-limiter-endpoint
     */
    public function test_vip_token_no_need_limiter() : void
    {
         //studi case hit ke endpoint normal anime
         $firstHit = $this->getJson('http://onime.test/api/ver1/animes' , [
            'Authorization' => 'Bearer ' . $this->vipToken
        ])->assertStatus(200);

        $cacheKey = 'normal-token-limiter:' . '/api/ver1/animes' . ':' . $this->user->id; //membuat key cache berdasarkan pathname dan id user
        
        $getCache = Cache::get($cacheKey);
        $this->assertEquals(null , $getCache); //cache harusnya tidak dibuat karena token yang digunakan adalah vip
        
       Cache::put($cacheKey , 5001); // manipulasi hit menjadi 5001
       $limitCache = Cache::get($cacheKey);
        
        $this->assertEquals(5001 , $limitCache); // cache untuk token ini harusnya sudah 5001     

        $secondHit = $this->getJson('http://onime.test/api/ver1/animes' , [
            'Authorization' => 'Bearer ' . $this->vipToken
        ])->assertStatus(200); //akan tetap berhasil karena vip token
    }
}
