<?php

namespace App\Contracts;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TranslationRepositoryInterface
{
    public function getAll(array $filters): Collection;
    public function paginate(array $filters, int $perPage = 20, int $page = 1): LengthAwarePaginator;
    public function getById(int $id): ?Translation;
    public function create(array $data): Translation;
    public function update(int $id, array $data): Translation;
    public function delete(int $id): bool;
    public function export(array $filters): array;
}