<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
     * Cache key for the published posts list.
     */
    public const PUBLISHED_CACHE_KEY = 'posts.published';

    /**
     * Clear the cached posts list whenever a post changes through Eloquent.
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::PUBLISHED_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::PUBLISHED_CACHE_KEY));
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
