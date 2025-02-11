<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\PinnedPostController;
use App\Http\Controllers\API\TopBarController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Public routes
Route::get('/popular-tags', [TagController::class, 'popularTags']);
Route::get('/popular-posts', [PostController::class, 'popularPosts']);
Route::get('/pinned-posts', [PinnedPostController::class, 'getPinned']);
Route::get('/top-bar', [TopBarController::class, 'index'])->name('top-bar.index');
Route::get('/posts/{post}', [PostController::class, 'show']);
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Top Bar Management - Only allow create/update/delete operations
    Route::apiResource('top-bar', TopBarController::class)
        ->parameters(['top-bar' => 'topBar'])
        ->except(['index', 'show']); // Exclude public routes

    // Posts
    Route::apiResource('posts', PostController::class)->except(['show']);

    // Pinned Posts
    Route::prefix('pinned-posts')->group(function () {
        Route::post('/', [PinnedPostController::class, 'store']);
        Route::delete('/{pinnedPost}', [PinnedPostController::class, 'destroy']);
    });
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
