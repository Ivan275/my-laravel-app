<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_post(): void
    {
        $response = $this->postJson(route('api.v1.posts.store'), [
            'title' => 'Hello',
            'content' => 'First post body',
            'author' => 'Ivan',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Hello')
            ->assertJsonPath('data.content', 'First post body')
            ->assertJsonPath('data.author', 'Ivan')
            ->assertJsonStructure(['data' => ['id', 'title', 'content', 'author', 'created_at']]);

        $this->assertDatabaseHas('posts', ['title' => 'Hello', 'author' => 'Ivan', 'published' => true]);
    }

    public function test_a_new_post_appears_in_the_cached_list(): void
    {
        Post::factory()->create();

        $this->getJson(route('api.v1.posts.index'))->assertJsonCount(1, 'data');

        $this->postJson(route('api.v1.posts.store'), [
            'title' => 'Fresh',
            'content' => 'Should bust the cache',
        ])->assertCreated();

        $this->getJson(route('api.v1.posts.index'))
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Fresh');
    }

    public function test_it_requires_a_title_and_content(): void
    {
        $this->postJson(route('api.v1.posts.store'), ['author' => 'Ivan'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content']);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_it_rejects_overly_long_values(): void
    {
        $this->postJson(route('api.v1.posts.store'), [
            'title' => str_repeat('a', 256),
            'content' => 'Body',
            'author' => str_repeat('b', 101),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'author']);
    }
}
