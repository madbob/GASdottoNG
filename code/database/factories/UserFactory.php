<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'username' => $this->faker->userName(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'password' => bcrypt(str_random(10)),
            'member_since' => date("Y-m-d H:i:s")
        ];
    }
}
