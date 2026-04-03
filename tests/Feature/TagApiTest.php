<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_tags(): void
    {
        Tag::create(['name' => 'common', 'description' => 'Common translations']);
        Tag::create(['name' => 'menu', 'description' => 'Menu translations']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/tags');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    public function test_can_create_tag(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/tags', [
                'name' => 'footer',
                'description' => 'Footer translations',
            ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'footer']);
    }

    public function test_can_show_tag(): void
    {
        $tag = Tag::create(['name' => 'common', 'description' => 'Common']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/tags/' . $tag->id);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'common']);
    }

    public function test_can_update_tag(): void
    {
        $tag = Tag::create(['name' => 'common', 'description' => 'Common']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/tags/' . $tag->id, [
                'name' => 'common-updated',
                'description' => 'Updated description',
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'common-updated']);
    }

    public function test_can_delete_tag(): void
    {
        $tag = Tag::create(['name' => 'common', 'description' => 'Common']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/tags/' . $tag->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_tag_requires_authentication(): void
    {
        $response = $this->getJson('/api/tags');
        $response->assertStatus(401);
    }
}
