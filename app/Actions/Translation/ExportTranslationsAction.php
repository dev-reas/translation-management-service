<?php

namespace App\Actions\Translation;

use App\Contracts\TranslationRepositoryInterface;

class ExportTranslationsAction
{
    public function __construct(
        private TranslationRepositoryInterface $repository
    ) {}

    public function execute(array $filters): array
    {
        return $this->repository->export($filters);
    }
}