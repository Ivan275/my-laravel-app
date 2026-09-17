<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    public function __construct(private PostRepository $posts) {}

    /**
     * List the posts that are visible to readers.
     *
     * @return Collection<int, Post>
     */
    public function listPublishedPosts(): Collection
    {
        return $this->posts->getPublished();
    }
}
