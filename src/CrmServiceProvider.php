<?php

namespace Example\CrmExample;

use Doctrine\ORM\EntityManagerInterface;
use Example\CrmExample\Contracts\ContactRepositoryInterface;
use Example\CrmExample\Contracts\ContactServiceInterface;
use Example\CrmExample\Http\Middleware\JwtTenantMiddleware;
use Example\CrmExample\Repositories\DoctrineContactRepository;
use Example\CrmExample\Services\AuditService;
use Example\CrmExample\Services\CallService;
use Example\CrmExample\Services\ContactService;
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
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'crm-migrations');
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
} 