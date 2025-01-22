<?php

namespace Example\CrmContactModule\Tests\Unit\Entities;

use Example\CrmContactModule\Entities\Contact;
use Example\CrmContactModule\Entities\ContactPhone;
use Example\CrmContactModule\Entities\ContactEmail;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Config;

class ContactTest extends TestCase
{
    private Contact $contact;

    protected function setUp(): void
    {
        parent::setUp();
        Config::shouldReceive('get')
            ->with('crm.contacts.limits.phones', 10)
            ->andReturn(2);
        Config::shouldReceive('get')
            ->with('crm.contacts.limits.emails', 10)
            ->andReturn(2);

        $this->contact = new Contact('John', 'Doe', 1);
    }

    public function test_can_create_contact(): void
    {
        $this->assertEquals('John', $this->contact->getFirstName());
        $this->assertEquals('Doe', $this->contact->getLastName());
    }

    public function test_validates_name_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('First name must be at least 2 characters');

        $this->contact->setFirstName('J');
    }

    public function test_enforces_phone_limit(): void
    {
        $this->contact->addPhone(new ContactPhone($this->contact, '+61412345678', 1));
        $this->contact->addPhone(new ContactPhone($this->contact, '+61412345679', 1));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum of 2 phone numbers allowed');

        $this->contact->addPhone(new ContactPhone($this->contact, '+61412345670', 1));
    }

    public function test_enforces_email_limit(): void
    {
        $this->contact->addEmail(new ContactEmail($this->contact, 'john@example.com', 1));
        $this->contact->addEmail(new ContactEmail($this->contact, 'john@test.com', 1));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum of 2 email addresses allowed');

        $this->contact->addEmail(new ContactEmail($this->contact, 'john@another.com', 1));
    }

    public function test_can_clear_phones(): void
    {
        $this->contact->addPhone(new ContactPhone($this->contact, '+61412345678', 1));
        $this->assertCount(1, $this->contact->getPhones());

        $this->contact->clearPhones();
        $this->assertEmpty($this->contact->getPhones());
    }

    public function test_updates_timestamp_on_changes(): void
    {
        $originalTime = $this->contact->getUpdatedAt();
        usleep(1000); // Small delay

        $this->contact->setFirstName('Jane');
        $this->assertGreaterThan($originalTime, $this->contact->getUpdatedAt());
    }
}
