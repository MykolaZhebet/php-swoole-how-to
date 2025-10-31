<?php

namespace App\Models\Factories;
use App\Infrastructure\DbAdapter;
use App\Models\User;
use \Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;


class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $faker = Faker::create();
        return [
            'name' => $faker->name,
            'email' => $faker->email,
            'password' => $faker->password,
        ];
    }
}