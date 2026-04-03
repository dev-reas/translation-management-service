<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleApiTest extends TestCase
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

    public function test_can_list_locales(): void
    {
        Locale::create(['code' => 'en', 'name' => 'English']);
        Locale::create(['code' => 'zh', 'name' => 'Chinese']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/locales');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    public function test_can_create_locale(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/locales', [
                'code' => 'fr',
                'name' => 'French',
            ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['code' => 'fr']);
    }

    public function test_can_show_locale(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/locales/' . $locale->id);

        $response->assertStatus(200);
        $response->assertJsonFragment(['code' => 'en']);
    }

    public function test_can_update_locale(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/locales/' . $locale->id, [
                'name' => 'English US',
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'English US']);
    }

    public function test_can_delete_locale(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/locales/' . $locale->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('locales', ['id' => $locale->id]);
    }

    public function test_locale_requires_authentication(): void
    {
        $response = $this->getJson('/api/locales');
        $response->assertStatus(401);
    }
}
