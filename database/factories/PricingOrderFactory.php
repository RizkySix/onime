<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingOrder>
 */
class PricingOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => rand(1,100),
            'order_id' => Str::random(5) . mt_rand(100000,999999),
            'payment_type' => 'bca',
            'transaction_status' => 'pending',
            'pricing_type' => 'Mega vip',
            'pricing_duration_in_days' => 90,
            'pricing_price' => 100000.00,
            'gross_amount' => 50000.00,
            'pricing_discount' => 50,
            'payment_number' => mt_rand(100000,999999),
            'transaction_time' => Carbon::now(),
        ];
    }
}
