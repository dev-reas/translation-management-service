<?php

namespace App\Http\Controllers\Api;

use App\Actions\Translation\CreateTranslationAction;
use App\Actions\Translation\UpdateTranslationAction;
use App\Actions\Translation\DeleteTranslationAction;
use App\Actions\Translation\SearchTranslationsAction;
use App\Http\Requests\Translation\CreateTranslationRequest;
use App\Http\Requests\Translation\UpdateTranslationRequest;
use App\Http\Resources\TranslationResource;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TranslationController
{
    public function index(Request $request, SearchTranslationsAction $action): JsonResponse
    {
        $filters = $request->only(['key', 'content', 'locale_id', 'locale_code', 'tag']);

        $perPage = min((int)$request->input('per_page', 20), 100);
        $page = (int)$request->input('page', 1);

        $translations = $action->execute($filters, $perPage, $page);

        return ResponseService::paginated($translations, TranslationResource::class);
    }

    public function store(CreateTranslationRequest $request, CreateTranslationAction $action): JsonResponse
    {
        $data = $request->validated();
        $translation = $action->execute($data);

        return ResponseService::created(new TranslationResource($translation));
    }

    public function show(int $id, SearchTranslationsAction $action): JsonResponse
    {
        $translations = $action->execute(['id' => $id], 1, 1);

        if ($translations->isEmpty()) {
            return ResponseService::notFound('Translation not found');
        }

        return ResponseService::success(new TranslationResource($translations->first()));
    }

    public function update(UpdateTranslationRequest $request, int $id, UpdateTranslationAction $action): JsonResponse
    {
        try {
            $data = $request->validated();
            $translation = $action->execute($id, $data);

            return ResponseService::updated(new TranslationResource($translation));
        }
        catch (ModelNotFoundException $e) {
            return ResponseService::notFound('Translation not found');
        }
    }

    public function destroy(int $id, DeleteTranslationAction $action): JsonResponse
    {
        try {
            $deleted = $action->execute($id);

            if (!$deleted) {
                return ResponseService::notFound('Translation not found');
            }

            return ResponseService::deleted('Translation deleted successfully');
        }
        catch (ModelNotFoundException $e) {
            return ResponseService::notFound('Translation not found');
        }
    }
    public function exportJson(Request $request)
    {
        $localeCode = $request->input('locale_code');
        $cacheKey = "translations_export_" . ($localeCode ?? 'all');
        
        $filename = $localeCode ? "translations_{$localeCode}.json" : 'translations_all.json';
        
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function () use ($localeCode, $cacheKey) {
            $cachedData = Cache::get($cacheKey);
            
            if ($cachedData !== null) {
                echo json_encode($cachedData, JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if ($localeCode) {
                $rows = DB::table('translations as t')
                    ->join('locales as l', 't.locale_id', '=', 'l.id')
                    ->where('l.code', $localeCode)
                    ->select('t.key', 't.content')
                    ->orderBy('t.id')
                    ->get();
                
                $translations = [];
                foreach ($rows as $row) {
                    $translations[$row->key] = $row->content;
                }
            } else {
                $rows = DB::table('translations as t')
                    ->join('locales as l', 't.locale_id', '=', 'l.id')
                    ->select('l.code as locale', 't.key', 't.content')
                    ->orderBy('l.code')
                    ->orderBy('t.id')
                    ->get();
                
                $translations = [];
                foreach ($rows as $row) {
                    if (!isset($translations[$row->locale])) {
                        $translations[$row->locale] = [];
                    }
                    $translations[$row->locale][$row->key] = $row->content;
                }
            }

            $data = [
                'translations' => $translations,
                'updated_at' => now()->toIso8601String(),
            ];

            Cache::put($cacheKey, $data, now()->addMinutes(5));
            
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        };

        return response()->stream($callback, 200, $headers);
    }
}