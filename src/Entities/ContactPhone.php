<?php

namespace Example\CrmExample\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Example\CrmExample\Entities\Traits\ValidatesPhoneNumber;

#[ORM\Entity]
#[ORM\Table(name: 'contact_phones')]
#[ORM\UniqueConstraint(columns: ['tenant_id', 'contact_id', 'number'])]
class ContactPhone
{
    use ValidatesPhoneNumber;

    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Contact::class, inversedBy: 'phones')]
    #[ORM\JoinColumn(nullable: false)]
    private Contact $contact;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $number;

    #[ORM\Column(type: Types::INTEGER)]
    private int $tenantId;

    public function __construct(Contact $contact, string $number, int $tenantId)
    {
        $this->validatePhoneNumber($number);

        $this->contact = $contact;
        $this->number = $number;
        $this->tenantId = $tenantId;
    }

    public function getNumber(): string
    {
        return $this->number;
    }
} 