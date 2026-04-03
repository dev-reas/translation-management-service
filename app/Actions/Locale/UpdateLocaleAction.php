<?php

namespace App\Actions\Locale;

use App\Contracts\LocaleRepositoryInterface;
use App\Models\Locale;

class UpdateLocaleAction
{
    public function __construct(
        private LocaleRepositoryInterface $repository
    ) {}

    public function execute(int $id, array $data): Locale
    {
        return $this->repository->update($id, $data);
    }
}