<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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

    /**
     * Update an existing post.
     */
    public function update(UpdatePostRequest $request, Post $post, PostService $postService): PostResource
    {
        return new PostResource($postService->updatePost($post, $request->validated()));
    }

    /**
     * Delete a post.
     */
    public function destroy(Post $post, PostService $postService): Response
    {
        $postService->deletePost($post);

        return response()->noContent();
    }
}
