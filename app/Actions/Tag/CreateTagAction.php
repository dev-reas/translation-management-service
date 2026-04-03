<?php

namespace App\Actions\Tag;

use App\Contracts\TagRepositoryInterface;
use App\Models\Tag;

class CreateTagAction
{
    public function __construct(
        private TagRepositoryInterface $repository
    ) {}

    public function execute(array $data): Tag
    {
        return $this->repository->create($data);
    }
}