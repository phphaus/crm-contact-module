<?php

namespace Example\CrmContactModule\Tests\Unit\Services;

use Doctrine\DBAL\Connection;
use Example\CrmContactModule\Contracts\ContactRepositoryInterface;
use Example\CrmContactModule\Exceptions\ContactNotFoundException;
use Example\CrmContactModule\Services\AuditService;
use Example\CrmContactModule\Services\ContactService;
use Example\CrmContactModule\Services\Validators\ContactValidator;
use Mockery;
use PHPUnit\Framework\TestCase;

class ContactServiceTest extends TestCase
{
    private ContactService $service;
    private ContactRepositoryInterface $repository;
    private Connection $connection;
    private ContactValidator $validator;
    private AuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ContactRepositoryInterface::class);
        $this->connection = Mockery::mock(Connection::class);
        $this->validator = new ContactValidator();
        $this->auditService = Mockery::mock(AuditService::class);

        $this->service = new ContactService(
            $this->repository,
            $this->connection,
            $this->validator,
            $this->auditService
        );
    }

    public function test_creates_contact(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [['number' => '+61412345678']],
            'emails' => [['email' => 'john@example.com']]
        ];

        $this->connection->shouldReceive('beginTransaction')->once();
        $this->connection->shouldReceive('commit')->once();

        $this->repository->shouldReceive('create')
            ->with($data)
            ->andReturn(['id' => 1] + $data);

        $this->auditService->shouldReceive('logAction')
            ->with('contact_created', 'contact', 1, $data)
            ->once();

        $result = $this->service->createContact($data);
        $this->assertEquals(1, $result['id']);
    }

    public function test_updates_contact(): void
    {
        $id = 1;
        $data = ['first_name' => 'Jane'];
        $original = [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];

        $this->connection->shouldReceive('beginTransaction')->once();
        $this->connection->shouldReceive('commit')->once();

        $this->repository->shouldReceive('findById')
            ->with($id)
            ->andReturn($original);

        $this->repository->shouldReceive('update')
            ->with($id, $data)
            ->andReturn(['id' => 1] + $data);

        $this->auditService->shouldReceive('logAction')
            ->with('contact_updated', 'contact', $id, [
                'original' => $original,
                'changes' => $data
            ])
            ->once();

        $result = $this->service->updateContact($id, $data);
        $this->assertEquals('Jane', $result['first_name']);
    }

    public function test_throws_not_found_exception(): void
    {
        $this->repository->shouldReceive('findById')
            ->with(999)
            ->andReturnNull();

        $this->expectException(ContactNotFoundException::class);
        $this->service->getContact(999);
    }

    public function test_records_call(): void
    {
        $id = 1;
        $contact = [
            'id' => 1,
            'phones' => [['number' => '+61412345678']]
        ];

        $this->connection->shouldReceive('beginTransaction')->once();
        $this->connection->shouldReceive('commit')->once();

        $this->repository->shouldReceive('findById')
            ->with($id)
            ->andReturn($contact);

        $this->repository->shouldReceive('recordCall')
            ->with($id, 'initiated')
            ->once();

        $this->auditService->shouldReceive('logAction')
            ->with('call_completed', 'contact', $id, Mockery::any())
            ->once();

        $this->service->recordCall($id, 'initiated');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
