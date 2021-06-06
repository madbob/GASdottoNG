<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Supplier;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'business_name' => $this->faker->company(),
            'payment_method' => $this->faker->text(100),
            'order_method' => $this->faker->text(100)
        ];
    }
}
