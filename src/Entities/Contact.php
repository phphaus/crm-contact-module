<?php

namespace Example\CrmContactModule\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Facades\Config;

#[ORM\Entity]
#[ORM\Table(name: 'contacts')]
#[ORM\Index(columns: ['tenant_id', 'deleted_at'], name: 'idx_tenant_deleted')]
#[ORM\Index(columns: ['first_name', 'last_name'], name: 'idx_name')]
class Contact
{
    private readonly int $maxPhones;
    private readonly int $maxEmails;

    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $firstName;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $lastName;

    #[ORM\Column(type: Types::INTEGER)]
    private int $tenantId;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $updatedAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $deletedAt = null;

    #[ORM\OneToMany(targetEntity: ContactPhone::class, mappedBy: 'contact', cascade: ['persist', 'remove'])]
    private array $phones = [];

    #[ORM\OneToMany(targetEntity: ContactEmail::class, mappedBy: 'contact', cascade: ['persist', 'remove'])]
    private array $emails = [];

    #[ORM\OneToMany(targetEntity: ContactCall::class, mappedBy: 'contact')]
    private array $calls = [];

    public function __construct(string $firstName, string $lastName, int $tenantId)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->tenantId = $tenantId;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        $this->maxPhones = Config::get('crm.contacts.limits.phones', 10);
        $this->maxEmails = Config::get('crm.contacts.limits.emails', 10);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        if (strlen($firstName) < 2) {
            throw new \InvalidArgumentException('First name must be at least 2 characters');
        }
        $this->firstName = $firstName;
        $this->updatedAt = new \DateTime();
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        if (strlen($lastName) < 2) {
            throw new \InvalidArgumentException('Last name must be at least 2 characters');
        }
        $this->lastName = $lastName;
        $this->updatedAt = new \DateTime();
    }

    public function addPhone(ContactPhone $phone): void
    {
        if (count($this->phones) >= $this->maxPhones) {
            throw new \InvalidArgumentException(
                sprintf('Maximum of %d phone numbers allowed', $this->maxPhones)
            );
        }
        $this->phones[] = $phone;
    }

    public function addEmail(ContactEmail $email): void
    {
        if (count($this->emails) >= $this->maxEmails) {
            throw new \InvalidArgumentException(
                sprintf('Maximum of %d email addresses allowed', $this->maxEmails)
            );
        }
        $this->emails[] = $email;
    }

    public function clearPhones(): void
    {
        $this->phones = [];
    }

    public function clearEmails(): void
    {
        $this->emails = [];
    }

    /** @return ContactPhone[] */
    public function getPhones(): array
    {
        return $this->phones;
    }

    /** @return ContactEmail[] */
    public function getEmails(): array
    {
        return $this->emails;
    }

    /** @return ContactCall[] */
    public function getCalls(): array
    {
        return $this->calls;
    }

    public function setDeletedAt(\DateTime $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    // Getters and setters...
}
