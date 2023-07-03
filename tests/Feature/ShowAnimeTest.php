<?php

namespace Tests\Feature;

use App\Models\AnimeName;
use App\Models\AnimeRating;
use App\Models\Genre;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Str;

class ShowAnimeTest extends TestCase
{
    use RefreshDatabase; // JANGAN COBA2 MENGGUNAKAN INI DI LIVE/MAIN DATABASE

    private $getToken;
    private $getTokenVip;
 
    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();

        //buat genre a-z
        $alphabet = range('a', 'z');
        $alphabet = array_combine(range(1, 26), $alphabet);

        $insertGenre = [];
        for($i = 1; $i <= 26; $i++){
            $insertGenre[] = [
                'genre_name' => $alphabet[$i],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        DB::table('genres')->insert($insertGenre);
       
        //buat anime
       for($i = 1; $i <= 5; $i++){
        $animeCreate = AnimeName::withoutEvents(function() {
            $animeNew = AnimeName::create([
                    'anime_name' => rand().'rizky',
                    'slug' => Str::slug(Str::random(10)),
                    'total_episode' => 24,
                    'studio' => 'mappa',
                    'author' => 'gua',
                    'vip' => rand(0 , 1),
                    'description' => 'kosong'
                ]);
    
                return $animeNew;
            });
           
           
            //buat relasi genre
            $getGenre = Genre::select('id')->get();
            $getGenre->values()->toArray();
            $id_genre = [];
            for($j = 0; $j <= 5; $j++){
                $randID = rand($getGenre[0]['id'] , $getGenre[25]['id']);
                if(!in_array($randID , $id_genre)){
                    $id_genre[] = $randID;
                }
            }

            $animeCreate->genres()->attach($id_genre);

            //buat rating
            AnimeRating::create([
                'anime_name_id' => $animeCreate->id,
                'point' => rand(),
                'participan' => rand(),
                'rating' => rand(1 , 10)
            ]);
       }

       //buat token
        $this->getToken = $user->createToken('onime-test-token' , ['normal-token'])->plainTextToken;
        $this->getTokenVip = $user->createToken('onime-test-token' , ['vip-token'])->plainTextToken;
    }


    /**
     * @group api-show-anime
     */
    public function test_show_anime_non_vip(): void
    {
        $vipSlug = AnimeName::where('vip' , true)->select('slug')->first();
        $nonVipSlug = AnimeName::where('vip' , false)->select('slug')->first();
        $responFail = $this->get('http://onime.test/api/ver1/animes/' . $vipSlug->slug , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(404);

        //get data yang bukan vip anime
        $responSuccess = $this->getJson('http://onime.test/api/ver1/animes/' . $nonVipSlug->slug , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(200);
        
        //testing logic related anime berdaskaran genre yang sama
        $data = $responSuccess->json();
        $allRelated = $data['related_animes'];
        
        $animeGenre = $data['anime']['genre'];
        $arrAnimeGenre = explode(', ' , $animeGenre);

        if($allRelated == null){
            $responSuccess->assertOk();
        }else{
         
            foreach($allRelated as $related){
               $related['vip'] == false ?: $responSuccess->assertInvalid();
                $relatedGenre = explode(', ' , $related['genre']);

                $intersection = array_intersect($relatedGenre , $arrAnimeGenre);
                if(empty($intersection)){
                    $responSuccess->assertInvalid();
                }
             }

             $responSuccess->assertOk();
            
        }
      

       
    }

    /**
     * @group api-show-anime
     */
    public function test_show_anime_vip(): void
    {
        $vipSlug = AnimeName::where('vip' , true)->select('slug')->first();
        $nonVipSlug = AnimeName::where('vip' , false)->select('slug')->first();

        $responTestOtorisasi = $this->get('http://onime.test/api/ver1/animes/' . $nonVipSlug->slug , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

        //get data vip anime
        $responSuccess = $this->getJson('http://onime.test/api/ver1/animes/' . $vipSlug->slug , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);
        
        //testing logic related anime berdaskaran genre yang sama
        $data = $responSuccess->json();
        $allRelated = $data['related_animes'];
        
        $animeGenre = $data['anime']['genre'];
        $arrAnimeGenre = explode(', ' , $animeGenre);

        if($allRelated == null){
            $responSuccess->assertOk();
        }else{
         
            foreach($allRelated as $related){
               
                $relatedGenre = explode(', ' , $related['genre']);

                $intersection = array_intersect($relatedGenre , $arrAnimeGenre);
                if(empty($intersection)){
                    $responSuccess->assertInvalid();
                }
             }

             $responSuccess->assertOk();
            
        }
      

       
    }

     /**
     * @group api-show-anime
     */
    public function test_genre_and_count_related_anime() :void
    {
        $response = $this->get('http://onime.test/api/ver1/genres' , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(200)->assertJsonIsObject();

        $data = $response->json();
        if($data['genres'] == null){
            $response->assertOk();
        }else{
            $response->assertJsonStructure([
                'status',
                'message',
                'genres' => [
                    '*' => [
                        'genre_name',
                        'anime_result'
                    ]
                ]
            ]);
        }
    }

    /**
     * @group api-show-anime
     */
    public function test_show_genre_for_non_vip_token() : void
    {
        $getGenreName = Genre::select('genre_name')->first();
        $response = $this->get('http://onime.test/api/ver1/genres/' . $getGenreName->genre_name  , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(200);

        $data = $response->json();
        $currentGenre[] = $getGenreName->genre_name;

        if($data['genre']['animes'] == null){
            $response->assertOk();
        }else{
            foreach($data['genre']['animes'] as $anime){
                $anime['vip'] == false ?: $response->assertInvalid();
                
                $genres = explode(', ' , $anime['genres']);
                $intersection = array_intersect($currentGenre , $genres);
               
                !empty($intersection) ?: $response->assertInvalid();
              
                
            }
        }

    }

     /**
     * @group api-show-anime
     */

     public function test_show_genre_for_vip_token() : void
    {
        $getGenreName = Genre::select('genre_name')->first();
        $response = $this->get('http://onime.test/api/ver1/genres/' . $getGenreName->genre_name  , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

        $data = $response->json();
        $currentGenre[] = $getGenreName->genre_name;

        if($data['genre']['animes'] == null){
            $response->assertOk();
        }else{
            foreach($data['genre']['animes'] as $anime){
          
                $genres = explode(', ' , $anime['genres']);
                $intersection = array_intersect($currentGenre , $genres);
               
                !empty($intersection) ?: $response->assertInvalid();
              
                
            }
        }

    }

     /**
     * @group api-show-anime
     */
    public function test_order_by_rating_true() : void
    {
        $getGenreName = Genre::select('genre_name')->first();
        $response = $this->get('http://onime.test/api/ver1/genres/' . $getGenreName->genre_name . '?rating=true'  , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200); 

        $data = $response->json();
        $currentGenre[] = $getGenreName->genre_name;
        $dataAnime = $data['genre']['animes'];
        $rating = [];

        if($dataAnime == null){
            $response->assertOk();
        }else{
            foreach($dataAnime as $anime){
          
                //cek kesamaan genre
                $genres = explode(', ' , $anime['genres']);
                $intersection = array_intersect($currentGenre , $genres);
               
                !empty($intersection) ?: $response->assertInvalid();

               //tambahkan rating
                $rating[] = $anime['rating'];
                
            }

            //cek rating orderby desc
            $new_rating = $rating;
            arsort($rating);

            implode(' ' , $new_rating) == implode(' ' , $rating) ? $response->assertOk() : $response->assertInvalid();
        }
        
        
    }
}
