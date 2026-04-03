<?php

namespace App\Actions\Translation;

use App\Contracts\TranslationRepositoryInterface;
use App\Models\Translation;

class UpdateTranslationAction
{
    public function __construct(
        private TranslationRepositoryInterface $repository
    ) {}

    public function execute(int $id, array $data): Translation
    {
        return $this->repository->update($id, $data);
    }
}