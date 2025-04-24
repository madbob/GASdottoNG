<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstname = fake('it_IT')->lastName();

        return [
            'username' => fake()->unique()->numerify(Str::slug($firstname) . '.####'),
            'firstname' => $firstname,
            'lastname' => fake('it_IT')->lastName(),
            'password' => static::$password ??= Hash::make('password'),
            'taxcode' => fake('it_IT')->taxId(),
            'member_since' => fake()->dateTimeThisDecade(),
            'card_number' => fake()->creditCardNumber(),
        ];
    }
}
