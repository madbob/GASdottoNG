<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Delivery;

class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'address' => sprintf('%s, %s, %s', $this->faker->streetAddress(), $this->faker->city(), $this->faker->randomNumber(5, true)),
        ];
    }
}
