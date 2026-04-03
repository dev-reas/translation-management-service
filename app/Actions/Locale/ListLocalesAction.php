<?php

namespace App\Actions\Locale;

use App\Contracts\LocaleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListLocalesAction
{
    public function __construct(
        private LocaleRepositoryInterface $repository
    ) {}

    public function execute(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $page);
    }
}