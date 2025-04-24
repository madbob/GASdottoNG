<?php

namespace Database\Factories;

use App\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Aggregate;

class OrderFactory extends Factory
{
    public function definition()
    {
        return [
            'supplier_id' => Supplier::factory(),
            'status' => 'open',
            'start' => fake()->dateTimeInInterval('-1 week', '-1 day'),
            'end' => fake()->dateTimeInInterval('1 day', '1 week'),
            'shipping' => fake()->dateTimeInInterval('1 week', '2 week'),
            'aggregate_id' => Aggregate::factory(),
        ];
    }
}
