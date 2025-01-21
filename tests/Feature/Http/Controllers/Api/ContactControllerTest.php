<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    private function getJwtToken(int $tenantId = 1, int $userId = 1): string
    {
        // Mock JWT token generation - implementation depends on your JWT setup
        return 'test.jwt.token';
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