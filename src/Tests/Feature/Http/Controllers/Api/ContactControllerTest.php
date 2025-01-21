<?php

namespace Example\CrmContactModule\Tests\Feature\Http\Controllers\Api;

use Example\CrmContactModule\Providers\CrmContactModuleServiceProvider;
use Example\CrmContactModule\Services\Auth\JwtParser;
use Example\CrmContactModule\Repositories\DoctrineContactRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    private DoctrineContactRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(DoctrineContactRepository::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            CrmContactModuleServiceProvider::class,
        ];
    }

    public function test_list_contacts_with_pagination_and_filters(): void
    {
        // Arrange
        $token = $this->getJwtToken();
        
        // Create test contacts using repository
        for ($i = 0; $i < 20; $i++) {
            $this->repository->create([
                'first_name' => "Test $i",
                'last_name' => 'User',
                'phones' => [['number' => '+61400000' . sprintf('%03d', $i)]],
                'emails' => [['email' => "test$i@example.com"]],
                'tenant_id' => 1
            ]);
        }

        // Create a contact with specific phone and email for filtering
        $this->repository->create([
            'first_name' => 'Filter',
            'last_name' => 'Test',
            'phones' => [['number' => '+61412345678']],
            'emails' => [['email' => 'test@example.com']],
            'tenant_id' => 1
        ]);

        // Test pagination
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

        // ... rest of the test
    }
} 