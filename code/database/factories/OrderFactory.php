<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Order;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'start' => date('Y-m-d'),
            'end' => date('Y-m-d', strtotime('+5 days')),
            'shipping' => date('Y-m-d', strtotime('+6 days')),
            'status' => 'open',
        ];
    }
}
