<?php

namespace Tests\Feature;

use App\Models\AnimeName;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnimeListByAlphabetTest extends TestCase
{
    use RefreshDatabase;
    private $getToken;
    private $getTokenVip;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();

        //create for non vip
        AnimeName::factory(10)->create([
            'created_at' => Carbon::now()->addDays(5),
            'updated_at' => Carbon::now()->addDays(5)
        ]);

        //create for vip
        AnimeName::factory(15)->create([
            'created_at' => Carbon::now()->addDays(5),
            'updated_at' => Carbon::now()->addDays(5),
            'vip' => true
        ]);

        //create invalid permintaan endpoint
        AnimeName::factory(10)->create([
            'created_at' => Carbon::now()->subMonth(2),
            'updated_at' => Carbon::now()->subMonth(2)
        ]);
       //create for vip
         AnimeName::factory(15)->create([
            'created_at' => Carbon::now()->subMonth(2),
            'updated_at' => Carbon::now()->subMonth(2),
            'vip' => true
        ]);

         //buat token
         $this->getToken = $user->createToken('onime-test-token' , ['normal-token'])->plainTextToken;
         $this->getTokenVip = $user->createToken('onime-test-token' , ['vip-token'])->plainTextToken;
    }

    /**
     * @group anime-list-test
     */
    public function test_anime_list_no_query_parameter_non_vip_token(): void
    {
        $response = $this->get('http://onime.test/api/ver1/anime-list' , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(200);

        $data = $response->json();
        $oneMonthAnime = $data['animes'];
        count($oneMonthAnime) == 10 ? : $response->assertInvalid();
      
        $animeName = [];
        foreach($oneMonthAnime as $anime){
            $animeName[] = $anime['anime_name'];
        }

        $findValidDate = AnimeName::whereIn('anime_name' , $animeName)->select('created_at' , 'vip')->get();
        
        //membuat skenario tanggal 30 hari kebelakang
        $validDate = Carbon::now()->subDays(30);
        $animeData = $findValidDate->values()->toArray();
      
        foreach($animeData as $dataAnime){
           $dataAnime['created_at'] >= $validDate && $dataAnime['vip'] == false ? : $response->assertInvalid();
        }

        
    }

     /**
     * @group anime-list-test
     */
    public function test_anime_list_no_query_parameter_vip_token(): void
    {
        $response = $this->get('http://onime.test/api/ver1/anime-list' , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

        $data = $response->json();
        $oneMonthAnime = $data['animes'];
      
        $this->assertEquals(count($oneMonthAnime) , 25);

        $animeName = [];
        foreach($oneMonthAnime as $anime){
            $animeName[] = $anime['anime_name'];
        }

        $findValidDate = AnimeName::whereIn('anime_name' , $animeName)->select('created_at' , 'vip')->get();
        
        //membuat skenario tanggal 30 hari kebelakang
        $validDate = Carbon::now()->subDays(30);
        $animeData = $findValidDate->values()->toArray();
      
        foreach($animeData as $dataAnime){
           $dataAnime['created_at'] >= $validDate ? : $response->assertInvalid();
        }

        
    }

      /**
     * @group anime-list-test
     */
    public function test_invalid_query_parameter_vip_token_as_example() : void
    {
        //ini invalid karena "list" => "ob" mengandung 2 huruf
        $response = $this->get('http://onime.test/api/ver1/anime-list?list=ob' , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

        $data = $response->json();
      
        $this->assertEquals(count($data['animes']) , 25);
    }

     /**
     * @group anime-list-test
     */
    public function test_valid_query_parameter_non_vip_token() :void
    {
         //ini valid karena "list" => "a" mengandung 1 huruf
         $response = $this->get('http://onime.test/api/ver1/anime-list?list=a' , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(200);

        $data = $response->json();
        $oneMonthAnime = $data['animes'];
      
       if($oneMonthAnime == null){
         $response->assertOk();
       }else{
            $animeName = [];
            foreach($oneMonthAnime as $anime){
                $animeName[] = $anime['anime_name'];

                //pastikan semua anime name diawali dengan huruf 'a'
                strtolower(substr($anime['anime_name'] , 0 , 1)) == 'a' ? : $response->assertInvalid();
            }

            $findValidDate = AnimeName::whereIn('anime_name' , $animeName)->select('vip')->get();
            $animeData = $findValidDate->values()->toArray();
        
            foreach($animeData as $animeDetail){
                $animeDetail['vip'] == false ? : $response->assertInvalid();
            }
       }
        
    }


     /**
     * @group anime-list-test
     */
    public function test_valid_query_parameter_vip_token() :void
    {
         //ini valid karena "list" => "a" mengandung 1 huruf
         $response = $this->get('http://onime.test/api/ver1/anime-list?list=a' , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

        $data = $response->json();
        $oneMonthAnime = $data['animes'];
      
       if($oneMonthAnime == null){
            $response->assertOk();
       }else{
            foreach($oneMonthAnime as $anime){
                //pastikan semua anime name diawali dengan huruf 'a'
            strtolower(substr($anime['anime_name'] , 0 , 1)) == 'a' ? : $response->assertInvalid();
        }
       }

       
        
    }
    
}
