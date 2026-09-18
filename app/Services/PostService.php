<?php

namespace App\Services;

use App\Jobs\ProcessNewPost;
use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostService
{
    public function __construct(private PostRepository $posts) {}

    /**
     * List one page of the posts that are visible to readers.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function listPublishedPosts(int $perPage, int $page): LengthAwarePaginator
    {
        return $this->posts->paginatePublished($perPage, $page);
    }

    /**
     * Create a post from validated input, then process it in the background.
     *
     * @param  array{title: string, content: string, author?: ?string, published?: bool}  $attributes
     */
    public function createPost(array $attributes): Post
    {
        $post = $this->posts->create($attributes);

        ProcessNewPost::dispatch($post);

        return $post;
    }

    /**
     * Count posts for the dashboard.
     *
     * @return array{total: int, published: int, unpublished: int}
     */
    public function stats(): array
    {
        return $this->posts->stats();
    }

    /**
     * Update a post with validated input.
     *
     * @param  array{title?: string, content?: string, author?: ?string, published?: bool}  $attributes
     */
    public function updatePost(Post $post, array $attributes): Post
    {
        return $this->posts->update($post, $attributes);
    }

    /**
     * Delete a post.
     */
    public function deletePost(Post $post): void
    {
        $this->posts->delete($post);
    }
}
