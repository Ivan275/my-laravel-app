<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_counts_all_posts_by_published_state(): void
    {
        Post::factory()->count(3)->create();
        Post::factory()->unpublished()->count(2)->create();

        $this->getJson(route('api.v1.posts.stats'))
            ->assertOk()
            ->assertExactJson(['data' => ['total' => 5, 'published' => 3, 'unpublished' => 2]]);
    }

    public function test_it_returns_zero_counts_when_there_are_no_posts(): void
    {
        $this->getJson(route('api.v1.posts.stats'))
            ->assertOk()
            ->assertExactJson(['data' => ['total' => 0, 'published' => 0, 'unpublished' => 0]]);
    }

    public function test_the_counts_refresh_when_posts_change(): void
    {
        $post = Post::factory()->create();

        $this->getJson(route('api.v1.posts.stats'))->assertJsonPath('data.total', 1);

        Post::factory()->unpublished()->create();
        $this->getJson(route('api.v1.posts.stats'))
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.unpublished', 1);

        $post->delete();
        $this->getJson(route('api.v1.posts.stats'))
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.published', 0);
    }
}
