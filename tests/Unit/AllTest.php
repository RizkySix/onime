<?php

namespace Tests\Unit;

use App\Http\Controllers\AnimeNameController;
use App\Http\Controllers\AnimeVideoController;
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
      $this->assertEquals('a b c' , $testSpace->remove_white_space('a   b      c'));
      $this->assertEquals('a b c' , $testSpace->remove_white_space('a   b      c ////////////////    '));
       $this->assertEquals('teksoho kamu sayang akujadi makanya' , $testSpace->remove_white_space('teks/oho    kamu sayang aku/jadi   // makanya  '));
       $this->assertEquals('oke ini sudah keren dan aku sangat tampan' , $testSpace->remove_white_space('oke        ini sudah  keren dan       aku  sangat   tampan'));
       $this->assertEquals('one more test passed' , $testSpace->remove_white_space('one more test passed'));
     }

     
     public function test_remove_dot()
     {
       $testDot = new AnimeVideoController;
    $this->assertEquals(' One Puch. Ma.n.Eps.-.1.mp4' , $testDot->remove_dot('    ///  . One Puc/h. Ma.n    ......... ////.Eps//./-.1/' , 'mp4'));
      $this->assertEquals('a b c.mp4' , $testDot->remove_dot('a   b      c ////////////////    ' , 'mp4'));
       $this->assertEquals('teksoho kamu sayang akujadi makanya.mkv' , $testDot->remove_dot('teks/oho    kamu sayang aku/jadi   // makanya.mkv  ' , 'mkv'));
       $this->assertEquals('oke ini sudah keren dan aku sangat tampan.mkv' , $testDot->remove_dot('oke        ini sudah  keren dan       aku  sangat   tampan' , 'mkv'));
       $this->assertEquals('one more test passed.mp4' , $testDot->remove_dot('one more test passed' , 'mp4'));
       $this->assertEquals('this.the-video of the.yearmkv.mkv' , $testDot->remove_dot('this.the-video of   ////// the.. yea/rmkv    ..    ' , 'mkv'));
      $this->assertEquals('dirgahayu dalam duka.dan. kekecewaa.n.mp4' , $testDot->remove_dot('  dirgahayu dalam duka.//. da/n ..... /////   kekecewaa/.n  // ..   ' , 'mp4'));
       $this->assertEquals('honohonomiya. scorpion.and the.gingsul of.the.sea long.s again.mp4' , $testDot->remove_dot(' hono hono/miya ///// ... scor/pion.and the..gingsul of../the.sea long/.s   again' , 'mp4'));
     }

    
}
