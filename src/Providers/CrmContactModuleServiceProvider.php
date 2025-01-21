<?php

namespace Example\CrmContactModule\Providers;

use Illuminate\Support\ServiceProvider;

class CrmContactModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/crm-contact.php', 'crm-contact'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        $this->publishes([
            __DIR__ . '/../Config/crm-contact.php' => config_path('crm-contact.php'),
        ], 'crm-contact-config');

        if ($this->app->runningInConsole()) {
            $this->loadFactoriesFrom(__DIR__ . '/../Database/Factories');
        }
    }
} 