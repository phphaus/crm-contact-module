<?php

namespace Example\CrmExample\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Example\CrmExample\Contracts\ContactRepositoryInterface;
use Example\CrmExample\Entities\Contact;
use Example\CrmExample\Entities\ContactPhone;
use Example\CrmExample\Entities\ContactEmail;
use Example\CrmExample\Entities\ContactCall;
use Example\CrmExample\Exceptions\ContactNotFoundException;
use Example\CrmExample\Exceptions\ValidationException;

class DoctrineContactRepository implements ContactRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    public function findById(int $id): ?array
    {
        $contact = $this->em->getRepository(Contact::class)
            ->findOneBy([
                'id' => $id,
                'tenantId' => $this->getCurrentTenantId(),
                'deletedAt' => null
            ]);

        return $contact ? $this->hydrateContact($contact) : null;
    }

    public function findByPhone(string $phone): array
    {
        $qb = $this->em->createQueryBuilder();
        
        $contacts = $qb->select('c')
            ->from(Contact::class, 'c')
            ->join('c.phones', 'p')
            ->where('p.number LIKE :phone')
            ->andWhere('c.tenantId = :tenantId')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('phone', '%' . $phone . '%')
            ->setParameter('tenantId', $this->getCurrentTenantId())
            ->getQuery()
            ->getResult();

        return array_map([$this, 'hydrateContact'], $contacts);
    }

    public function findByEmailDomain(string $domain): array
    {
        $qb = $this->em->createQueryBuilder();
        
        $contacts = $qb->select('c')
            ->from(Contact::class, 'c')
            ->join('c.emails', 'e')
            ->where('e.email LIKE :domain')
            ->andWhere('c.tenantId = :tenantId')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('domain', '%@' . $domain)
            ->setParameter('tenantId', $this->getCurrentTenantId())
            ->getQuery()
            ->getResult();

        return array_map([$this, 'hydrateContact'], $contacts);
    }

    public function create(array $data): array
    {
        $contact = new Contact(
            $data['first_name'],
            $data['last_name'],
            $this->getCurrentTenantId()
        );

        foreach ($data['phones'] ?? [] as $phone) {
            $contact->addPhone(new ContactPhone(
                $contact,
                $phone['number'],
                $this->getCurrentTenantId()
            ));
        }

        foreach ($data['emails'] ?? [] as $email) {
            $contact->addEmail(new ContactEmail(
                $contact,
                $email['email'],
                $this->getCurrentTenantId()
            ));
        }

        $this->em->persist($contact);
        $this->em->flush();

        return $this->hydrateContact($contact);
    }

    public function update(int $id, array $data): array
    {
        /** @var Contact $contact */
        $contact = $this->em->getRepository(Contact::class)->findOneBy([
            'id' => $id,
            'tenantId' => $this->getCurrentTenantId(),
            'deletedAt' => null
        ]);

        if (!$contact) {
            throw new ContactNotFoundException("Contact not found");
        }

        if (isset($data['first_name'])) {
            $contact->setFirstName($data['first_name']);
        }
        if (isset($data['last_name'])) {
            $contact->setLastName($data['last_name']);
        }

        if (isset($data['phones'])) {
            $contact->clearPhones();
            foreach ($data['phones'] as $phone) {
                $contact->addPhone(new ContactPhone(
                    $contact,
                    $phone['number'],
                    $this->getCurrentTenantId()
                ));
            }
        }

        if (isset($data['emails'])) {
            $contact->clearEmails();
            foreach ($data['emails'] as $email) {
                $contact->addEmail(new ContactEmail(
                    $contact,
                    $email['email'],
                    $this->getCurrentTenantId()
                ));
            }
        }

        $contact->setUpdatedAt(new \DateTime());
        $this->em->flush();

        return $this->hydrateContact($contact);
    }

    public function delete(int $id): void
    {
        /** @var Contact $contact */
        $contact = $this->em->getRepository(Contact::class)->findOneBy([
            'id' => $id,
            'tenantId' => $this->getCurrentTenantId(),
            'deletedAt' => null
        ]);

        if (!$contact) {
            throw new ContactNotFoundException("Contact not found");
        }

        $contact->setDeletedAt(new \DateTime());
        $this->em->flush();
    }

    public function recordCall(int $id, string $status): void
    {
        /** @var Contact $contact */
        $contact = $this->em->getRepository(Contact::class)->findOneBy([
            'id' => $id,
            'tenantId' => $this->getCurrentTenantId(),
            'deletedAt' => null
        ]);

        if (!$contact) {
            throw new ContactNotFoundException("Contact not found");
        }

        if (empty($contact->getPhones())) {
            throw new ValidationException("Contact has no phone numbers");
        }

        $call = new ContactCall(
            $contact,
            $status,
            $contact->getPhones()[0]->getNumber(),
            $this->getCurrentTenantId()
        );

        $this->em->persist($call);
        $this->em->flush();
    }

    private function hydrateContact(Contact $contact): array
    {
        return [
            'id' => $contact->getId(),
            'first_name' => $contact->getFirstName(),
            'last_name' => $contact->getLastName(),
            'phones' => array_map(
                fn($phone) => ['number' => $phone->getNumber()],
                $contact->getPhones()
            ),
            'emails' => array_map(
                fn($email) => ['email' => $email->getEmail()],
                $contact->getEmails()
            ),
            'calls' => array_map(
                fn($call) => [
                    'status' => $call->getStatus(),
                    'timestamp' => $call->getTimestamp()->format('Y-m-d H:i:s')
                ],
                $contact->getCalls()
            )
        ];
    }

    private function getCurrentTenantId(): int
    {
        $tenantId = tenant('id');
        if (!$tenantId) {
            throw new \RuntimeException("Tenant context not found");
        }
        return $tenantId;
    }
} 