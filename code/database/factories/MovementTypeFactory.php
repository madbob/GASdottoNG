<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\MovementType;

class MovementTypeFactory extends Factory
{
    protected $model = MovementType::class;

    public function definition()
    {
        return [
            'function' => '[]',
        ];
    }
}
