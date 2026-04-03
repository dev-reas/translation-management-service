<?php

namespace App\Actions\Tag;

use App\Contracts\TagRepositoryInterface;

class DeleteTagAction
{
    public function __construct(
        private TagRepositoryInterface $repository
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}