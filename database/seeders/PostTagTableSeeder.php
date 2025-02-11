<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostTagTableSeeder extends Seeder
{
    public function run()
    {
        $posts = Post::all();
        $tags = \App\Models\Tag::pluck('id')->toArray();

        foreach ($posts as $post) {
            $post->tags()->attach(
                array_slice($tags, 0, rand(2, 4))
            );
        }
    }
}
