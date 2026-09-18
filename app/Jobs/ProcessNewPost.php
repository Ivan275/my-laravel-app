<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Example background job: runs after a post is created and logs a short
 * content summary. Replace the body of handle() with real work (sending
 * notifications, generating previews, indexing for search, ...).
 */
class ProcessNewPost implements ShouldQueue
{
    use Queueable;

    /**
     * Words an average reader gets through per minute.
     */
    public const WORDS_PER_MINUTE = 200;

    /**
     * Seconds the job may run; stays below the queue's retry_after (90s).
     */
    public int $timeout = 30;

    /**
     * Attempts before the job is marked as failed.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before each retry.
     *
     * @var list<int>
     */
    public array $backoff = [5, 30];

    /**
     * Skip the job instead of failing if the post is deleted before it runs.
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public Post $post) {}

    public function handle(): void
    {
        $words = Str::wordCount($this->post->content);

        Log::info('Processed new post', [
            'post_id' => $this->post->id,
            'title' => $this->post->title,
            'words' => $words,
            'reading_minutes' => max(1, (int) ceil($words / self::WORDS_PER_MINUTE)),
        ]);
    }
}
