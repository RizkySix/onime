<?php

namespace Tests\Feature;

use App\Models\AnimeName;
use App\Models\AnimeRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;

class AllVipAnimeTest extends TestCase
{
    use RefreshDatabase; // JANGAN COBA2 MENGGUNAKAN INI DI LIVE/MAIN DATABASE

    private $getToken;
    private $getTokenVip;
 
    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
       
       for($i = 1; $i <= 15; $i++){
        $animeCreate = AnimeName::withoutEvents(function() {
            $animeNew = AnimeName::create([
                    'anime_name' => rand().'rizky',
                    'slug' => Str::slug(Str::random(10)),
                    'total_episode' => 24,
                    'studio' => 'mappa',
                    'author' => 'gua',
                    'vip' => rand(0 ,1),
                    'description' => 'kosong'
                ]);
    
                return $animeNew;
            });
           
    
            AnimeRating::create([
                'anime_name_id' => $animeCreate->id,
                'point' => rand(),
                'participan' => rand(),
                'rating' => rand(1 , 10)
            ]);
       }

        $this->getToken = $user->createToken('onime-test-token' , ['normal-token'])->plainTextToken;
        $this->getTokenVip = $user->createToken('onime-test-token' , ['vip-token'])->plainTextToken;
    }

    

    /**
     * @group api-get-anime-vip
     */
    public function test_api_endpoint_all_anime() : void
    {
        $responseFail = $this->get('http://onime.test/api/ver1/animes-vip');
        $responseSuccess = $this->get('http://onime.test/api/ver1/animes-vip' , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ]);

        $responseFail->assertStatus(302);
        $responseSuccess->assertStatus(200);
        
    }

    /**
     * @group api-get-anime-vip
     */
    public function test_invalid_for_normal_token() : void
    {
        $responseSuccess = $this->get('http://onime.test/api/ver1/animes-vip' , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(403);
    }


    /**
     * @group api-get-anime-vip
     */
    public function test_should_response_json() :void
    {
        $response = $this->getJson('http://onime.test/api/ver1/animes-vip' , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

       
    }

    /**
     * @group api-get-anime-vip
     */
    public function test_should_response_404_page_request_over_than_10() : void
    {
        $response = $this->get('http://onime.test/api/ver1/animes-vip?page=11' , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(404);
       
    }

     /**
     * @group api-get-anime-vip
     */
    public function test_all_anime_should_vip_true() : void
    {
        $response = $this->get('http://onime.test/api/ver1/animes-vip' , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

        $responseData = $response->json();
        $animes = $responseData['animes'];
            
        if($animes == null){
            $response->assertOk();
        }else{
            foreach($animes as $anime){
                $anime['vip'] == true ? : $response->assertInvalid();
            }
        }

    }

     /**
     * @group api-get-anime-vip
     */
    public function test_find_anime_parameter() : void
    {
        $response = $this->get( 'http://onime.test/api/ver1/animes-vip?find_anime=ri'  , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

        $responseData = $response->json();

        if($responseData['animes'] == null){
            $response->assertOk();
        }else{
            foreach($responseData['animes'] as $anime){
                if(!str_contains($anime['anime_name'] , 'ri') || $anime['vip'] == false){
                    $response->assertInvalid();
                }
            }
        
        }

    }

    /**
     * @group api-get-anime-vip
     */
    public function test_order_by_rating_true() : void
    {
        $response = $this->get( 'http://onime.test/api/ver1/animes-vip?rating=true'  , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

        $responseData = $response->json();

        if($responseData['animes'] == null){
            $response->assertOk();
        }else{
            
            $ratings = [];
            foreach($responseData['animes'] as $anime){
                $ratings[] = $anime['rating'];
                $anime['vip'] == true ? : $response->assertInvalid();
            }
    
            $newRating = $ratings;
    
            arsort($ratings);
            
            $this->assertEquals(implode(' ' , $newRating) , implode(' ' , $ratings));
           
        }

        

    }
    

    

}
