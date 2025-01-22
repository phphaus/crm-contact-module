<?php

namespace Example\CrmContactModule\Services\Validators;

use Example\CrmContactModule\Exceptions\ValidationException;

class ContactValidator
{
    /**
     * @param array<string, mixed> $data
     * @param bool $isUpdate
     */
    public function validateContactData(array $data, bool $isUpdate = false): void
    {
        // Required fields for create
        if (!$isUpdate) {
            $this->validateRequired($data, ['first_name', 'last_name']);
        }

        // Validate names if provided
        if (isset($data['first_name'])) {
            $this->validateName($data['first_name'], 'first_name');
        }
        if (isset($data['last_name'])) {
            $this->validateName($data['last_name'], 'last_name');
        }

        // Validate phones if provided
        if (isset($data['phones'])) {
            $this->validatePhones($data['phones']);
        }

        // Validate emails if provided
        if (isset($data['emails'])) {
            $this->validateEmails($data['emails']);
        }
    }

    private function validateRequired(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new ValidationException("$field is required");
            }
        }
    }

    private function validateName(string $name, string $field): void
    {
        if (strlen($name) < 2) {
            throw new ValidationException("$field must be at least 2 characters long");
        }
    }

    private function validatePhones(array $phones): void
    {
        if (count($phones) > 10) {
            throw new ValidationException('Maximum of 10 phone numbers allowed');
        }

        foreach ($phones as $phone) {
            if (!isset($phone['number'])) {
                throw new ValidationException('Phone number is required');
            }
            $this->validatePhoneNumber($phone['number']);
        }
    }

    private function validateEmails(array $emails): void
    {
        if (count($emails) > 10) {
            throw new ValidationException('Maximum of 10 email addresses allowed');
        }

        foreach ($emails as $email) {
            if (!isset($email['email'])) {
                throw new ValidationException('Email address is required');
            }
            $this->validateEmail($email['email']);
        }
    }

    public function validatePhoneNumber(string $number): void
    {
        if (!preg_match('/^\+(?:61|64)\d{9}$/', $number)) {
            throw new ValidationException('Invalid phone number format. Must be AU or NZ E.164 format');
        }
    }

    public function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }
    }

    public function validateEmailDomain(string $domain): void
    {
        if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) {
            throw new ValidationException('Invalid email domain format');
        }
    }
}
