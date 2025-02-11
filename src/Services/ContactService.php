<?php

namespace Example\CrmContactModule\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Example\CrmContactModule\Exceptions\ContactNotFoundException;
use Example\CrmContactModule\Exceptions\DuplicateContactException;
use Example\CrmContactModule\Exceptions\ValidationException;
use Example\CrmContactModule\Services\Validators\ContactValidator;
use Example\CrmContactModule\Services\CallService;
use Example\CrmContactModule\Contracts\ContactServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Example\CrmContactModule\Contracts\ContactSearchCriteria;
use Doctrine\ORM\EntityManagerInterface;

class ContactService implements ContactServiceInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(
        private readonly ContactRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContactValidator $validator,
        private readonly AuditService $auditService
    ) {
        $this->queryBuilder = $this->connection->createQueryBuilder();
    }

    /**
     * Retrieve all contacts with optional filters
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function getAllContacts(array $filters = []): array
    {
        // No need to handle SQL injection, dealt with by Doctrine.
        // Still, I would probably add some sanity checking, just in case

        // Apply filters
        if (isset($filters['phone'])) {
            return $this->repository->findByPhone($filters['phone']);
        }
        if (isset($filters['email_domain'])) {
            return $this->repository->findByEmailDomain($filters['email_domain']);
        }

        return $this->repository->findAll();
    }

    /**
     * Find a contact by ID
     *
     * @throws ContactNotFoundException
     */
    public function getContact(int $id): array
    {
        return $this->repository->findById($id);
    }

    /**
     * Create a new contact
     *
     * @param array<string, mixed> $data
     * @throws DuplicateContactException
     */
    public function createContact(array $data): array
    {
        $this->connection->beginTransaction();

        try {
            // Validate input data
            $this->validator->validateContactData($data);

            // Create contact
            $contact = $this->repository->create($data);

            // Log the action
            $this->auditService->logAction(
                'contact_created',
                'contact',
                $contact['id'],
                $data
            );

            $this->connection->commit();
            return $contact;

        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing contact
     *
     * @param array<string, mixed> $data
     * @throws ContactNotFoundException
     * @throws DuplicateContactException
     */
    public function updateContact(int $id, array $data): array
    {
        $this->connection->beginTransaction();

        try {
            // Validate input data
            $this->validator->validateContactData($data, true);

            // Get original data for audit
            $original = $this->repository->findById($id);

            // Update contact
            $contact = $this->repository->update($id, $data);

            // Log the action with changes
            $this->auditService->logAction(
                'contact_updated',
                'contact',
                $id,
                [
                    'original' => $original,
                    'changes' => $data
                ]
            );

            $this->connection->commit();
            return $contact;

        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete a contact
     *
     * @throws ContactNotFoundException
     */
    public function deleteContact(int $id): void
    {
        $this->connection->beginTransaction();

        try {
            // Get contact data for audit log
            $contact = $this->repository->findById($id);

            // Delete contact
            $this->repository->delete($id);

            // Log the action
            $this->auditService->logAction(
                'contact_deleted',
                'contact',
                $id,
                $contact
            );

            $this->connection->commit();

        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function recordCall(int $id, string $status): void
    {
        $this->connection->beginTransaction();

        try {
            // Get contact's primary phone
            $contact = $this->repository->findById($id);
            if (empty($contact['phones'])) {
                throw new ValidationException('Contact has no phone numbers');
            }

            $phoneNumber = $contact['phones'][0]['number'];

            // Record initial call attempt
            $this->connection->insert(
                'contact_calls',
                [
                    'contact_id' => $id,
                    'tenant_id' => $this->getCurrentTenantId(),
                    'status' => 'initiated',
                    'timestamp' => new \DateTime(),
                    'phone_number' => $phoneNumber
                ],
                [
                    'contact_id' => Types::INTEGER,
                    'tenant_id' => Types::INTEGER,
                    'status' => Types::STRING,
                    'timestamp' => Types::DATETIME_MUTABLE,
                    'phone_number' => Types::STRING
                ]
            );

            $callId = (int) $this->connection->lastInsertId();

            // Initiate the actual call
            $callService = app(CallService::class);

            // Just return a call status straight away. In reality there are many ways to solve this more elegantly, in an async fashion:
            // could update the contact, could publish an event, could add message to queue, could send webhook, etc.
            $finalStatus = $callService->initiateCall($phoneNumber);

            // Update call record with final status
            $this->connection->update(
                'contact_calls',
                [
                    'status' => $finalStatus,
                    'updated_at' => new \DateTime()
                ],
                ['id' => $callId],
                [
                    'status' => Types::STRING,
                    'updated_at' => Types::DATETIME_MUTABLE
                ]
            );

            // Log the action
            $this->auditService->logAction(
                'call_completed',
                'contact',
                $id,
                [
                    'call_id' => $callId,
                    'status' => $finalStatus,
                    'phone_number' => $phoneNumber
                ]
            );

            $this->connection->commit();

        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    private function createBaseQuery(): QueryBuilder
    {
        return $this->queryBuilder
            ->from('contacts', 'c')
            ->where('c.deleted_at IS NULL');
    }

    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        if (!empty($filters['phone'])) {
            $qb->andWhere('c.phone LIKE :phone')
               ->setParameter('phone', '%' . $filters['phone'] . '%', Types::STRING);
        }

        if (!empty($filters['email_domain'])) {
            $qb->andWhere('c.email LIKE :email_domain')
               ->setParameter('email_domain', '%@' . $filters['email_domain'], Types::STRING);
        }
    }

    private function applyTenantContext(QueryBuilder $qb): void
    {
        if ($tenantId = $this->getCurrentTenantId()) {
            $qb->andWhere('c.tenant_id = :tenant_id')
               ->setParameter('tenant_id', $tenantId, Types::INTEGER);
        }
    }

    private function applySorting(QueryBuilder $qb, string $sort): void
    {
        $direction = 'ASC';
        if (str_starts_with($sort, '-')) {
            $direction = 'DESC';
            $sort = substr($sort, 1);
        }

        $allowedFields = ['name', 'email', 'created_at'];
        $field = in_array($sort, $allowedFields) ? $sort : 'name';

        $qb->orderBy("c.$field", $direction);
    }

    private function isDuplicateEmail(string $email): bool
    {
        $qb = $this->createBaseQuery()
            ->select('COUNT(c.id)')
            ->andWhere('c.email = :email')
            ->setParameter('email', $email, Types::STRING);

        $this->applyTenantContext($qb);

        return (int) $qb->executeQuery()->fetchOne() > 0;
    }

    private function getCurrentTenantId(): ?int
    {
        return config('crm.multi_tenant.enabled') ? tenant('id') : null;
    }

    public function listContacts(
        ?string $phone = null,
        ?string $email = null,
        int $perPage = 15,
        int $page = 1
    ): LengthAwarePaginator {
        $qb = $this->createBaseQuery()
            ->select('c.*')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        if ($phone) {
            $qb->andWhere('p.number LIKE :phone')
               ->setParameter('phone', "%{$phone}%", Types::STRING)
               ->leftJoin('c', 'contact_phones', 'p', 'c.id = p.contact_id');
        }

        if ($email) {
            $qb->andWhere('e.email LIKE :email')
               ->setParameter('email', "%{$email}%", Types::STRING)
               ->leftJoin('c', 'contact_emails', 'e', 'c.id = e.contact_id');
        }

        $this->applyTenantContext($qb);

        $total = (clone $qb)
            ->select('COUNT(DISTINCT c.id)')
            ->executeQuery()
            ->fetchOne();

        $items = $qb->executeQuery()->fetchAllAssociative();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );
    }
}
