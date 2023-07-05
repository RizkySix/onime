<?php

namespace Tests\Feature;

use App\Models\Pricing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminPricingTest extends TestCase
{
    use RefreshDatabase;
    private $nonAdmin;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonAdmin = $this->createUserRole();
        $this->admin = $this->createUserRole(true);

    }

    /**
     * @group admin-pricing-test
     */
    public function test_non_admin_cant_access_this_route(): void
    {
        $pricing = [
            'pricing_name' => 'Mega vip',
            'vip_power' => 'NORMAL',
            'price' => rand(100,999),
            'discount' => 15,
            'duration' => 90,
            'description' => fake()->text()
        ];

        //non admin tidak bisa akses route berikut
          $this->actingAs($this->nonAdmin)->get(route('pricing.create'))->assertStatus(404);
          $this->actingAs($this->nonAdmin)->post(route('pricing.store') , $pricing)->assertStatus(404);
          $this->actingAs($this->nonAdmin)->get(route('pricing.admin'))->assertStatus(404);
          $this->actingAs($this->nonAdmin)->get(route('pricing.edit' , $pricing['pricing_name']))->assertStatus(404);
          $this->actingAs($this->nonAdmin)->put(route('pricing.update' , $pricing['pricing_name']) , $pricing)->assertStatus(404);
          $this->actingAs($this->nonAdmin)->delete(route('pricing.destroy' , $pricing['pricing_name']))->assertStatus(404);
          $this->actingAs($this->nonAdmin)->post(route('pricing.restore' , $pricing['pricing_name']))->assertStatus(404);
          $this->actingAs($this->nonAdmin)->post(route('pricing.force-delete' , $pricing['pricing_name']))->assertStatus(404);

          //non admin bisa akses route berikut
         $this->actingAs($this->nonAdmin)->get(route('pricing.index'))->assertStatus(200);


    }

     /**
     * @group admin-pricing-test
     */
    public function test_validation_errors_when_create_pricing() : void
    {
        $pricing = Pricing::factory()->create([
            'pricing_name' => 'Mega vip'
        ]);
        $response = $this->actingAs($this->admin)->post(route('pricing.store') , [
            'pricing_name' => 'Mega vip', // akan error karena nama sudah dibuat
            'vip_power' => 'NORMAL',
            'price' => rand(100,999),
            'discount' => 15,
            'duration' => 90,
            'description' => fake()->text()
        ]);

        $response->assertStatus(302);
        $response->assertInvalid(['pricing_name']);

        //test karena tidak ada field yang boleh nullable 
        $response2 = $this->actingAs($this->admin)->post(route('pricing.store') , [
            'pricing_name' => 'CANT NULL BAKAYARO', // akan error karena nama sudah dibuat
            'vip_power' => '',
            'price' => '',
            'discount' => 'this must be integer',
            'duration' => 90,
            'description' => fake()->text()
        ]);

        $response2->assertStatus(302);
        $response2->assertInvalid(['vip_power' , 'price' , 'discount']);

        
    }

       /**
     * @group admin-pricing-test
     */
    public function test_admin_can_create_pricing() : void
    {
        $firstPricing = Pricing::factory()->create();

        $pricing = [
            'pricing_name' => 'Mega vip',
            'vip_power' => 'NORMAL',
            'price' => rand(100,999),
            'discount' => 15,
            'duration' => 90,
            'description' => fake()->text()
        ];

        $response = $this->actingAs($this->admin)->post(route('pricing.store') , $pricing);
        
        $response->assertStatus(302);
        
        //cek pricing terbaru harus ada pada database
        $this->assertDatabaseCount('pricings' , 2);
        $this->assertDatabaseHas('pricings' , $pricing);

        //cek pricing terbaru harus muncul pada list pricing index view customer
        $responseNewPricingAppearForCustomer = $this->viewUser($this->nonAdmin)->assertStatus(200);
        $responseNewPricingAppearForCustomer->assertSee('Mega vip'); //case 

         //cek pricing terbaru harus muncul pada table list pricing view admin
         $responseNewPricingAppearForAdmin = $this->viewUser($this->admin)->assertStatus(200);
         $responseNewPricingAppearForAdmin->assertSee('Mega vip'); //case 


    }

     /**
     * @group admin-pricing-test
     */
    public function test_admin_can_access_form_update_pricing() : void
    {
        $pricing = Pricing::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('pricing.edit' , $pricing->pricing_name))->assertStatus(200);

        $response->assertSee($pricing->pricing_name);

        
    }

     /**
     * @group admin-pricing-test
     */
    public function test_admin_can_update_pricing() : void
    {
        $firstPricing = Pricing::factory()->create([
            'pricing_name' => 'Pricing 1'
        ]);

        $secondPricing = Pricing::factory()->create([
            'pricing_name' => 'Mega vip'
        ]);

          //akan gagal karena pricing_name yang diberikan sudah digunakan oleh firstPricing
        $responseFail = $this->actingAs($this->admin)->put(route('pricing.update' , $secondPricing->pricing_name) , [
            'pricing_name' => 'Pricing 1',
            'price' => 100000,
            'discount' => $secondPricing->discount,
            'duration' => $secondPricing->duration,
            'description' => $secondPricing->description
        ]);

        $responseFail->assertStatus(302);
        $responseFail->assertInvalid(['pricing_name']);

        sleep(1); //sleep 1 detik agar updated_at nya tidak barengan dengan firstPricing

         //akan berhasil karena pricing_name yang diberikan sama dengan Pricing yang akan diupdate
         $responseSuccess = $this->actingAs($this->admin)->put(route('pricing.update' , $secondPricing->pricing_name) , [
            'pricing_name' => 'Mega vip',
            'price' => 100000,
            'discount' => $secondPricing->discount,
            'duration' => $secondPricing->duration,
            'description' => $secondPricing->description
        ]);

        $responseSuccess->assertStatus(302);
        
        //passtikan data pada database sudah berubah
        $findUpdatedPricing = Pricing::select('pricing_name' , 'price')->orderBy('updated_at' , 'DESC')->first();
    
        $this->assertEquals('Mega vip' , $findUpdatedPricing->pricing_name);
        $this->assertEquals(100000 , $findUpdatedPricing->price );

         //cek pricing harga terbaru harus muncul pada list pricing index view customer
         $responseNewPricingAppearForCustomer = $this->viewUser($this->nonAdmin)->assertStatus(200);
         $responseNewPricingAppearForCustomer->assertSee('Rp. 100,000'); //case 
 
          //cek pricing harga terbaru harus muncul pada table list pricing view admin
          $responseNewPricingAppearForAdmin = $this->viewUser($this->admin)->assertStatus(200);
          $responseNewPricingAppearForAdmin->assertSee('Rp. 100,000'); //case 

    }

     /**
     * @group admin-pricing-test
     */
    public function test_admin_delete_pricing_shouldbe_soft_deleted() : void
    {
        $pricing = Pricing::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('pricing.destroy' , $pricing->pricing_name));
        
        //seharusnya pricing tidak muncul lagi pada customer atau admin pricing table
        $responseSoftDeletedPricingNotAppearForCustomer = $this->viewUser($this->nonAdmin)->assertStatus(200);
        $responseSoftDeletedPricingNotAppearForCustomer->assertDontSee($pricing->pricing_name); //case 

        $responseSoftDeletedPricingNotAppearForAdmin = $this->viewUser($this->admin)->assertStatus(200);
        $responseSoftDeletedPricingNotAppearForAdmin->assertDontSee($pricing->pricing_name); //case 


        //jumlah row dalam table harus tetap 1, karena soft delete
        $this->assertDatabaseCount('pricings' , 1);
        
        //cek harusnya pricing yang ter soft deleted ada pada Trahsed 
        $trashedPricing = Pricing::onlyTrashed()->first();
        $this->assertEquals($pricing->pricing_name , $trashedPricing->pricing_name);

        //cek harusnya pricing yang ter soft deleted tidak ada pada publish pricing dan menjadi null
        $publishedPricing = Pricing::first();
        $this->assertEquals(null , $publishedPricing);
       
        
    }

    /**
     * @group admin-pricing-test
     */
    public function test_admin_can_restore_soft_deleted_pricing() : void
    {
        $pricing = Pricing::factory()->create();
        
        $softDeletingPricing = $this->actingAs($this->admin)->delete(route('pricing.destroy' , $pricing->pricing_name));
        $trashedPricing = Pricing::onlyTrashed()->first();
       

        //pastikan route restore hanya dapat menerima pricing_name aksi berikut gagal karena tidak ada pricing dengan nama $pricing->id
        $responseFail = $this->actingAs($this->admin)->post(route('pricing.restore' , $pricing->id));
        $this->assertEquals($trashedPricing->pricing_name , $pricing->pricing_name); //pricing masih ada pada Trahsed

        //aksi berikut akan berhasil karena mengirim pricing_name
        $responseSuccess = $this->actingAs($this->admin)->post(route('pricing.restore' , $pricing->pricing_name));

        $trashedPricing->refresh();
        $publishedPricing = Pricing::first();
        
        $this->assertEquals(null , $trashedPricing->deleted_at); //trahsedPricing deleted_at nya menjadi null
        $this->assertNotEquals(null , $publishedPricing); //pricing sudah muncul kembali pada published


         //cek pricing restored harus muncul pada list pricing index view customer
       $restoredPricingAppearCustomerView = $this->viewUser($this->nonAdmin)->assertStatus(200);
       $restoredPricingAppearCustomerView->assertSee($pricing->pricing_name); //case 
 
          //cek pricing restored harus muncul pada table list pricing view admin
       $restoredPricingAppearAdminView = $this->viewUser($this->admin)->assertStatus(200);
       $restoredPricingAppearAdminView->assertSee($pricing->pricing_name); //case 

    }

       /**
     * @group admin-pricing-test
     */
    public function test_admin_can_forcedelete_pricing() : void
    {
        $pricing = Pricing::factory()->create();
        
        $softDeletingPricing = $this->actingAs($this->admin)->delete(route('pricing.destroy' , $pricing->pricing_name));

        //akan gagal karena force delete harus menerima pricing_name
        $forceDeleteFail = $this->actingAs($this->admin)->post(route('pricing.force-delete' , $pricing->id));
        $this->assertDatabaseCount('pricings' , 1); //jumlah data masih satu

         //berhasil karena mengirim pricing_name
         $forceDeleteSuccess = $this->actingAs($this->admin)->post(route('pricing.force-delete' , $pricing->pricing_name));
         $this->assertDatabaseCount('pricings' , 0); //jumlah data kosong

            //cek pricing force deleted tidak muncul pada list pricing index view customer
       $forceDeletedPricingNotAppearCustomerView = $this->viewUser($this->nonAdmin)->assertStatus(200);
       $forceDeletedPricingNotAppearCustomerView->assertDontSee($pricing->pricing_name); //case 
 
          //cek pricing force deleted tidak muncul pada table list pricing view admin
       $forceDeletedPricingNotAppearAdminView = $this->viewUser($this->admin)->assertStatus(200);
       $forceDeletedPricingNotAppearAdminView->assertDontSee($pricing->pricing_name); //case 
    }


     /**
     * @group admin-pricing-test
     */
    public function test_admin_cant_create_duplicate_pricing_name_even_it_sofdeleted() : void
    {
        $pricing = Pricing::factory()->create([
            'pricing_name' => 'Mega vip'
        ]);

        $response = $this->actingAs($this->admin)->delete(route('pricing.destroy' , $pricing->pricing_name));
        $trashedPricing = Pricing::onlyTrashed()->first();
        $this->assertEquals($pricing->pricing_name ,  $trashedPricing->pricing_name);

        //tidak akan bisa membuat pricing dengan nama sama karena pricing name tersebut masih status softdelete
        $failCreatePricing = $this->actingAs($this->admin)->post(route('pricing.store') , $pricing->toArray())->assertStatus(302);
        
        $failCreatePricing->assertInvalid(['pricing_name']);
       
    }


    private function createUserRole(bool $roleAdmin = false) : User
    {
        $user = User::factory()->create(['admin' => $roleAdmin]);

        return $user;
    }


    private function viewUser(User $user)
    {
        $user->admin == true ? $route = route('pricing.admin') : $route = route('pricing.index');
        $viewUser = $this->actingAs($user)->get($route);

        return $viewUser;
       
    }
}
