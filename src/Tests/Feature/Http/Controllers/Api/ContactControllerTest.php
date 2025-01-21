<?php

namespace Example\CrmExample\Tests\Feature\Http\Controllers\Api;

use Example\CrmExample\Models\Contact;
use Example\CrmExample\Providers\CrmServiceProvider;
use Example\CrmExample\Services\Auth\JwtParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            CrmServiceProvider::class,
        ];
    }

    // ... rest of the test file
} 