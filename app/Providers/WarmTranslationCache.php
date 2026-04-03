<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class WarmTranslationCache extends ServiceProvider
{
    public function boot(): void
    {
        $this->warmTranslationCache();
    }

    private function warmTranslationCache(): void
    {
        $cacheKey = 'translations_export_all';
        
        if (!Cache::has($cacheKey)) {
            try {
                $translations = [];
                
                DB::table('translations as t')
                    ->join('locales as l', 't.locale_id', '=', 'l.id')
                    ->leftJoin('translation_tag as tt', 't.id', '=', 'tt.translation_id')
                    ->leftJoin('tags as tg', 'tt.tag_id', '=', 'tg.id')
                    ->select('t.key', 't.content', 'l.code as locale', 'tg.name as tag')
                    ->orderBy('l.code')
                    ->orderBy('t.id')
                    ->chunk(1000, function ($rows) use (&$translations) {
                        foreach ($rows as $row) {
                            $tagName = $row->tag ?? 'no-tags';
                            if (!isset($translations[$row->locale])) {
                                $translations[$row->locale] = [];
                            }
                            if (!isset($translations[$row->locale][$tagName])) {
                                $translations[$row->locale][$tagName] = [];
                            }
                            $translations[$row->locale][$tagName][$row->key] = $row->content;
                        }
                    });
                
                $data = [
                    'translations' => $translations,
                    'updated_at' => now()->toIso8601String(),
                ];
                
                Cache::put($cacheKey, $data, now()->addMinutes(5));
            } catch (\Exception $e) {
                // Silently fail - let the endpoint handle it
            }
        }
    }
}
