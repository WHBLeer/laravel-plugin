<?php

namespace Sanlilin\LaravelPlugin\Providers;

use Carbon\Laravel\ServiceProvider;
use Sanlilin\LaravelPlugin\Contracts\RepositoryInterface;
use Sanlilin\LaravelPlugin\Support\Repositories\FileRepository;

class ContractsServiceProvider extends ServiceProvider
{
    /**
     * Register some binding.
     */
    public function register()
    {
        $this->app->bind(RepositoryInterface::class, FileRepository::class);
    }
}
