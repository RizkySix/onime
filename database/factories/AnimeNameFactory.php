<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnimeName>
 */
class AnimeNameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'anime_name' => fake()->name(),
            'slug' => Str::slug(Str::random(10)),
            'total_episode' => 24,
            'studio' => 'mappa',
            'author' => 'gua',
            'description' => 'kosong'

        ];
    }
}
