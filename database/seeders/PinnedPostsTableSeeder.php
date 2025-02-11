<?php

namespace Database\Seeders;

use App\Models\PinnedPost;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PinnedPostsTableSeeder extends Seeder
{
    public function run()
    {
        $posts = Post::where('status', 'published')
            ->inRandomOrder()
            ->take(3)
            ->get();

        foreach ($posts as $index => $post) {
            PinnedPost::create([
                'post_id' => $post->id,
                'position' => $index + 1
            ]);
        }
    }
}
