<?php

namespace Tests\Unit;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_belongs_to_locale(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        $translation = Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $this->assertInstanceOf(Locale::class, $translation->locale);
        $this->assertEquals('en', $translation->locale->code);
    }

    public function test_translation_can_have_tags(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        $tag = Tag::create(['name' => 'common', 'description' => 'Common']);
        
        $translation = Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);
        
        $translation->tags()->attach($tag->id);

        $this->assertCount(1, $translation->tags);
    }

    public function test_translation_unique_constraint(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        
        Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Translation::create([
            'locale_id' => $locale->id,
            'key' => 'hello',
            'content' => 'Hello World',
        ]);
    }
}
