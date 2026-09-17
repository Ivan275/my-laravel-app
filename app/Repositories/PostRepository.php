<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class PostRepository
{
    /**
     * Get all published posts, newest first.
     *
     * @return Collection<int, Post>
     */
    public function getPublished(): Collection
    {
        return Post::query()
            ->published()
            ->latest()
            ->latest('id')
            ->get();
    }
}
