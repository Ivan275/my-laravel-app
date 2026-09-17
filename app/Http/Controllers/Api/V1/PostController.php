<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    /**
     * List published posts.
     */
    public function index(PostService $postService): AnonymousResourceCollection
    {
        return PostResource::collection($postService->listPublishedPosts());
    }
}
