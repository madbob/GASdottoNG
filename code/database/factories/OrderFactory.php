<?php

namespace Database\Factories;

use App\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Aggregate;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'aggregate_id' => Aggregate::factory(),
            'comment' => fake()->sentence(),
            'status' => fake()->randomElement(['suspended', 'open', 'closed', 'shipped', 'user_payment', 'archived']),
            'start' => fake()->dateTimeInInterval('-1 week', '-1 day'),
            'end' => fake()->dateTimeInInterval('1 day', '1 week'),
            'shipping' => fake()->dateTimeInInterval('1 week', '2 week'),
        ];
    }
}
