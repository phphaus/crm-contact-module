<?php

namespace Example\CrmContactModule\Http\Responses;

use Example\CrmContactModule\Contracts\ContactResponseInterface;
use Example\CrmContactModule\Entities\Contact;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactResponse implements ContactResponseInterface, Arrayable
{
    private Contact $contact;

    public function fromEntity(Contact $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    public function toResponse($request): Response
    {
        $format = $request->getAcceptableContentTypes()[0] ?? 'application/json';
        
        return match ($format) {
            'application/xml' => response()->xml($this->toArray()),
            'application/json' => response()->json($this->toArray()),
            default => response()->json($this->toArray()),
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->contact->getId(),
            'first_name' => $this->contact->getFirstName(),
            'last_name' => $this->contact->getLastName(),
            'emails' => $this->contact->getEmails()->map(fn($email) => [
                'email' => $email->getEmail(),
                'type' => $email->getType(),
                'is_primary' => $email->isPrimary(),
                'verified_at' => $email->getVerifiedAt()?->format('c'),
            ])->values()->toArray(),
            'phones' => $this->contact->getPhones()->map(fn($phone) => [
                'number' => $phone->getNumber(),
                'type' => $phone->getType(),
                'is_primary' => $phone->isPrimary(),
            ])->values()->toArray(),
            'created_at' => $this->contact->getCreatedAt()->format('c'),
            'updated_at' => $this->contact->getUpdatedAt()->format('c'),
        ];
    }
} 