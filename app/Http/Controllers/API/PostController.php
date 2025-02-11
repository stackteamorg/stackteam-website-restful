<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return Post::with('tags')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts',
            'content' => 'required|string',
            'excerpt' => 'sometimes|string|max:300',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tags' => 'sometimes|array',
        ]);

        // Process and store image
        $imagePath = $this->handleImageUpload($request->file('image'));

        $post = Post::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'image' => $imagePath,
            'user_id' => auth()->id(),
            'status' => $request->status ?? 'draft',
            'published_at' => $request->published_at,
        ]);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load('tags'), 201);
    }

    public function show(Post $post)
    {
        return $post->load('tags');
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:posts,slug,'.$post->id,
            'content' => 'sometimes|string',
            'excerpt' => 'sometimes|string|max:300',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tags' => 'sometimes|array',
        ]);

        // Update image if new one is provided
        if ($request->hasFile('image')) {
            // Delete old image
            Storage::delete($post->image);

            // Store new image
            $imagePath = $this->handleImageUpload($request->file('image'));
            $post->image = $imagePath;
        }

        $post->update($request->except('image', 'tags'));

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load('tags'));
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
            ->limit(10)
            ->get();
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
