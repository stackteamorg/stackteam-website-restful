<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PostsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('fa_IR');
        $users = User::pluck('id')->toArray();

        for ($i = 0; $i < 20; $i++) {
            $post = Post::create([
                'title' => $faker->sentence(6),
                'slug' => $faker->unique()->slug,
                'content' => $faker->realText(2000),
                'excerpt' => $faker->paragraph(3),
                'user_id' => $faker->randomElement($users),
                'status' => $faker->randomElement(['draft', 'published']),
                'views' => $faker->numberBetween(0, 1500),
                'published_at' => $faker->boolean(80) ? Carbon::now()->subDays(rand(1, 60)) : null,
            ]);
        }
    }
}
