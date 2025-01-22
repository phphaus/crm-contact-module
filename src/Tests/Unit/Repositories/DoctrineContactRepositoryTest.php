<?php

namespace Example\CrmContactModule\Tests\Unit\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Example\CrmContactModule\Entities\Contact;
use Example\CrmContactModule\Repositories\DoctrineContactRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

class DoctrineContactRepositoryTest extends TestCase
{
    private DoctrineContactRepository $repository;
    private EntityManagerInterface $em;
    private EntityRepository $doctrineRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Mockery::mock(EntityManagerInterface::class);
        $this->doctrineRepository = Mockery::mock(EntityRepository::class);

        $this->em->shouldReceive('getRepository')
            ->with(Contact::class)
            ->andReturn($this->doctrineRepository);

        $this->repository = new DoctrineContactRepository($this->em);
    }

    public function test_finds_contact_by_id(): void
    {
        $contact = Mockery::mock(Contact::class);
        $contact->shouldReceive('getId')->andReturn(1);
        $contact->shouldReceive('getFirstName')->andReturn('John');
        $contact->shouldReceive('getLastName')->andReturn('Doe');
        $contact->shouldReceive('getPhones')->andReturn([]);
        $contact->shouldReceive('getEmails')->andReturn([]);
        $contact->shouldReceive('getCalls')->andReturn([]);

        $this->doctrineRepository->shouldReceive('findOneBy')
            ->with([
                'id' => 1,
                'tenantId' => 1,
                'deletedAt' => null
            ])
            ->andReturn($contact);

        $result = $this->repository->findById(1);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('John', $result['first_name']);
    }

    public function test_creates_contact(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [['number' => '+61412345678']],
            'emails' => [['email' => 'john@example.com']]
        ];

        $contact = Mockery::mock(Contact::class);
        $contact->shouldReceive('getId')->andReturn(1);
        $contact->shouldReceive('getFirstName')->andReturn('John');
        $contact->shouldReceive('getLastName')->andReturn('Doe');
        $contact->shouldReceive('getPhones')->andReturn([]);
        $contact->shouldReceive('getEmails')->andReturn([]);

        $this->em->shouldReceive('persist')->once();
        $this->em->shouldReceive('flush')->once();

        $result = $this->repository->create($data);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('John', $result['first_name']);
    }

    public function test_enforces_tenant_isolation(): void
    {
        $this->doctrineRepository->shouldReceive('findOneBy')
            ->with([
                'id' => 1,
                'tenantId' => 2, // Different tenant
                'deletedAt' => null
            ])
            ->andReturnNull();

        $result = $this->repository->findById(1);
        $this->assertNull($result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
