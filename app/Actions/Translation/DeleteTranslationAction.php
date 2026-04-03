<?php

namespace App\Actions\Translation;

use App\Contracts\TranslationRepositoryInterface;

class DeleteTranslationAction
{
    public function __construct(
        private TranslationRepositoryInterface $repository
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}