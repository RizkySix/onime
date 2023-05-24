<?php

namespace Tests\Unit;

use App\Http\Controllers\AnimeNameController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\TestCase;


class AllTest extends TestCase
{
    /**
     * A basic unit test example.
     */
   
     use RefreshDatabase;

     public function test_remove_white_space()
     {
       $testSpace = new AnimeNameController;
       $this->assertEquals('oke ini sudah keren dan aku sangat tampan' , $testSpace->remove_white_space('oke        ini sudah  keren dan       aku  sangat   tampan'));
       $this->assertEquals('one more test passed' , $testSpace->remove_white_space('one more test passed'));
     }

    
}
