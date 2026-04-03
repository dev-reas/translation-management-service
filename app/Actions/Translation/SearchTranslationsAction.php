<?php

namespace App\Actions\Translation;

use App\Contracts\TranslationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchTranslationsAction
{
    public function __construct(
        private TranslationRepositoryInterface $repository
    ) {}

    public function execute(array $filters, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage, $page);
    }
}