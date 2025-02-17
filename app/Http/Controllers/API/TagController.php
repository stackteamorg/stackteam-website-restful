<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function popularTags()
    {
        return Tag::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($tag) {
                return [
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'post_count' => $tag->posts_count
                ];
            });
    }

    // New method to get tag by slug
    public function showBySlug($slug)
    {
        $tag = Tag::where('slug', $slug)
            ->withCount('posts')
            ->firstOrFail();

        return [
            'name' => $tag->name,
            'slug' => $tag->slug,
            'post_count' => $tag->posts_count,
            'created_at' => $tag->created_at
        ];
    }
}
