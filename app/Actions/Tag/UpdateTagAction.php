<?php

namespace App\Actions\Tag;

use App\Contracts\TagRepositoryInterface;
use App\Models\Tag;

class UpdateTagAction
{
    public function __construct(
        private TagRepositoryInterface $repository
    ) {}

    public function execute(int $id, array $data): Tag
    {
        return $this->repository->update($id, $data);
    }
}