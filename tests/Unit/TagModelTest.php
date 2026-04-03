<?php

namespace Tests\Unit;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_has_many_translations(): void
    {
        $locale = Locale::create(['code' => 'en', 'name' => 'English']);
        $tag = Tag::create(['name' => 'common', 'description' => 'Common']);
        
        $translation1 = Translation::create(['locale_id' => $locale->id, 'key' => 'hello', 'content' => 'Hello']);
        $translation2 = Translation::create(['locale_id' => $locale->id, 'key' => 'bye', 'content' => 'Bye']);
        
        $translation1->tags()->attach($tag->id);
        $translation2->tags()->attach($tag->id);

        $this->assertCount(2, $tag->translations);
    }

    public function test_tag_name_is_unique(): void
    {
        Tag::create(['name' => 'common', 'description' => 'Common']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Tag::create(['name' => 'common', 'description' => 'Another Common']);
    }
}
