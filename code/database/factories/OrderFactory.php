<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Order;
use App\Aggregate;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        $aggregate = Aggregate::factory()->create();

        return [
            'start' => date('Y-m-d'),
            'end' => date('Y-m-d', strtotime('+5 days')),
            'shipping' => date('Y-m-d', strtotime('+6 days')),
            'status' => 'open',
            'aggregate_id' => $aggregate->id,
        ];
    }
}
