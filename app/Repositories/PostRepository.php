<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PostRepository
{
    /**
     * How long the published posts list stays cached, in seconds.
     *
     * Posts can also be edited directly in Supabase, which bypasses model
     * events, so the TTL bounds how stale the list can get.
     */
    public const PUBLISHED_CACHE_TTL = 60;

    /**
     * Get all published posts, newest first.
     *
     * @return Collection<int, Post>
     */
    public function getPublished(): Collection
    {
        // Cache raw attributes: the cache store refuses to unserialize model objects.
        $rows = Cache::remember(
            Post::PUBLISHED_CACHE_KEY,
            self::PUBLISHED_CACHE_TTL,
            fn () => Post::query()
                ->published()
                ->latest()
                ->latest('id')
                ->get()
                ->map(fn (Post $post) => $post->getAttributes())
                ->all(),
        );

        return Post::hydrate($rows);
    }

    /**
     * Store a new post.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Post
    {
        return Post::create($attributes);
    }
}
