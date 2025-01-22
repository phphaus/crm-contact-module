<?php

namespace Example\CrmContactModule;

use Doctrine\ORM\EntityManagerInterface;
use Example\CrmContactModule\Contracts\ContactRepositoryInterface;
use Example\CrmContactModule\Contracts\ContactServiceInterface;
use Example\CrmContactModule\Http\Middleware\JwtTenantMiddleware;
use Example\CrmContactModule\Repositories\DoctrineContactRepository;
use Example\CrmContactModule\Services\AuditService;
use Example\CrmContactModule\Services\CallService;
use Example\CrmContactModule\Services\ContactService;
use Illuminate\Support\ServiceProvider;

class CrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/crm.php', 'crm');

        $this->app->bind(ContactRepositoryInterface::class, DoctrineContactRepository::class);
        $this->app->bind(ContactServiceInterface::class, ContactService::class);

        $this->app->singleton(AuditService::class);
        $this->app->singleton(CallService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/crm.php' => config_path('crm.php'),
            ], 'crm-config');

            $this->publishes([
                __DIR__ . '/../database/Migrations' => database_path('Migrations'),
            ], 'crm-Migrations');
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
