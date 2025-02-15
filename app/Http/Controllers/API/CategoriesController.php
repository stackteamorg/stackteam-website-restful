<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    /**
     * لیست همه دسته‌بندی‌ها با تعداد پست‌ها (مرتب شده)
     */
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->get()
            ->map(function ($category) { // Replaced through() with map()
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'posts_count' => $category->posts_count,
                    'created_at' => $category->created_at,
                ];
            });

        return response()->json($categories);
    }

    /**
     * نمایش جزئیات یک دسته‌بندی خاص
     */
    public function show($identifier): JsonResponse
    {
        $category = Category::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->firstOrFail();

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'posts_count' => $category->posts_count,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ]);
    }

    /**
     * ایجاد دسته‌بندی جدید
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'nullable|string|max:255|unique:categories',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    /**
     * به‌روزرسانی دسته‌بندی
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'sometimes|string|max:255|unique:categories,slug,' . $category->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return response()->json($category);
    }

    /**
     * حذف دسته‌بندی
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->posts()->exists()) {
            return response()->json([
                'message' => 'امکان حذف دسته‌بندی با پست‌های مرتبط وجود ندارد'
            ], 400);
        }

        $category->delete();
        return response()->json(null, 204);
    }

    /**
     * جستجو در دسته‌بندی‌ها
     */
    public function search(Request $request): JsonResponse
    {
        $query = Category::query()
            ->withCount('posts')
            ->orderByDesc('posts_count');

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('min_posts')) {
            $query->having('posts_count', '>=', $request->min_posts);
        }

        $results = $query->get(); // Replaced paginate(10) with get()

        return response()->json($results);
    }
}
