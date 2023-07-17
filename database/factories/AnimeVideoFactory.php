<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnimeVideo>
 */
class AnimeVideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
           'anime_name_id' => rand(1,100),
           'anime_eps' => fake()->name(),
           'resolution' => '480',
           'duration' => 24,
           'video_format' => 'mp4',
           'video_url' => 'http://onime.test/storage/F-Miyako Mimosa/Otakudesu.bid_Vld.Saga.S2--01_480p.mp4'

        ];
    }
}
