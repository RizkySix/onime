<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VipUser>
 */
class VipUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pricing_id' => rand(1,100),
            'user_id' => rand(1,100),
            'vip_duration' => Carbon::now()->addDays(90),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
