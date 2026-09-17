<?php

namespace App\Models;

use App\Repositories\PostRepository;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'content', 'author', 'published'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * The posts table has no updated_at column.
     */
    public const UPDATED_AT = null;

    /**
     * Cache key holding the current version of the cached published pages.
     */
    public const PUBLISHED_CACHE_VERSION_KEY = 'posts.published.version';

    /**
     * Clear the cached posts pages whenever a post changes through Eloquent.
     */
    protected static function booted(): void
    {
        static::saved(fn () => PostRepository::flushPublishedCache());
        static::deleted(fn () => PostRepository::flushPublishedCache());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include published posts.
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('published', true);
    }
}
