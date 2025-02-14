<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\PinnedPostController;
use App\Http\Controllers\API\TopBarController;
use App\Http\Controllers\Auth\RegisteredUserController;
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
Route::get('/popular-tags', [TagController::class, 'popularTags']);
Route::get('/popular-posts', [PostController::class, 'popularPosts']);
Route::get('/pinned-posts', [PinnedPostController::class, 'getPinned']);
Route::get('/top-bar', [TopBarController::class, 'index'])->name('top-bar.index');
Route::get('/posts/{post}', [PostController::class, 'show']); // Public route to view a single post
Route::get('/posts', [PostController::class, 'index']); // Public route to list posts (paginated)
Route::get('/posts/category/{category}', [PostController::class, 'postsByCategory']);
Route::get('/posts/tag/{tag}', [PostController::class, 'postsByTag']);

// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Top Bar Management - Only allow create/update/delete operations
    Route::apiResource('top-bar', TopBarController::class)
        ->parameters(['top-bar' => 'topBar'])
        ->except(['index', 'show']); // Exclude public routes

    // Posts management (create, update, delete)
    Route::apiResource('posts', PostController::class)->except(['index', 'show']);

    // Pinned Posts management
    Route::prefix('pinned-posts')->group(function () {
        Route::post('/', [PinnedPostController::class, 'store']);
        Route::delete('/{pinnedPost}', [PinnedPostController::class, 'destroy']);
    });

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
