<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('App\Repository\UserRepositoryInterface', 'App\Repository\UserRepository');
        $this->app->bind('App\Repository\GroupRepositoryInterface', 'App\Repository\GroupRepository');
        $this->app->bind('App\Repository\FileRepositoryInterface', 'App\Repository\FileRepository');


    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
