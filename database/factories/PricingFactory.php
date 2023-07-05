<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pricing>
 */
class PricingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pricing_name' => fake()->name() . rand(1 , 9999),
            'vip_power' => 'NORMAL',
            'price' => rand(100,999),
            'discount' => 15,
            'duration' => 90,
            'description' => fake()->text()
        ];
    }
}
