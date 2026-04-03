<?php

namespace App\Actions\Locale;

use App\Contracts\LocaleRepositoryInterface;

class DeleteLocaleAction
{
    public function __construct(
        private LocaleRepositoryInterface $repository
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}