<?php

namespace App\Repositories;

use App\Contracts\LocaleRepositoryInterface;
use App\Models\Locale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LocaleRepository implements LocaleRepositoryInterface
{
    public function getAll(): Collection
    {
        return Locale::all();
    }

    public function paginate(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return Locale::orderBy('id')->paginate($perPage, ['*'], 'page', $page);
    }

    public function getById(int $id): ?Locale
    {
        return Locale::find($id);
    }

    public function getByCode(string $code): ?Locale
    {
        return Locale::where('code', $code)->first();
    }

    public function create(array $data): Locale
    {
        return Locale::create($data);
    }

    public function update(int $id, array $data): Locale
    {
        $locale = Locale::findOrFail($id);
        $locale->update($data);
        return $locale->fresh();
    }

    public function delete(int $id): bool
    {
        return Locale::findOrFail($id)->delete();
    }
}