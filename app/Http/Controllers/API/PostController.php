<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PostController extends Controller
{
    public function index(Request $request)
    {
        // Paginate the posts with 12 posts per page
        $posts = Post::with(['tags', 'category:id,name,slug'])->paginate(12);


        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'slug'       => 'required|string|unique:posts',
            'content'    => 'required|string',
            'excerpt'    => 'sometimes|string|max:300',
            'image'      => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tags'       => 'sometimes|array',
            'category_id'=> 'sometimes|exists:categories,id',
            'post_type'  => 'sometimes|string|in:blog,services,technologies',
        ]);

        // Process and store image
        $imagePath = $this->handleImageUpload($request->file('image'));

        $post = Post::create([
            'title'        => $request->title,
            'slug'         => $request->slug,
            'content'      => $request->content,
            'excerpt'      => $request->excerpt,
            'image'        => $imagePath,
            'user_id'      => auth()->id(),
            'status'       => $request->status ?? 'draft',
            'published_at' => $request->published_at,
            'category_id'  => $request->category_id,  // new!
            'post_type'    => $request->post_type,    // new!
        ]);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load('tags', 'category'), 201);
    }

    public function show(Post $post)
    {
        return response()->json($post->load('tags', 'category:id,name,slug'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'slug'        => 'sometimes|string|unique:posts,slug,'.$post->id,
            'content'     => 'sometimes|string',
            'excerpt'     => 'sometimes|string|max:300',
            'image'       => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tags'        => 'sometimes|array',
            'category_id' => 'sometimes|exists:categories,id',
            'post_type'   => 'sometimes|string|in:blog,services,technologies',
        ]);

        // Update image if new one is provided
        if ($request->hasFile('image')) {
            // Delete old image
            Storage::delete($post->image);

            // Store new image
            $imagePath = $this->handleImageUpload($request->file('image'));
            $post->image = $imagePath;
        }

        // Update post fields
        $post->update($request->except('image', 'tags'));

        // Update tags if provided
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load('tags', 'category'));
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->noContent();
    }

    public function popularPosts()
    {
        return Post::orderBy('views', 'desc')
            ->where('status', 'published')
            ->limit(3)
            ->get();
    }

    public function postsByCategory($categorySlug)
    {
        $posts = Post::with('tags', 'category')
            ->whereHas('category', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            })->paginate(12);

        return response()->json($posts);
    }

    public function postsByTag($tagSlug)
    {
        $posts = Post::with('tags', 'category')
            ->whereHas('tags', function ($query) use ($tagSlug) {
                $query->where('slug', $tagSlug);
            })->paginate(12);

        return response()->json($posts);
    }

    private function handleImageUpload($imageFile)
    {
        // Resize and optimize image
        $image = Image::make($imageFile)
            ->fit(1200, 630) // Adjust dimensions as needed
            ->encode('webp', 75); // Convert to WebP format

        $filename = 'posts/'.md5(time().$imageFile->getClientOriginalName()).'.webp';

        Storage::disk('public')->put($filename, $image);

        return $filename;
    }
}
