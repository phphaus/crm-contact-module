<?php

namespace Example\CrmExample\Contracts;

interface ContactInterface
{
    public function getId(): int;
    public function getFirstName(): string;
    public function getLastName(): string;
    public function getPhones(): array;
    public function getEmails(): array;
    public function getCalls(): array;
} 