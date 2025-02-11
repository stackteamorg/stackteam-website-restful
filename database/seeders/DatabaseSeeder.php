<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            TagsTableSeeder::class,
            PostsTableSeeder::class,
            PostTagTableSeeder::class,
            PinnedPostsTableSeeder::class,
            TopBarsTableSeeder::class,
        ]);
    }
}
