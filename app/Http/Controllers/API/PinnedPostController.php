<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\PinnedPost;
use App\Models\Post;
use Illuminate\Http\Request;

class PinnedPostController extends Controller
{
    public function index()
    {
        return PinnedPost::with('post')->orderBy('position')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'position' => 'required|numeric|between:1,3'
        ]);

        // Remove existing pinned post at this position
        PinnedPost::where('position', $request->position)->delete();

        return PinnedPost::create($request->all());
    }

    public function getPinned()
    {
        $pinned = PinnedPost::with('post')
            ->orderBy('position')
            ->take(3)
            ->get();

        // If less than 3 pinned posts, fill with popular posts
        if ($pinned->count() < 3) {
            $popularPosts = Post::orderBy('views', 'desc')
                ->whereNotIn('id', $pinned->pluck('post_id'))
                ->take(3 - $pinned->count())
                ->get();

            return $pinned->merge($popularPosts);
        }

        return $pinned;
    }

    public function destroy(PinnedPost $pinnedPost)
    {
        $pinnedPost->delete();
        return response()->noContent();
    }
}
