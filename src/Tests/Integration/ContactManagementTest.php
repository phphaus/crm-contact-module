<?php

namespace Example\CrmContactModule\Tests\Integration;

use Example\CrmContactModule\Entities\Contact;
use Example\CrmContactModule\Services\AuditService;
use Example\CrmContactModule\Services\CallService;
use Example\CrmContactModule\Services\ContactService;
use Example\CrmContactModule\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ContactManagementTest extends TestCase
{
    private ContactService $service;
    private AuditService $auditService;
    private CallService $callService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ContactService::class);
        $this->auditService = $this->app->make(AuditService::class);
        $this->callService = $this->app->make(CallService::class);

        // Set up test configuration
        Config::set('crm.contacts.limits.phones', 2);
        Config::set('crm.contacts.limits.emails', 2);
    }

    public function test_full_contact_lifecycle(): void
    {
        // Create contact
        $contact = $this->service->createContact([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [
                ['number' => '+61412345678']
            ],
            'emails' => [
                ['email' => 'john@example.com']
            ]
        ]);

        $this->assertNotNull($contact['id']);

        // Verify audit log
        $auditLog = $this->auditService->getEntityAuditLog('contact', $contact['id']);
        $this->assertCount(1, $auditLog);
        $this->assertEquals('contact_created', $auditLog[0]['action']);

        // Update contact
        $updated = $this->service->updateContact($contact['id'], [
            'first_name' => 'Jane',
            'phones' => [
                ['number' => '+61412345679']
            ]
        ]);

        $this->assertEquals('Jane', $updated['first_name']);

        // Record a call
        $this->service->recordCall($contact['id'], 'initiated');

        // Verify call was recorded
        $contact = $this->service->getContact($contact['id']);
        $this->assertNotEmpty($contact['calls']);

        // Soft delete
        $this->service->deleteContact($contact['id']);

        // Verify contact is not found
        $this->expectException(\Example\CrmContactModule\Exceptions\ContactNotFoundException::class);
        $this->service->getContact($contact['id']);
    }

    public function test_tenant_isolation(): void
    {
        // Create contact for tenant 1
        $this->setTenantId(1);
        $contact1 = $this->service->createContact([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [['number' => '+61412345678']]
        ]);

        // Switch to tenant 2
        $this->setTenantId(2);

        // Create contact for tenant 2
        $contact2 = $this->service->createContact([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phones' => [['number' => '+61412345679']]
        ]);

        // Tenant 2 should not see tenant 1's contact
        $this->expectException(\Example\CrmContactModule\Exceptions\ContactNotFoundException::class);
        $this->service->getContact($contact1['id']);
    }

    public function test_concurrent_call_handling(): void
    {
        $contact = $this->service->createContact([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [['number' => '+61412345678']]
        ]);

        // Simulate concurrent call requests
        $promises = [];
        for ($i = 0; $i < 3; $i++) {
            $promises[] = async(function() use ($contact) {
                return $this->service->recordCall($contact['id'], 'initiated');
            });
        }

        $results = await($promises);

        // Verify all calls were recorded
        $contact = $this->service->getContact($contact['id']);
        $this->assertCount(3, $contact['calls']);
    }

    private function setTenantId(int $id): void
    {
        $this->app->bind('tenant.id', fn() => $id);
    }
}
