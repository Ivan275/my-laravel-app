<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;

class PostStatsController extends Controller
{
    /**
     * Summary counts for the dashboard.
     */
    public function __invoke(PostService $postService): JsonResponse
    {
        return response()->json(['data' => $postService->stats()]);
    }
}
