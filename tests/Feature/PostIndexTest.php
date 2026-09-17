<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Serialize like the file and redis stores do, which refuse to unserialize objects.
        config(['cache.stores.array.serialize' => true]);
        Cache::forgetDriver('array');
    }

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

    public function test_it_serves_published_posts_from_the_cache(): void
    {
        Post::factory()->create();

        $this->getJson(route('api.v1.posts.index'))->assertJsonCount(1, 'data');

        // Inserting without Eloquent skips model events, like editing in Supabase directly.
        DB::table('posts')->insert(['title' => 'Direct', 'content' => 'Inserted outside Eloquent', 'published' => true]);

        $this->getJson(route('api.v1.posts.index'))
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_clears_the_cache_when_a_post_is_saved(): void
    {
        $post = Post::factory()->create();

        $this->getJson(route('api.v1.posts.index'))->assertJsonCount(1, 'data');

        Post::factory()->create();

        $this->getJson(route('api.v1.posts.index'))->assertJsonCount(2, 'data');

        $post->update(['published' => false]);

        $this->getJson(route('api.v1.posts.index'))->assertJsonCount(1, 'data');
    }

    public function test_it_clears_the_cache_when_a_post_is_deleted(): void
    {
        $post = Post::factory()->create();

        $this->getJson(route('api.v1.posts.index'))->assertJsonCount(1, 'data');

        $post->delete();

        $this->getJson(route('api.v1.posts.index'))->assertJsonCount(0, 'data');
    }

    public function test_it_paginates_published_posts(): void
    {
        $posts = Post::factory()
            ->count(5)
            ->sequence(fn ($sequence) => ['created_at' => now()->subMinutes($sequence->index)])
            ->create();
        Post::factory()->unpublished()->create();

        $this->getJson(route('api.v1.posts.index', ['per_page' => 2, 'page' => 2]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $posts[2]->id)
            ->assertJsonPath('data.1.id', $posts[3]->id)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonStructure(['links' => ['first', 'last', 'prev', 'next']]);
    }

    public function test_it_defaults_to_ten_posts_per_page(): void
    {
        Post::factory()->count(12)->create();

        $this->getJson(route('api.v1.posts.index'))
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_it_returns_an_empty_page_past_the_end(): void
    {
        Post::factory()->count(2)->create();

        $this->getJson(route('api.v1.posts.index', ['page' => 5]))
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_it_validates_pagination_parameters(): void
    {
        $this->getJson(route('api.v1.posts.index', ['page' => 0, 'per_page' => 51]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page', 'per_page']);

        $this->getJson(route('api.v1.posts.index', ['per_page' => 'abc']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_saving_a_post_clears_every_cached_page(): void
    {
        Post::factory()
            ->count(3)
            ->sequence(fn ($sequence) => ['created_at' => now()->subMinutes($sequence->index + 1)])
            ->create();

        $this->getJson(route('api.v1.posts.index', ['per_page' => 2, 'page' => 1]))->assertJsonPath('meta.total', 3);
        $this->getJson(route('api.v1.posts.index', ['per_page' => 2, 'page' => 2]))->assertJsonCount(1, 'data');

        Post::factory()->create(['created_at' => now()]);

        $this->getJson(route('api.v1.posts.index', ['per_page' => 2, 'page' => 1]))->assertJsonPath('meta.total', 4);
        $this->getJson(route('api.v1.posts.index', ['per_page' => 2, 'page' => 2]))->assertJsonCount(2, 'data');
    }
}
