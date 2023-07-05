<?php

namespace Tests\Feature;

use App\Models\AnimeName;
use App\Models\AnimeRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Illuminate\Support\Str;
use stdClass;

class AllAnimeTest extends TestCase
{
    use RefreshDatabase; // JANGAN COBA2 MENGGUNAKAN INI DI LIVE/MAIN DATABASE

    private $getToken;
 
    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
       
       for($i = 1; $i <= 5; $i++){
        $animeCreate = AnimeName::withoutEvents(function() {
            $animeNew = AnimeName::create([
                    'anime_name' => rand().'rizky',
                    'slug' => Str::slug(Str::random(10)),
                    'total_episode' => 24,
                    'studio' => 'mappa',
                    'author' => 'gua',
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
    }

    

    /**
     * @group api-get-anime
     */
    public function test_api_endpoint_all_anime() : void
    {
        $responseFail = $this->get(route('api.all-anime'));
        $responseSuccess = $this->get(route('api.all-anime') , [
            'Authorization' => 'Bearer ' . $this->getToken
        ]);

        $responseFail->assertStatus(302);
        $responseSuccess->assertStatus(200);
        
    }

    /**
     * @group api-get-anime
     */
    public function test_should_response_json() :void
    {
        $response = $this->get(route('api.all-anime') , [
            'Authorization' => 'Bearer ' . $this->getToken
        ]);

        $response->assertJsonIsObject();
    }

    /**
     * @group api-get-anime
     */
    public function test_should_response_404_page_request_over_than_10() : void
    {
        $response = $this->get(route('api.all-anime' , ['page' => 11]) , [
            'Authorization' => 'Bearer ' . $this->getToken
        ]);

        $response->assertStatus(404);
       
    }


     /**
     * @group api-get-anime
     */
    public function test_find_anime_parameter() : void
    {
        $response = $this->getJson( route('api.all-anime' , ['find_anime' => 'ri'])  , [
            'Authorization' => 'Bearer ' . $this->getToken
        ]);

        $responseData = $response->getOriginalContent();

        if($responseData['animes'] == null){
            $response->assertOk();
        }

        foreach($responseData['animes'] as $anime){
            if(!str_contains($anime['anime_name'] , 'ri')){
                $response->assertInvalid();
            }
        }
       $response->assertOk();
    }

    /**
     * @group api-get-anime
     */
    public function test_order_by_rating_true() : void
    {
        $response = $this->getJson( route('api.all-anime' , ['rating' => 'true'])  , [
            'Authorization' => 'Bearer ' . $this->getToken
        ]);

        $responseData = $response->getOriginalContent();

        if($responseData['animes'] == null){
            $response->assertOk();
        }

        $ratings = [];
        foreach($responseData['animes'] as $anime){
            $ratings[] = $anime['rating']['rating'];
        }

        $newRating = $ratings;

        arsort($ratings);
        
        $this->assertEquals(implode(' ' , $newRating) , implode(' ' , $ratings));
        

    }

    

    
}
