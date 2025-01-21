<?php

namespace Example\CrmExample\Contracts;

interface ContactServiceInterface
{
    /**
     * Get all contacts with optional filters
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function getAllContacts(array $filters = []): array;

    /**
     * Get a contact by ID
     *
     * @throws \Example\CrmExample\Exceptions\ContactNotFoundException
     */
    public function getContact(int $id): array;

    /**
     * Create a new contact
     *
     * @param array<string, mixed> $data
     * @throws \Example\CrmExample\Exceptions\ValidationException
     * @throws \Example\CrmExample\Exceptions\DuplicateContactException
     */
    public function createContact(array $data): array;

    /**
     * Update an existing contact
     *
     * @param array<string, mixed> $data
     * @throws \Example\CrmExample\Exceptions\ContactNotFoundException
     * @throws \Example\CrmExample\Exceptions\ValidationException
     */
    public function updateContact(int $id, array $data): array;

    /**
     * Delete a contact
     *
     * @throws \Example\CrmExample\Exceptions\ContactNotFoundException
     */
    public function deleteContact(int $id): void;

    /**
     * Record a call for a contact
     *
     * @throws \Example\CrmExample\Exceptions\ContactNotFoundException
     * @throws \Example\CrmExample\Exceptions\ValidationException
     */
    public function recordCall(int $id, string $status): void;
} 