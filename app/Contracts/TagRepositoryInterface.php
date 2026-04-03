<?php

namespace App\Contracts;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TagRepositoryInterface
{
    public function getAll(): Collection;
    public function paginate(int $perPage = 20, int $page = 1): LengthAwarePaginator;
    public function getById(int $id): ?Tag;
    public function getByName(string $name): ?Tag;
    public function create(array $data): Tag;
    public function update(int $id, array $data): Tag;
    public function delete(int $id): bool;
}