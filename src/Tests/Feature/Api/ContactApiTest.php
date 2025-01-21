<?php

namespace Example\CrmExample\Tests\Feature\Api;

use Example\CrmExample\Tests\TestCase;
use Example\CrmExample\Tests\Factories\ContactFactory;
use Illuminate\Support\Facades\Config;

class ContactApiTest extends TestCase
{
    private ContactFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->factory = new ContactFactory();
        
        // Mock JWT middleware for testing
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->generateTestToken(),
        ]);
    }

    public function test_can_create_contact(): void
    {
        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [
                ['number' => '+61412345678']
            ],
            'emails' => [
                ['email' => 'john@example.com']
            ]
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'first_name',
                'last_name',
                'phones' => [['number']],
                'emails' => [['email']]
            ]);
    }

    public function test_enforces_phone_number_limits(): void
    {
        Config::set('crm.contacts.limits.phones', 2);

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [
                ['number' => '+61412345678'],
                ['number' => '+61412345679'],
                ['number' => '+61412345670']
            ]
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'Maximum of 2 phone numbers allowed'
            ]);
    }

    public function test_validates_phone_number_format(): void
    {
        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [
                ['number' => '+1234567890'] // Invalid AU/NZ number
            ]
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'Invalid phone number format'
            ]);
    }

    public function test_can_update_contact(): void
    {
        $contact = $this->factory->create();

        $response = $this->putJson("/api/v1/contacts/{$contact['id']}", [
            'first_name' => 'Jane',
            'emails' => [
                ['email' => 'jane@example.com']
            ]
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'first_name' => 'Jane',
                'emails' => [
                    ['email' => 'jane@example.com']
                ]
            ]);
    }

    public function test_can_delete_contact(): void
    {
        $contact = $this->factory->create();

        $response = $this->deleteJson("/api/v1/contacts/{$contact['id']}");
        $response->assertNoContent();

        // Verify it's soft deleted
        $this->getJson("/api/v1/contacts/{$contact['id']}")
            ->assertNotFound();
    }

    public function test_can_find_by_phone(): void
    {
        $contact = $this->factory->create([
            'phones' => [['number' => '+61412345678']]
        ]);

        $response = $this->getJson('/api/v1/contacts/by-phone/412345678');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $contact['id']
            ]);
    }

    public function test_can_find_by_email_domain(): void
    {
        $contact = $this->factory->create([
            'emails' => [['email' => 'test@example.com']]
        ]);

        $response = $this->getJson('/api/v1/contacts/by-email-domain/example.com');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $contact['id']
            ]);
    }

    public function test_enforces_tenant_isolation(): void
    {
        // Create contact for tenant 1
        $contact = $this->factory->create();

        // Switch to tenant 2
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->generateTestToken(2)
        ]);

        // Should not be able to access contact from tenant 1
        $this->getJson("/api/v1/contacts/{$contact['id']}")
            ->assertNotFound();
    }

    public function test_can_record_call(): void
    {
        $contact = $this->factory->create([
            'phones' => [['number' => '+61412345678']]
        ]);

        $response = $this->postJson("/api/v1/contacts/{$contact['id']}/call");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'status'
            ]);

        // Verify call was recorded
        $this->getJson("/api/v1/contacts/{$contact['id']}")
            ->assertJsonStructure([
                'calls' => [[
                    'status',
                    'timestamp',
                    'phone_number'
                ]]
            ]);
    }

    public function test_cannot_record_call_without_phone(): void
    {
        $contact = $this->factory->create([
            'phones' => [] // No phones
        ]);

        $this->postJson("/api/v1/contacts/{$contact['id']}/call")
            ->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'Contact has no phone numbers'
            ]);
    }

    private function generateTestToken(int $tenantId = 1): string
    {
        return base64_encode(json_encode([
            'tenant_id' => $tenantId,
            'user_id' => 1
        ]));
    }
} 