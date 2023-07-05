<?php

namespace Tests\Feature;

use App\Models\AnimeName;
use App\Models\AnimeRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;

class AnimeRatingTest extends TestCase
{   
    use RefreshDatabase;
    private $getToken;
    private $getTokenVip;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();

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
            'point' => 10,
            'participan' => 2,
            'rating' => 5
        ]);

        $this->getToken = $user->createToken('onime-test-token' , ['normal-token'])->plainTextToken;
        $this->getTokenVip = $user->createToken('onime-test-token' , ['vip-token'])->plainTextToken;
        
    }

    /**
     * @group anime-add-rating
     */
    public function test_add_rating_to_vip_anime_for_non_vip_token(): void
    {
        //upadate dulu anime vip nya menjad true
        $anime = AnimeName::select('slug' , 'id')->first();
        $anime->vip = 1;
        $anime->save();
     
        $response = $this->put('http://onime.test/api/ver1/animes/' . $anime->slug . '/rating' , [
            'point' => 10 //mengirim 10 point rating
        ] , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(404);

       
    }

    /**
     * @group anime-add-rating
     */
    public function test_add_rating_to_non_vip_anime_for_vip_token(): void
    {
        $anime = AnimeName::select('slug')->first();
    
        $response = $this->put('http://onime.test/api/ver1/animes/' . $anime->slug . '/rating' , [
            'point' => 10 //mengirim 10 point rating
        ] , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(200);

       
    }

    /**
     * @group anime-add-rating
     */
    public function test_add_rating_invalid_point_interval(): void
    {
        $anime = AnimeName::select('slug')->first();
    
        $response0 = $this->put('http://onime.test/api/ver1/animes/' . $anime->slug . '/rating' , [
            'point' => 0 //mengirim 0 point rating akan gagal karena minimal point 1-10
        ] , [
            'Authorization' => 'Bearer ' . $this->getTokenVip
        ])->assertStatus(400);


        $response10 = $this->put('http://onime.test/api/ver1/animes/' . $anime->slug . '/rating' , [
            'point' => 11 //mengirim 11 point rating akan gagal karena minimal point 1-10
        ] , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(400);

       
    }


    /**
     * @group anime-add-rating
     */
    public function test_add_rating_with_valid_point(): void
    {
        $anime = AnimeName::with(['rating:anime_name_id,rating'])->select('id', 'slug')->first();
    
        $response = $this->put('http://onime.test/api/ver1/animes/' . $anime->slug . '/rating' , [
            'point' => 10 //mengirim 10 point rating
        ] , [
            'Authorization' => 'Bearer ' . $this->getToken
        ])->assertStatus(200);

        $anime->refresh();
        
        $currentRatimgShould = 20 / 3; // point sebelumnya ada 10 kemudian ditambah 10 menjadi 20, pariticapn sebelumnya adalah 2 ditambah 1 jadi 3
        $currentRatimgShould = sprintf('%.1f' , $currentRatimgShould);

        $this->assertEquals($anime->rating->rating , $currentRatimgShould);
       

       
    }
}
