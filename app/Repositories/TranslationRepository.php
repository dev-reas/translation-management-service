<?php

namespace App\Repositories;

use App\Contracts\TranslationRepositoryInterface;
use App\Models\Translation;
use App\Models\Locale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TranslationRepository implements TranslationRepositoryInterface
{
    public function getAll(array $filters): Collection
    {
        $query = Translation::with(['locale', 'tags']);

        if (!empty($filters['key'])) {
            $query->where('key', 'like', '%' . $filters['key'] . '%');
        }

        if (!empty($filters['content'])) {
            $query->where('content', 'like', '%' . $filters['content'] . '%');
        }

        if (!empty($filters['locale_id'])) {
            $query->where('locale_id', $filters['locale_id']);
        }

        if (!empty($filters['locale_code'])) {
            $query->whereHas('locale', function ($q) use ($filters) {
                $q->where('code', $filters['locale_code']);
            });
        }

        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('name', $filters['tag']);
            });
        }

        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        return $query->orderBy('key')->get();
    }

    public function paginate(array $filters, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = Translation::select(['translations.id', 'translations.locale_id', 'translations.key', 'translations.content', 'translations.created_at', 'translations.updated_at'])
            ->with(['locale:id,code,name', 'tags:id,name']);

        if (!empty($filters['key'])) {
            $query->where('key', 'like', '%' . $filters['key'] . '%');
        }

        if (!empty($filters['content'])) {
            $query->where('content', 'like', '%' . $filters['content'] . '%');
        }

        if (!empty($filters['locale_id'])) {
            $query->where('locale_id', $filters['locale_id']);
        }

        if (!empty($filters['locale_code'])) {
            $query->whereHas('locale', function ($q) use ($filters) {
                $q->where('code', $filters['locale_code']);
            });
        }

        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('name', $filters['tag']);
            });
        }

        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        return $query->orderBy('id')->paginate($perPage, ['*'], 'page', $page);
    }

    public function getById(int $id): ?Translation
    {
        return Translation::with(['locale', 'tags'])->find($id);
    }

    public function create(array $data): Translation
    {
        $translation = Translation::create([
            'locale_id' => $data['locale_id'],
            'key' => $data['key'],
            'content' => $data['content'],
        ]);

        if (!empty($data['tags'])) {
            $translation->tags()->sync($data['tags']);
        }

        return $translation;
    }

    public function update(int $id, array $data): Translation
    {
        $translation = Translation::findOrFail($id);
        $translation->update([
            'locale_id' => $data['locale_id'] ?? $translation->locale_id,
            'key' => $data['key'] ?? $translation->key,
            'content' => $data['content'] ?? $translation->content,
        ]);

        if (isset($data['tags'])) {
            $translation->tags()->sync($data['tags']);
        }

        return $translation->fresh(['locale', 'tags']);
    }

    public function delete(int $id): bool
    {
        return Translation::findOrFail($id)->delete();
    }

    public function export(array $filters): array
    {
        $query = Translation::select('translations.key', 'translations.content');

        if (!empty($filters['locale_code'])) {
            $query->whereHas('locale', function ($q) use ($filters) {
                $q->where('code', $filters['locale_code']);
            });
        }

        if (!empty($filters['tags'])) {
            $tagNames = explode(',', $filters['tags']);
            $query->whereHas('tags', function ($q) use ($tagNames) {
                $q->whereIn('name', $tagNames);
            });
        }

        $result = [
            'locale' => $filters['locale_code'] ?? null,
            'exported_at' => now()->toIso8601String(),
            'translations' => [],
        ];

        $query->chunk(10000, function ($translations) use (&$result) {
            foreach ($translations as $translation) {
                $result['translations'][$translation->key] = $translation->content;
            }
        });

        return $result;
    }
}