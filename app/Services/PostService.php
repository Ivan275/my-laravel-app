<?php

namespace App\Services;

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
     * Create a post from validated input.
     *
     * @param  array{title: string, content: string, author?: ?string, published?: bool}  $attributes
     */
    public function createPost(array $attributes): Post
    {
        return $this->posts->create($attributes);
    }
}
