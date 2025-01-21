<?php

namespace Example\CrmExample\Database\Factories;

use Example\CrmExample\Models\Contact;
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
        ];
    }
} 