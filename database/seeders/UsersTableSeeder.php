<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'مدیر سیستم',
            'email' => 'admin@example.com',
            'password' => bcrypt('12345678'),
        ]);

        // Create regular users
        $faker = \Faker\Factory::create('fa_IR');

        for ($i = 0; $i < 5; $i++) {
            User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('12345678'),
            ]);
        }
    }
}
