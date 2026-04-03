<?php

namespace App\Contracts;

use App\Models\Locale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface LocaleRepositoryInterface
{
    public function getAll(): Collection;
    public function paginate(int $perPage = 20, int $page = 1): LengthAwarePaginator;
    public function getById(int $id): ?Locale;
    public function getByCode(string $code): ?Locale;
    public function create(array $data): Locale;
    public function update(int $id, array $data): Locale;
    public function delete(int $id): bool;
}