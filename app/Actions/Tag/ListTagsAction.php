<?php

namespace App\Actions\Tag;

use App\Contracts\TagRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListTagsAction
{
    public function __construct(
        private TagRepositoryInterface $repository
    ) {}

    public function execute(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $page);
    }
}