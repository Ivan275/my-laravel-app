<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PostRepository
{
    /**
     * How long each cached page of published posts lives, in seconds.
     *
     * Posts can also be edited directly in Supabase, which bypasses model
     * events, so the TTL bounds how stale a page can get.
     */
    public const PUBLISHED_CACHE_TTL = 60;

    /**
     * Get one page of published posts, newest first.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function paginatePublished(int $perPage, int $page): LengthAwarePaginator
    {
        $key = sprintf('posts.published.%s.page.%d.per.%d', self::publishedCacheVersion(), $page, $perPage);

        // Cache raw attributes: the cache store refuses to unserialize model objects.
        $cached = Cache::remember($key, self::PUBLISHED_CACHE_TTL, function () use ($perPage, $page) {
            $paginator = Post::query()
                ->published()
                ->latest()
                ->latest('id')
                ->paginate($perPage, page: $page);

            return [
                'rows' => $paginator->getCollection()->map(fn (Post $post) => $post->getAttributes())->all(),
                'total' => $paginator->total(),
            ];
        });

        return new Paginator(
            Post::hydrate($cached['rows']),
            $cached['total'],
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()],
        );
    }

    /**
     * Invalidate every cached page of published posts at once.
     *
     * Page keys embed the version, so changing it orphans the old pages,
     * which then expire through their TTL.
     */
    public static function flushPublishedCache(): void
    {
        Cache::forget(Post::PUBLISHED_CACHE_VERSION_KEY);
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

    /**
     * Update a post.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Post $post, array $attributes): Post
    {
        $post->update($attributes);

        return $post;
    }

    /**
     * Delete a post.
     */
    public function delete(Post $post): void
    {
        $post->delete();
    }

    private static function publishedCacheVersion(): string
    {
        return Cache::rememberForever(Post::PUBLISHED_CACHE_VERSION_KEY, fn () => Str::random(12));
    }
}
