<?php

namespace Example\CrmExample\Tests\Factories;

use Example\CrmExample\Contracts\ContactServiceInterface;
use Illuminate\Container\Container;

class ContactFactory
{
    private ContactServiceInterface $service;

    public function __construct()
    {
        $this->service = Container::getInstance()->make(ContactServiceInterface::class);
    }

    public function create(array $attributes = []): array
    {
        $defaults = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [
                ['number' => '+61412345678']
            ],
            'emails' => [
                ['email' => 'john@example.com']
            ]
        ];

        return $this->service->createContact(array_merge($defaults, $attributes));
    }

    public function createMany(int $count, array $attributes = []): array
    {
        return array_map(
            fn() => $this->create($attributes),
            range(1, $count)
        );
    }
} 