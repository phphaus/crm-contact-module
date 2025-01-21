<?php

namespace Tests\Feature\Example\CrmExample\Http\Controllers\Api;

use Example\CrmExample\CrmServiceProvider;
use Example\CrmExample\Entities\Contact;
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

    protected function defineEnvironment($app): void
    {
        // Set up any environment configuration
        $app['config']->set('crm.multi_tenant.enabled', true);
        $app['config']->set('crm.contacts.limits.phones', 10);
        $app['config']->set('crm.contacts.limits.emails', 10);
        
        // Use SQLite for quick tests, but allow PostgreSQL override via env
        $app['config']->set('database.default', 'testing');
        
        if (env('TEST_DB_CONNECTION') === 'pgsql') {
            $app['config']->set('database.connections.testing', [
                'driver' => 'pgsql',
                'host' => env('TEST_DB_HOST', '127.0.0.1'),
                'port' => env('TEST_DB_PORT', '5432'),
                'database' => env('TEST_DB_DATABASE', 'crm_test'),
                'username' => env('TEST_DB_USERNAME', 'postgres'),
                'password' => env('TEST_DB_PASSWORD', ''),
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ]);
        } else {
            $app['config']->set('database.connections.testing', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        }
    }

    protected function defineDatabaseMigrations(): void
    {
        // Run package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
    }

    private function getJwtToken(int $tenantId = 1, int $userId = 1): string
    {
        $jwtParser = $this->app->make(JwtParser::class);
        return $jwtParser->encode([
            'tenant_id' => $tenantId,
            'user_id' => $userId
        ]);
    }

    public function test_list_contacts_with_pagination_and_filters(): void
    {
        // Arrange
        $token = $this->getJwtToken();
        
        // Create test contacts with phones and emails
        Contact::factory()
            ->count(20)
            ->hasPhones(1)
            ->hasEmails(1)
            ->create();

        // Create a contact with specific phone and email for filtering
        Contact::factory()
            ->hasPhones(1, ['number' => '+61412345678'])
            ->hasEmails(1, ['email' => 'test@example.com'])
            ->create();

        // Act & Assert - Test pagination
        $response = $this->withToken($token)
            ->getJson('/api/v1/contacts?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ],
                'links'
            ])
            ->assertJsonCount(10, 'data');

        // Test phone filter
        $response = $this->withToken($token)
            ->getJson('/api/v1/contacts?phone=+61412345678');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Test email filter
        $response = $this->withToken($token)
            ->getJson('/api/v1/contacts?email=test@example.com');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Test unauthorized access
        $this->getJson('/api/v1/contacts')
            ->assertStatus(401);
    }
} 