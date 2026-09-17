<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    /**
     * List published posts, one page at a time.
     */
    public function index(Request $request, PostService $postService): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $posts = $postService->listPublishedPosts(
            perPage: (int) ($validated['per_page'] ?? 10),
            page: (int) ($validated['page'] ?? 1),
        );

        return PostResource::collection($posts->withQueryString());
    }

    /**
     * Create a new post.
     */
    public function store(StorePostRequest $request, PostService $postService): PostResource
    {
        return new PostResource($postService->createPost($request->validated()));
    }
}
