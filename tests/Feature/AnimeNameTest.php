<?php

namespace Tests\Feature;

use App\Jobs\ClipingShortAnime;
use App\Models\AnimeVideo;
use App\Models\User;
use Database\Factories\AnimeVideoFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class AnimeNameTest extends TestCase
{
    use RefreshDatabase;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['admin' => true]);
    }


    /**
     *@group anime-test
     */
    public function test_create_anime_with_zip(): void
    {
        Queue::fake();
        $zip = new ZipArchive;

        if (true === ($zip->open(public_path('asset-img/test.zip'), ZipArchive::CREATE | ZipArchive::OVERWRITE))) {
            $zip->addFile(public_path('asset-img/anime1.mp4'), 'anime1.mp4');
            $zip->close();
        }

        // Menambahkan file ZIP palsu ke dalam array
        $data = $this->set_anime_data(true);
            
            Queue::assertNothingPushed();
            $response = $this->actingAs($this->admin)->post(route('anime-name.store.zip') , $data);
            $response->assertSessionHas('info' , 'Success Extracting');

            Queue::assertPushed(ClipingShortAnime::class); //memastikan pembuatan clipping video dimasukan ke queue
            $this->assertDatabaseCount('anime_videos' , 1);
            
            $anime_video_url = Storage::disk('public')->url('F-' .$data['anime_name']. '/anime1.mp4');
            $this->assertDatabaseHas('anime_videos' , ['anime_eps' => 'anime1.mp4' , 'video_url' => $anime_video_url]);
            $this->assertDatabaseHas('anime_names' , ['anime_name' => $data['anime_name']]);
            Storage::disk('public')->assertExists('F-' .$data['anime_name']. '/anime1.mp4'); //memastikan directory ada

            //delete manual test anime file
            Storage::deleteDirectory('F-' . $data['anime_name']);
        }



    /**
     *@group anime-test
     */
    public function test_create_anime_with_zip_fail_because_zip_contain_non_video_type(): void
    {
        Queue::fake();
        $zip = new ZipArchive;

        if (true === ($zip->open(public_path('asset-img/test.zip'), ZipArchive::CREATE | ZipArchive::OVERWRITE))) {
            $zip->addFile(public_path('asset-img/anime1.mp4'), 'anime1.mp4'); //benar
            $zip->addFile(public_path('asset-img/logo.png'), 'logo.png'); //salah
            $zip->close();
        }

        // Menambahkan file ZIP palsu ke dalam array
        $data = $this->set_anime_data(true);
            
            Queue::assertNothingPushed();
            $response = $this->actingAs($this->admin)->post(route('anime-name.store.zip') , $data);
            $response->assertSessionHas('info' , 'Fail Extracting Zip Invalid');

            Queue::assertPushed(ClipingShortAnime::class , 1); //memastikan pembuatan clipping video dimasukan ke queue hanya 1x
            $this->assertDatabaseEmpty('anime_videos');
            
            $anime_video_url = Storage::disk('public')->url('F-' .$data['anime_name']. '/anime1.mp4');
            $this->assertDatabaseMissing('anime_videos' , ['anime_eps' => 'anime1.mp4' , 'video_url' => $anime_video_url]);
            $this->assertDatabaseMissing('anime_names' , ['anime_name' => $data['anime_name']]);
            Storage::disk('public')->assertMissing('F-' .$data['anime_name']. '/anime1.mp4'); //memastikan directory ada

            //delete manual test anime file
            Storage::deleteDirectory('F-' . $data['anime_name']);
        }


    /**
     *@group anime-test
     */
    public function test_create_anime_and_add_video_manualy_and_do_update_anime_name() : void
    {
        Queue::fake();

        $data = $this->set_anime_data();

        //buat anime
        $response = $this->actingAs($this->admin)->post(route('anime-name.store') , $data);
        $response->assertStatus(302);
        $response->assertRedirect(route('anime-videos.create' , ['anime-name' => $data['anime_name'] , 'anime-slug' => 'senyamiku-kawai']));
        
        Queue::assertNothingPushed();

        //add video anime
        $response = $this->actingAs($this->admin)->post(route('anime-videos.store') , [
            'anime_name_slug' => 'senyamiku-kawai',
            'video' => new UploadedFile(public_path('asset-img/anime1.mp4') , 'anime1.mp4' , null , null , true)
        ]);
        $response->assertSessionHas('success' , 'Video Added');

        Queue::assertPushed(ClipingShortAnime::class); //memastikan pembuatan clipping video dimasukan ke queue
        $this->assertDatabaseCount('anime_videos' , 1);
        
        $anime_video_url = Storage::disk('public')->url('F-' .$data['anime_name']. '/anime1.mp4');
        $this->assertDatabaseHas('anime_videos' , ['anime_eps' => 'anime1.mp4' , 'video_url' => $anime_video_url]);
        $this->assertDatabaseHas('anime_names' , ['anime_name' => $data['anime_name']]);
        Storage::disk('public')->assertExists('F-' .$data['anime_name']. '/anime1.mp4'); //memastikan directory ada

        //update anime-name
        $data['anime_name'] = 'Miku Daisuki';
        $response = $this->actingAs($this->admin)->put(route('anime-name.update' , 'senyamiku-kawai') , $data);
        $response->assertSessionHas('info-success' , 'Success Update');
        $this->assertDatabaseHas('anime_names' , ['anime_name' => $data['anime_name']]);
        $this->assertDatabaseMissing('anime_names' , ['anime_name' => 'Senyamiku kawai']);
        $this->assertDatabaseMissing('anime_videos' , ['anime_eps' => 'anime1.mp4' , 'video_url' => $anime_video_url]);

        $new_anime_video_url = Storage::disk('public')->url('F-' .$data['anime_name']. '/anime1.mp4');
        $this->assertDatabaseHas('anime_videos' , ['anime_eps' => 'anime1.mp4' , 'video_url' => $new_anime_video_url]);
        Storage::disk('public')->assertExists('F-' .$data['anime_name']. '/anime1.mp4'); //memanggil folder anime terbaru
        Storage::disk('public')->assertMissing('F-Senyamiku kawai/anime1.mp4'); //memastikan directory ada

        //delete manual test anime file
        Storage::deleteDirectory('F-' . $data['anime_name']);
    }

     /**
     *@group anime-test
     */
    public function test_anime_video_name_change_the_video_url_should_change_to() : void
    {
        Queue::fake();

        $data = $this->set_anime_data();

        //buat anime
        $response = $this->actingAs($this->admin)->post(route('anime-name.store') , $data);
      
        //add video anime
        $response = $this->actingAs($this->admin)->post(route('anime-videos.store') , [
            'anime_name_slug' => 'senyamiku-kawai',
            'video' => new UploadedFile(public_path('asset-img/anime1.mp4') , 'anime1.mp4' , null , null , true)
        ]);
        $response->assertSessionHas('success' , 'Video Added');

       $this->assertDatabaseCount('anime_videos' , 1);
       $this->assertDatabaseHas('anime_videos' , [
        'anime_eps' => 'anime1.mp4',
        'video_url' => Storage::disk('public')->url('F-' . $data['anime_name'] . '/anime1.mp4')
       ]);

       Storage::disk('public')->assertExists('F-'.$data['anime_name'].'/anime1.mp4');

       $response = $this->actingAs($this->admin)->put(route('anime-videos.update' , AnimeVideo::first()->id) , [
        'anime_eps' => 'Nezuko Chan'
       ]);
       $response->assertStatus(302);
       $response->assertSessionHas('info' , 'Success Updating');

       $this->assertDatabaseCount('anime_videos' , 1);
       $this->assertDatabaseMissing('anime_videos' , [
        'anime_eps' => 'anime1.mp4',
        'video_url' => Storage::disk('public')->url('F-' . $data['anime_name'] . '/anime1.mp4')
       ]);
       
       //new anime video url
       $this->assertDatabaseHas('anime_videos' , [
        'anime_eps' => 'Nezuko Chan.mp4',
        'video_url' => Storage::disk('public')->url('F-' . $data['anime_name'] . '/Nezuko Chan.mp4')
       ]);

       Storage::disk('public')->assertMissing('F-'.$data['anime_name'].'/anime1.mp4');
       Storage::disk('public')->assertExists('F-'.$data['anime_name'].'/Nezuko Chan.mp4');

       Storage::deleteDirectory('F-' . $data['anime_name']);

    }


    private function set_anime_data(bool $zip = false) : array
    {
        $data = [
            'anime_name' => 'Senyamiku kawai',
            'total_episode' => 24,
            'studio' => 'mappa',
            'author' => 'gua',
            'description' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Voluptatum similique quidem earum molestias, placeat, adipisci perspiciatis itaque possimus aliquam nesciunt suscipit consequatur deleniti repudiandae delectus! Enim nam cum ratione nemo.', 
            'genre' => 'Horor, Action, Mysteri',
            $zip == false ? : 
            'zip' => new UploadedFile(public_path('asset-img/test.zip') , 'test.zip' , null , null , true)
        ];

        return $data;
    }
    
}
