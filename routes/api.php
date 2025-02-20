<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\PinnedPostController;
use App\Http\Controllers\API\TopBarController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/popular-tags', [TagController::class, 'popularTags']);
Route::get('/tag/{slug}', [TagController::class, 'showBySlug']);
Route::get('/popular-posts', [PostController::class, 'popularPosts']);
Route::get('/pinned-posts', [PinnedPostController::class, 'getPinned']);
Route::get('/top-bar', [TopBarController::class, 'index'])->name('top-bar.index');
Route::get('/posts/{post}', [PostController::class, 'show']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/category/{category}', [PostController::class, 'postsByCategory']);
Route::get('category/{identifier}', [CategoriesController::class, 'show']);
Route::get('/posts/tag/{tag}', [PostController::class, 'postsByTag']);
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoriesController::class, 'index']);
    Route::get('/{identifier}', [CategoriesController::class, 'show']);
    Route::get('/search', [CategoriesController::class, 'search']);
});

// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Top Bar Management
    Route::apiResource('top-bar', TopBarController::class)
        ->parameters(['top-bar' => 'topBar'])
        ->except(['index', 'show']);

    // Posts management
    Route::apiResource('posts', PostController::class)->except(['index', 'show']);

    // Category Management
    Route::apiResource('categories', CategoriesController::class)
        ->except(['index', 'show', 'search'])
        ->middleware('can:manage-content');

    // Pinned Posts management
    Route::prefix('pinned-posts')->group(function () {
        Route::post('/', [PinnedPostController::class, 'store']);
        Route::delete('/{pinnedPost}', [PinnedPostController::class, 'destroy']);
    });

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
