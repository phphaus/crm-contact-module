<?php

namespace Example\CrmContactModule\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Example\CrmContactModule\Entities\Traits\ValidatesPhoneNumber;
use Illuminate\Support\Facades\Config;

#[ORM\Entity]
#[ORM\Table(name: 'contact_calls')]
#[ORM\Index(columns: ['tenant_id', 'contact_id', 'timestamp'])]
#[ORM\Index(columns: ['status'])]
class ContactCall
{
    use ValidatesPhoneNumber;

    private readonly array $validStatuses;

    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Contact::class, inversedBy: 'calls')]
    #[ORM\JoinColumn(nullable: false)]
    private Contact $contact;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $status;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $phoneNumber;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $timestamp;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $tenantId;

    public function __construct(Contact $contact, string $status, string $phoneNumber, int $tenantId)
    {
        $this->validStatuses = Config::get('crm.calls.statuses', ['initiated', 'successful', 'busy', 'failed']);

        if (!in_array($status, $this->validStatuses)) {
            throw new \InvalidArgumentException('Invalid call status');
        }

        $this->validatePhoneNumber($phoneNumber);

        $this->contact = $contact;
        $this->status = $status;
        $this->phoneNumber = $phoneNumber;
        $this->timestamp = new \DateTime();
        $this->tenantId = $tenantId;
    }

    public function updateStatus(string $status): void
    {
        if (!in_array($status, $this->validStatuses)) {
            throw new \InvalidArgumentException('Invalid call status');
        }

        $this->status = $status;
        $this->updatedAt = new \DateTime();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}
