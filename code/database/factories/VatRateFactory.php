<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\VatRate;

class VatRateFactory extends Factory
{
    protected $model = VatRate::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'percentage' => $this->faker->randomNumber(2),
        ];
    }
}
