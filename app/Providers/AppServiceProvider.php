<?php

namespace App\Providers;

use App\Contracts\LocaleRepositoryInterface;
use App\Contracts\TagRepositoryInterface;
use App\Contracts\TranslationRepositoryInterface;
use App\Repositories\LocaleRepository;
use App\Repositories\TagRepository;
use App\Repositories\TranslationRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LocaleRepositoryInterface::class, LocaleRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(TranslationRepositoryInterface::class, TranslationRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
