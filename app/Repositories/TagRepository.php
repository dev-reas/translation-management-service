<?php

namespace App\Repositories;

use App\Contracts\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TagRepository implements TagRepositoryInterface
{
    public function getAll(): Collection
    {
        return Tag::all();
    }

    public function paginate(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return Tag::orderBy('id')->paginate($perPage, ['*'], 'page', $page);
    }

    public function getById(int $id): ?Tag
    {
        return Tag::find($id);
    }

    public function getByName(string $name): ?Tag
    {
        return Tag::where('name', $name)->first();
    }

    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    public function update(int $id, array $data): Tag
    {
        $tag = Tag::findOrFail($id);
        $tag->update($data);
        return $tag->fresh();
    }

    public function delete(int $id): bool
    {
        return Tag::findOrFail($id)->delete();
    }
}