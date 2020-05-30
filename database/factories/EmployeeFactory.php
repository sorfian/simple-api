<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Employee;
use Faker\Generator as Faker;

$factory->define(Employee::class, function (Faker $faker) {
    $avatar_path = 'storage/app/public/avatars';
    return [
        'full_name' => $faker->name,
        'nick_name' => $faker->lastName,
        'age' => $faker->numberBetween(20,60),
        'birth_date' => $faker->date('Y-m-d', 'now'),
        'address' => $faker->address,
        'mobile' => $faker->e164PhoneNumber,
        'avatar' => $faker->image($avatar_path, 640, 480),
        'created_by' => $faker->numberBetween(1,10),
        'modify_by' => $faker->numberBetween(1,10),
    ];
});
