<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_published_posts_newest_first(): void
    {
        $older = Post::factory()->create(['created_at' => now()->subDay()]);
        $newer = Post::factory()->create(['created_at' => now()]);
        Post::factory()->unpublished()->create();

        $response = $this->getJson(route('api.v1.posts.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id)
            ->assertJsonStructure(['data' => [['id', 'title', 'content', 'author', 'created_at']]]);
    }
}
