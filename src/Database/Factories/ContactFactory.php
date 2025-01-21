<?php

namespace Example\CrmExample\Database\Factories;

use Example\CrmExample\Models\Contact;
use Example\CrmExample\Models\ContactPhone;
use Example\CrmExample\Models\ContactEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'tenant_id' => fn() => tenant('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function withPhones(int $count = 1, array $attributes = []): self
    {
        return $this->has(
            ContactPhone::factory()->count($count)->state($attributes),
            'phones'
        );
    }

    public function withEmails(int $count = 1, array $attributes = []): self
    {
        return $this->has(
            ContactEmail::factory()->count($count)->state($attributes),
            'emails'
        );
    }
} 