<?php

namespace Tests\Unit;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_has_many_translations(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        
        Translation::create(['locale_id' => $locale->id, 'key' => 'hello', 'content' => 'Hello']);
        Translation::create(['locale_id' => $locale->id, 'key' => 'bye', 'content' => 'Bye']);

        $this->assertCount(2, $locale->translations);
    }

    public function test_locale_code_is_unique(): void
    {
        Locale::create(['code' => 'en', 'name' => 'English']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Locale::create(['code' => 'en', 'name' => 'Another English']);
    }
}
