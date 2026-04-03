<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationApiTest extends TestCase
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

    public function test_can_list_translations(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        Translation::create(['locale_id' => $locale->id, 'key' => 'hello', 'content' => 'Hello']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'key', 'content', 'locale_id', 'locale_code', 'tags']
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total']
        ]);
    }

    public function test_can_create_translation(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/translations', [
                'locale_id' => $locale->id,
                'key' => 'hello',
                'content' => 'Hello',
            ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'key' => 'hello',
            'content' => 'Hello',
        ]);
    }

    public function test_can_show_translation(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        $translation = Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/' . $translation->id);

        $response->assertStatus(200);
        $response->assertJsonFragment(['key' => 'hello']);
    }

    public function test_can_update_translation(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        $translation = Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/translations/' . $translation->id, [
                'content' => 'Hello World',
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['content' => 'Hello World']);
    }

    public function test_can_delete_translation(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        $translation = Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/translations/' . $translation->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }

    public function test_can_filter_translations_by_locale_code(): void
    {
        $enLocale = Locale::create(['code' => 'en', 'name' => 'English']);
        $zhLocale = Locale::create(['code' => 'zh', 'name' => 'Chinese']);
        
        Translation::create(['locale_id' => $enLocale->id, 'key' => 'hello', 'content' => 'Hello']);
        Translation::create(['locale_id' => $zhLocale->id, 'key' => 'hello', 'content' => '你好']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations?locale_code=en');

        $response->assertStatus(200);
        $translations = $response->json('data');
        $this->assertCount(1, $translations);
    }

    public function test_can_filter_translations_by_key(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        Translation::create(['locale_id' => $locale->id, 'key' => 'hello', 'content' => 'Hello']);
        Translation::create(['locale_id' => $locale->id, 'key' => 'goodbye', 'content' => 'Goodbye']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations?key=hello');

        $response->assertStatus(200);
        $translations = $response->json('data');
        $this->assertCount(1, $translations);
    }

    public function test_can_attach_tags_to_translation(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        $tag = Tag::create(['name' => 'common', 'description' => 'Common']);
        
        $translation = Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);
        
        $translation->tags()->attach($tag->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/' . $translation->id);

        $response->assertStatus(200);
        $tags = $response->json('data.tags');
        $this->assertCount(1, $tags);
    }

    public function test_translation_requires_authentication(): void
    {
        $response = $this->getJson('/api/translations');
        $response->assertStatus(401);
    }

    public function test_create_translation_validates_required_fields(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/translations', []);

        $response->assertStatus(422);
    }
}
