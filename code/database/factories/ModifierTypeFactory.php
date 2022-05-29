<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\ModifierType;

class ModifierTypeFactory extends Factory
{
    protected $model = ModifierType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
