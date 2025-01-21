<?php

namespace Example\CrmExample\Tests\Unit\Services\Validators;

use Example\CrmExample\Exceptions\ValidationException;
use Example\CrmExample\Services\Validators\ContactValidator;
use PHPUnit\Framework\TestCase;

class ContactValidatorTest extends TestCase
{
    private ContactValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ContactValidator();
    }

    public function test_validates_required_fields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('first_name is required');

        $this->validator->validateContactData([
            'last_name' => 'Doe'
        ]);
    }

    public function test_validates_phone_format(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid phone number format');

        $this->validator->validateContactData([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phones' => [
                ['number' => '0412345678'] // Not E.164 format
            ]
        ], true);
    }

    public function test_validates_email_format(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid email format');

        $this->validator->validateContactData([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'emails' => [
                ['email' => 'invalid-email']
            ]
        ], true);
    }

    public function test_allows_partial_updates(): void
    {
        $this->validator->validateContactData([
            'first_name' => 'John'
        ], true);

        $this->addToAssertionCount(1); // No exception thrown
    }
} 