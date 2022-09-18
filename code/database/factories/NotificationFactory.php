<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Notification;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition()
    {
        return [
            'content' => $this->faker->word(),
            'start_date' => date('Y-m-d'),
			'end_date' => date('Y-m-d', strtotime('+10 days')),
            'mailed' => false,
        ];
    }
}
