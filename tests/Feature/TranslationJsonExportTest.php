<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslationJsonExportTest extends TestCase
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

    public function test_export_json_returns_200(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        
        Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export-json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertHeader('Content-Disposition');
    }

    public function test_export_json_returns_correct_structure(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        
        Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export-json');

        $response->assertStatus(200);
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('translations', $content);
        $this->assertArrayHasKey('updated_at', $content);
        $this->assertArrayHasKey('en', $content['translations']);
    }

    public function test_export_json_with_locale_filter(): void
    {
        $enLocale = Locale::create(['code' => 'en', 'name' => 'English']);
        $zhLocale = Locale::create(['code' => 'zh', 'name' => 'Chinese']);
        
        Translation::create(['locale_id' => $enLocale->id, 'key' => 'hello', 'content' => 'Hello']);
        Translation::create(['locale_id' => $zhLocale->id, 'key' => 'hello', 'content' => '你好']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export-json?locale_code=en');

        $response->assertStatus(200);
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('en', $content['translations']);
    }

    public function test_export_json_with_tags_filter(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        $tag = Tag::create(['name' => 'common', 'description' => 'Common translations']);
        
        $translation = Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);
        
        $translation->tags()->attach($tag->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export-json?tags=common');

        $response->assertStatus(200);
        
        $content = json_decode($response->getContent(), true);
        $this->assertNotEmpty($content['translations']);
    }

    public function test_export_json_returns_no_tags_for_untagged(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        
        Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export-json');

        $response->assertStatus(200);
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('no-tags', $content['translations']['en']);
    }

    public function test_export_json_requires_authentication(): void
    {
        $response = $this->getJson('/api/translations/export-json');
        $response->assertStatus(401);
    }

    public function test_export_json_performance_is_under_500ms(): void
    {
        Cache::flush();
        
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        
        for ($i = 0; $i < 100; $i++) {
            Translation::create([
                'locale_id' => $locale->id,
                'key' => 'key_' . $i,
                'content' => 'Content ' . $i,
            ]);
        }

        $startTime = microtime(true);
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export-json');
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        $this->assertLessThan(500, $duration, "Export took {$duration}ms, expected under 500ms");
    }

    public function test_export_json_cache_works(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        
        Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $startTime = microtime(true);
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export-json');
        $firstRun = (microtime(true) - $startTime) * 1000;

        $startTime = microtime(true);
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/translations/export-json');
        $secondRun = (microtime(true) - $startTime) * 1000;

        $this->assertLessThan($firstRun, $secondRun, "Cached request should be faster");
    }
}
