<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationCsvExportTest extends TestCase
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

    public function test_export_csv_returns_200(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        
        Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/translations/export-csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition');
    }

    public function test_export_csv_returns_valid_csv(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        
        Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/translations/export-csv');

        $content = $response->getContent();
        $lines = explode("\n", trim($content));
        
        $this->assertCount(2, $lines);
        $this->assertStringContainsString('key,content,locale,tags', $lines[0]);
        $this->assertStringContainsString('hello,Hello,en,', $lines[1]);
    }

    public function test_export_csv_with_locale_filter(): void
    {
        $enLocale = Locale::create(['code' => 'en', 'name' => 'English']);
        $zhLocale = Locale::create(['code' => 'zh', 'name' => 'Chinese']);
        
        Translation::create(['locale_id' => $enLocale->id, 'key' => 'hello', 'content' => 'Hello']);
        Translation::create(['locale_id' => $zhLocale->id, 'key' => 'hello', 'content' => '你好']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/translations/export-csv?locale_code=en');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        $this->assertStringContainsString('en', $content);
        $this->assertStringNotContainsString('zh', $content);
    }

    public function test_export_csv_requires_authentication(): void
    {
        $response = $this->get('/api/translations/export-csv');
        $response->assertStatus(401);
    }
}
