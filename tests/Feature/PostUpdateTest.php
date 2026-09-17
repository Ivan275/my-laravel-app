<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_a_post(): void
    {
        $post = Post::factory()->create(['title' => 'Old', 'author' => 'Ivan']);

        $this->putJson(route('api.v1.posts.update', $post), [
            'title' => 'New title',
            'content' => 'New content',
            'author' => null,
            'published' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', 'New title')
            ->assertJsonPath('data.author', null)
            ->assertJsonPath('data.published', false);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'New title',
            'content' => 'New content',
            'author' => null,
            'published' => false,
        ]);
    }

    public function test_it_updates_only_the_fields_sent(): void
    {
        $post = Post::factory()->create(['title' => 'Keep me', 'content' => 'Old content']);

        $this->patchJson(route('api.v1.posts.update', $post), ['content' => 'Changed'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Keep me')
            ->assertJsonPath('data.content', 'Changed');
    }

    public function test_it_rejects_blank_or_invalid_values(): void
    {
        $post = Post::factory()->create(['title' => 'Unchanged']);

        $this->putJson(route('api.v1.posts.update', $post), [
            'title' => '',
            'content' => str_repeat('a', 10001),
            'published' => 'maybe',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content', 'published']);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Unchanged']);
    }

    public function test_it_returns_not_found_for_a_missing_post(): void
    {
        $this->putJson(route('api.v1.posts.update', 999), ['title' => 'Nope'])->assertNotFound();
    }

    public function test_updating_a_post_refreshes_the_cached_list(): void
    {
        $post = Post::factory()->create(['title' => 'Before']);

        $this->getJson(route('api.v1.posts.index'))->assertJsonPath('data.0.title', 'Before');

        $this->putJson(route('api.v1.posts.update', $post), ['title' => 'After'])->assertOk();

        $this->getJson(route('api.v1.posts.index'))->assertJsonPath('data.0.title', 'After');
    }
}
