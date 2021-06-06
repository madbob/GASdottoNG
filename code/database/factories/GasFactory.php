<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Gas;

class GasFactory extends Factory
{
    protected $model = Gas::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
