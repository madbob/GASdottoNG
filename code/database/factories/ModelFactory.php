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
        'password' => bcrypt(str_random(10)),
        'member_since' => date("Y-m-d H:i:s")
    ];
});

$factory->define(App\Gas::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(App\Supplier::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'business_name' => $faker->company,
        'payment_method' => $faker->text(100),
        'order_method' => $faker->text(100)
    ];
});

$factory->define(App\Category::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(10),
    ];
});

$factory->define(App\Measure::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(10),
    ];
});

$factory->define(App\Product::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(10),
        'price' => $faker->randomNumber(2)
    ];
});

$factory->define(App\Permission::class, function (Faker\Generator $faker) {
    return [
        'target_type' => 'App\Gas',
    ];
});
