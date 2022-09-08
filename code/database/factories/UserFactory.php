<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        do {
            $username = $this->faker->userName();
        } while(User::where('username', $username)->count() != 0);

        return [
            'username' => $username,
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'password' => bcrypt(str_random(10)),
            'member_since' => date("Y-m-d H:i:s"),
            'card_number' => str_random(20),
        ];
    }
}
