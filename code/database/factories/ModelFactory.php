<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'username' => $faker->userName,
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'member_since' => date("Y-m-d H:i:s")
    ];
});

$factory->define(App\Gas::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'bank_balance' => $faker->numberBetween(0, 10),
        'cash_balance' => $faker->numberBetween(0, 10),
        'suppliers_balance' => $faker->numberBetween(0, 10),
        'deposit_balance' => $faker->numberBetween(0, 10),
    ];
});

$factory->define(App\Permission::class, function (Faker\Generator $faker) {
    return [
        'target_type' => 'App\Gas',
    ];
});
