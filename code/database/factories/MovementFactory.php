<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Movement;

class MovementFactory extends Factory
{
    protected $model = Movement::class;

    public function definition()
    {
        return [
            'amount' => $this->faker->randomNumber(2)
        ];
    }
}
