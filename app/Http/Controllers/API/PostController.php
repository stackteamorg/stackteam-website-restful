<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
            'title'        => 'required|string|max:255',
            'slug'         => 'required|string|unique:posts',
            'content'      => 'required|string',
            'excerpt'      => 'sometimes|string|max:300',
            'image'        => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tags'         => 'sometimes|array',
            'tags.*.name'  => 'required|string',
            'tags.*.slug'  => 'required|string',
            'category_id'  => 'sometimes|exists:categories,id',
            'post_type'    => 'sometimes|string|in:blog,services,technologies',
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
            'category_id'  => $request->category_id,
            'post_type'    => $request->post_type,
        ]);

        // Process tags: create or retrieve each tag by its slug and name, then sync IDs
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagData) {
                $tag = \App\Models\Tag::firstOrCreate(
                    ['slug' => $tagData['slug']],
                    ['name' => $tagData['name']]
                );
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        if ($request->has('open_graph')) {
            $ogData = $request->input('open_graph');
            if ($request->hasFile('open_graph.og_image')) {
                $ogImagePath = $this->handleImageUpload($request->file('open_graph.og_image'));
                $ogData['og_image'] = $ogImagePath;
            }
            $post->openGraph()->create($ogData);
        }

        return response()->json($post->load('tags', 'category'), 201);
    }

    public function show(Post $post)
    {
        return response()->json($post->load('tags', 'category:id,name,slug'));
    }


    public function update(Request $request, Post $post)
    {
        // First, let's properly parse multipart form data
        // Debug logging to understand what's coming in
        \Log::debug('All request data keys: ', array_keys($request->all()));
        \Log::debug('POST keys: ', array_keys($_POST));
        \Log::debug('FILES keys: ', array_keys($_FILES));

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:posts,slug,'.$post->id,
            'content' => 'sometimes|string',
            'excerpt' => 'sometimes|string|max:300',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tags' => 'sometimes|array',
            'tags.*.name' => 'required|string',
            'tags.*.slug' => 'required|string',
            'category_id' => 'sometimes|exists:categories,id',
            'post_type' => 'sometimes|string|in:blog,services,technologies',
            'status' => 'sometimes|string|in:draft,published',
        ]);

        // Special handling for multipart content - try multiple approaches
        $content = null;

        // Approach 1: Try direct input access
        if ($request->has('content')) {
            $content = $request->input('content');
            \Log::debug('Content found via direct input: ' . substr($content, 0, 100) . '...');
        }

        // Approach 2: Check if content exists in $_POST which is more direct for multipart
        if (empty($content) && isset($_POST['content'])) {
            $content = $_POST['content'];
            \Log::debug('Content found via $_POST: ' . substr($content, 0, 100) . '...');
        }

        // Approach 3: Parse raw content if needed
        if (empty($content) && strpos($request->header('Content-Type'), 'multipart/form-data') !== false) {
            // Try to extract content from the raw input stream for multipart data
            $rawContent = file_get_contents('php://input');
            \Log::debug('Raw input length: ' . strlen($rawContent));

            // Look for content boundary marker
            if (preg_match('/name="content".*?\r\n\r\n(.*?)(?:\r\n-{2,}|$)/s', $rawContent, $matches)) {
                $content = $matches[1];
                \Log::debug('Content extracted from raw input: ' . substr($content, 0, 100) . '...');
            }
        }

        // Final fallback - keep existing content if nothing detected
        if (empty($content)) {
            $content = $post->content;
            \Log::debug('Using existing post content as fallback');
        }

        // Prepare update data with our carefully extracted content
        $updateData = [
            'title' => $request->input('title', $post->title),
            'slug' => $request->input('slug', $post->slug),
            'content' => $content, // Use our specially handled content field
            'excerpt' => $request->input('excerpt', $post->excerpt),
            'category_id' => $request->input('category_id', $post->category_id),
            'post_type' => $request->input('post_type', $post->post_type),
            'status' => $request->input('status', $post->status),
        ];

        \Log::debug('Final update data content length: ' . strlen($updateData['content']));

        // Handle image upload
        if ($request->hasFile('image')) {
            Storage::delete($post->image);
            $updateData['image'] = $this->handleImageUpload($request->file('image'));
        }

        // Update post with our carefully prepared data
        $post->update($updateData);

        // Process tags
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagData) {
                $tag = \App\Models\Tag::firstOrCreate(
                    ['slug' => $tagData['slug']],
                    ['name' => $tagData['name']]
                );
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        // Process Open Graph data
        if ($request->has('open_graph')) {
            $ogData = $request->input('open_graph');
            if ($request->hasFile('open_graph.og_image')) {
                $ogImagePath = $this->handleImageUpload($request->file('open_graph.og_image'));
                $ogData['og_image'] = $ogImagePath;
            }

            if ($post->openGraph) {
                $post->openGraph->update($ogData);
            } else {
                $post->openGraph()->create($ogData);
            }
        }

        // Return fresh post with relationships
        return response()->json($post->fresh()->load('tags', 'category'));
    }

    public function destroy(Post $post)
    {
        // Detach all tags from the post to clear the pivot table
        $post->tags()->detach();

        // Delete related Open Graph data if exists
        if ($post->openGraph) {
            $post->openGraph()->delete();
        }

        // Delete the post's image from storage
        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        // Finally, delete the post itself
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
        $manager = new ImageManager(new Driver());

        $image = $manager->read($imageFile)
            ->cover(1200, 630)
            ->toWebp(75);

        $filename = 'posts/'.md5(time().$imageFile->getClientOriginalName()).'.webp';
        Storage::disk('public')->put($filename, $image->toString());

        return $filename;
    }
}
