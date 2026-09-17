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

        $this->getJson(route('api.v1.posts.index'))->assertJsonCount(1, 'data');
        $this->assertTrue(Cache::has(Post::PUBLISHED_CACHE_KEY));
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
}
