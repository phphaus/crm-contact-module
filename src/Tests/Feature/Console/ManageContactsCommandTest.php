<?php

namespace Example\CrmContactModule\Tests\Feature\Console;

use Example\CrmContactModule\Contracts\ContactServiceInterface;
use Example\CrmContactModule\Tests\TestCase;
use Mockery;

class ManageContactsCommandTest extends TestCase
{
    private ContactServiceInterface $contactService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contactService = Mockery::mock(ContactServiceInterface::class);
        $this->app->instance(ContactServiceInterface::class, $this->contactService);
    }

    public function test_can_list_contacts(): void
    {
        $this->contactService->shouldReceive('getAllContacts')
            ->once()
            ->andReturn([
                [
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'phones' => [['number' => '+61412345678']],
                    'emails' => [['email' => 'john@example.com']]
                ]
            ]);

        $this->artisan('contacts:manage', ['operation' => 'list'])
            ->assertSuccessful()
            ->expectsTable(
                ['ID', 'First Name', 'Last Name', 'Phones', 'Emails'],
                [[1, 'John', 'Doe', '+61412345678', 'john@example.com']]
            );
    }

    public function test_can_create_contact(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [['number' => '+61412345678']],
            'emails' => [['email' => 'john@example.com']]
        ];

        $this->contactService->shouldReceive('createContact')
            ->once()
            ->with($data)
            ->andReturn(['id' => 1] + $data);

        $this->artisan('contacts:manage', [
            'operation' => 'create',
            '--first-name' => 'John',
            '--last-name' => 'Doe',
            '--phones' => ['+61412345678'],
            '--emails' => ['john@example.com']
        ])
        ->assertSuccessful()
        ->expectsOutput('Contact created with ID: 1');
    }

    public function test_validates_required_options(): void
    {
        $this->artisan('contacts:manage', ['operation' => 'create'])
            ->assertFailed()
            ->expectsOutput('Option --first-name is required');
    }
}
