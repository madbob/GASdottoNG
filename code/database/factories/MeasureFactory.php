<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Measure;

class MeasureFactory extends Factory
{
    protected $model = Measure::class;

    public function definition()
    {
        return [
            'name' => $this->faker->text(10),
            'discrete' => true,
        ];
    }
}
