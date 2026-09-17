<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_a_post(): void
    {
        $post = Post::factory()->create();

        $this->deleteJson(route('api.v1.posts.destroy', $post))->assertNoContent();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_it_returns_not_found_for_a_missing_post(): void
    {
        $this->deleteJson(route('api.v1.posts.destroy', 999))->assertNotFound();
    }

    public function test_deleting_a_post_refreshes_the_cached_list(): void
    {
        $post = Post::factory()->create();
        Post::factory()->create();

        $this->getJson(route('api.v1.posts.index'))->assertJsonCount(2, 'data');

        $this->deleteJson(route('api.v1.posts.destroy', $post))->assertNoContent();

        $this->getJson(route('api.v1.posts.index'))
            ->assertJsonCount(1, 'data')
            ->assertJsonMissing(['id' => $post->id]);
    }
}
