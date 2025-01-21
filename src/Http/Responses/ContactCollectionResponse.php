<?php

namespace Example\CrmContactModule\Http\Responses;

use Doctrine\Common\Collections\Collection;
use Example\CrmContactModule\Contracts\ContactCollectionResponseInterface;
use Example\CrmContactModule\Contracts\ContactResponseInterface;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\Response;

class ContactCollectionResponse implements ContactCollectionResponseInterface, Arrayable
{
    private Collection $contacts;
    
    public function __construct(
        private readonly ContactResponseInterface $contactResponse
    ) {
    }

    public function fromCollection(Collection $contacts): self
    {
        $this->contacts = $contacts;
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
            'data' => $this->contacts->map(
                fn($contact) => $this->contactResponse->fromEntity($contact)->toArray()
            )->toArray(),
            'meta' => [
                'total' => $this->contacts->count(),
            ],
        ];
    }
} 