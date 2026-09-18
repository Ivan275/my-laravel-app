<?php

namespace Tests\Feature;

use App\Jobs\ProcessNewPost;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ProcessNewPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_a_summary_of_the_post(): void
    {
        $post = Post::factory()->create([
            'title' => 'Long read',
            'content' => str_repeat('word ', 450),
        ]);

        Log::shouldReceive('info')->once()->with('Processed new post', [
            'post_id' => $post->id,
            'title' => 'Long read',
            'words' => 450,
            'reading_minutes' => 3,
        ]);

        (new ProcessNewPost($post))->handle();
    }

    public function test_short_posts_take_at_least_one_minute_to_read(): void
    {
        $post = Post::factory()->create(['content' => 'Just a few words']);

        Log::shouldReceive('info')->once()->withArgs(
            fn (string $message, array $context) => $context['words'] === 4 && $context['reading_minutes'] === 1,
        );

        (new ProcessNewPost($post))->handle();
    }
}
