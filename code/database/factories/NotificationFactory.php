<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->word(),
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+10 days')),
            'mailed' => false,
        ];
    }
}
