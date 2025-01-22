<?php

namespace Example\CrmContactModule\Tests\Unit\Entities\Traits;

use Example\CrmContactModule\Entities\Traits\ValidatesPhoneNumber;
use PHPUnit\Framework\TestCase;

class ValidatesPhoneNumberTest extends TestCase
{
    private object $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new class {
            use ValidatesPhoneNumber;

            public function validate(string $number): void
            {
                $this->validatePhoneNumber($number);
            }
        };
    }

    public function test_accepts_valid_australian_mobile(): void
    {
        $this->validator->validate('+61412345678');
        $this->addToAssertionCount(1); // No exception thrown
    }

    public function test_accepts_valid_australian_landline(): void
    {
        $this->validator->validate('+61234567890');
        $this->addToAssertionCount(1);
    }

    public function test_accepts_valid_nz_mobile(): void
    {
        $this->validator->validate('+64212345678');
        $this->addToAssertionCount(1);
    }

    public function test_accepts_valid_nz_landline(): void
    {
        $this->validator->validate('+64312345678');
        $this->addToAssertionCount(1);
    }

    public function test_rejects_invalid_country_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validate('+1234567890');
    }

    public function test_rejects_invalid_australian_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validate('+61512345678'); // Invalid area code
    }

    public function test_rejects_invalid_nz_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validate('+64112345678'); // Invalid area code
    }

    public function test_rejects_non_e164_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validate('0412345678');
    }
}
