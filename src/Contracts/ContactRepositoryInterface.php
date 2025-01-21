<?php

namespace Example\CrmExample\Contracts;

interface ContactRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findByPhone(string $phone): array;
    public function findByEmailDomain(string $domain): array;
    public function create(array $data): array;
    public function update(int $id, array $data): array;
    public function delete(int $id): void;
    public function recordCall(int $id, string $status): void;
} 