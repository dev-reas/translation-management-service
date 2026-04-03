<?php

namespace App\Actions\Translation;

use App\Contracts\TranslationRepositoryInterface;
use App\Models\Translation;

class CreateTranslationAction
{
    public function __construct(
        private TranslationRepositoryInterface $repository
    ) {}

    public function execute(array $data): Translation
    {
        return $this->repository->create($data);
    }
}