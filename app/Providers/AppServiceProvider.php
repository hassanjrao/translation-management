<?php

namespace App\Providers;

use App\Repositories\Contracts\TranslationRepositoryInterface;
use App\Repositories\TranslationRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TranslationRepositoryInterface::class, TranslationRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
