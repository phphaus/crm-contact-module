<?php

namespace Example\CrmExample\Providers;

use Illuminate\Support\ServiceProvider;

class CrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/crm.php', 'crm'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        $this->publishes([
            __DIR__ . '/../Config/crm.php' => config_path('crm.php'),
        ], 'crm-config');
        
        // ... rest of boot method
    }
} 