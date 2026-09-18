<?php

use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\PostStatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('posts/stats', PostStatsController::class)->name('posts.stats');
    Route::apiResource('posts', PostController::class)->only(['index', 'store', 'update', 'destroy']);
});
