<?php

namespace Example\CrmExample\Entities\Traits;

trait ValidatesPhoneNumber
{
    private function validatePhoneNumber(string $number): void
    {
        if (!preg_match('/^\+(?:61|64)\d{9}$/', $number)) {
            throw new \InvalidArgumentException(
                'Phone number must be in E.164 format and start with +61 (Australia) or +64 (New Zealand)'
            );
        }

        // Additional validation for specific country formats
        $countryCode = substr($number, 0, 3);
        $localNumber = substr($number, 3);

        match($countryCode) {
            '+61' => $this->validateAustralianNumber($localNumber),
            '+64' => $this->validateNewZealandNumber($localNumber),
            default => throw new \InvalidArgumentException('Unsupported country code')
        };
    }

    private function validateAustralianNumber(string $localNumber): void
    {
        // Australian mobile numbers start with 4
        // Australian landline numbers start with 2,3,7,8
        if (!preg_match('/^(?:4|[2378])\d{8}$/', $localNumber)) {
            throw new \InvalidArgumentException('Invalid Australian phone number format');
        }
    }

    private function validateNewZealandNumber(string $localNumber): void
    {
        // NZ mobile numbers start with 2
        // NZ landline numbers start with 3,4,6,7,9
        if (!preg_match('/^(?:2|[34679])\d{8}$/', $localNumber)) {
            throw new \InvalidArgumentException('Invalid New Zealand phone number format');
        }
    }
} 