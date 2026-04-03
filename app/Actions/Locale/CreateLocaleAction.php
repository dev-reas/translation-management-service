<?php

namespace App\Actions\Locale;

use App\Contracts\LocaleRepositoryInterface;
use App\Models\Locale;

class CreateLocaleAction
{
    public function __construct(
        private LocaleRepositoryInterface $repository
    ) {}

    public function execute(array $data): Locale
    {
        return $this->repository->create($data);
    }
}