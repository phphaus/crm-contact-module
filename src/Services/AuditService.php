<?php

namespace Example\CrmExample\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class AuditService
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    /**
     * Log an action in the audit log
     *
     * @param string $action The action performed (e.g., 'contact_created')
     * @param string $entityType The type of entity (e.g., 'contact')
     * @param int $entityId The ID of the affected entity
     * @param array<string, mixed> $changes The changes made
     */
    public function logAction(string $action, string $entityType, int $entityId, array $changes): void
    {
        $this->connection->insert(
            'audit_log',
            [
                'tenant_id' => $this->getCurrentTenantId(),
                'user_id' => $this->getCurrentUserId(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'changes' => json_encode($changes, JSON_THROW_ON_ERROR),
                'created_at' => new \DateTime()
            ],
            [
                'tenant_id' => Types::INTEGER,
                'user_id' => Types::INTEGER,
                'action' => Types::STRING,
                'entity_type' => Types::STRING,
                'entity_id' => Types::INTEGER,
                'changes' => Types::JSON,
                'created_at' => Types::DATETIME_MUTABLE
            ]
        );
    }

    /**
     * Get audit log entries for a specific entity
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEntityAuditLog(string $entityType, int $entityId): array
    {
        $qb = $this->connection->createQueryBuilder();
        
        return $qb->select('*')
            ->from('audit_log')
            ->where('entity_type = :entity_type')
            ->andWhere('entity_id = :entity_id')
            ->andWhere('tenant_id = :tenant_id')
            ->setParameter('entity_type', $entityType, Types::STRING)
            ->setParameter('entity_id', $entityId, Types::INTEGER)
            ->setParameter('tenant_id', $this->getCurrentTenantId(), Types::INTEGER)
            ->orderBy('created_at', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Get audit log entries for the current tenant
     *
     * @param array<string, mixed> $filters Optional filters
     * @return array<int, array<string, mixed>>
     */
    public function getTenantAuditLog(array $filters = []): array
    {
        $qb = $this->connection->createQueryBuilder();
        
        $qb->select('*')
            ->from('audit_log')
            ->where('tenant_id = :tenant_id')
            ->setParameter('tenant_id', $this->getCurrentTenantId(), Types::INTEGER);

        if (isset($filters['action'])) {
            $qb->andWhere('action = :action')
               ->setParameter('action', $filters['action'], Types::STRING);
        }

        if (isset($filters['entity_type'])) {
            $qb->andWhere('entity_type = :entity_type')
               ->setParameter('entity_type', $filters['entity_type'], Types::STRING);
        }

        if (isset($filters['from_date'])) {
            $qb->andWhere('created_at >= :from_date')
               ->setParameter('from_date', new \DateTime($filters['from_date']), Types::DATETIME_MUTABLE);
        }

        if (isset($filters['to_date'])) {
            $qb->andWhere('created_at <= :to_date')
               ->setParameter('to_date', new \DateTime($filters['to_date']), Types::DATETIME_MUTABLE);
        }

        if (isset($filters['user_id'])) {
            $qb->andWhere('user_id = :user_id')
               ->setParameter('user_id', $filters['user_id'], Types::INTEGER);
        }

        return $qb->orderBy('created_at', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function getCurrentTenantId(): int
    {
        $tenantId = tenant('id');
        if (!$tenantId) {
            throw new \RuntimeException("Tenant context not found");
        }
        return $tenantId;
    }

    private function getCurrentUserId(): int
    {
        $userId = auth()->id();
        if (!$userId) {
            throw new \RuntimeException("User context not found");
        }
        return $userId;
    }
} 