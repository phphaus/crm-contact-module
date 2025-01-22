<?php

namespace Example\CrmContactModule\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contact_emails')]
#[ORM\UniqueConstraint(columns: ['tenant_id', 'contact_id', 'email'])]
class ContactEmail
{
    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Contact::class, inversedBy: 'emails')]
    #[ORM\JoinColumn(nullable: false)]
    private Contact $contact;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $email;

    #[ORM\Column(type: Types::INTEGER)]
    private int $tenantId;

    public function __construct(Contact $contact, string $email, int $tenantId)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        $this->contact = $contact;
        $this->email = $email;
        $this->tenantId = $tenantId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
