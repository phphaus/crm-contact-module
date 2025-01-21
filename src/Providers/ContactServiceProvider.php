<?php

namespace Example\CrmContactModule\Providers;

use Example\CrmContactModule\Contracts\ApiResponseInterface;
use Example\CrmContactModule\Entities\Contact;
use Example\CrmContactModule\Entities\ContactCall;
use Example\CrmContactModule\Http\Responses\ApiResponse;
use Illuminate\Support\ServiceProvider;

class ContactServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ApiResponseInterface::class, function () {
            return new ApiResponse([
                Contact::class => fn(Contact $contact) => [
                    'id' => $contact->getId(),
                    'first_name' => $contact->getFirstName(),
                    'last_name' => $contact->getLastName(),
                    'emails' => $contact->getEmails()->map->toArray(),
                    'phones' => $contact->getPhones()->map->toArray(),
                    'created_at' => $contact->getCreatedAt()->format('c'),
                    'updated_at' => $contact->getUpdatedAt()->format('c'),
                ],
                ContactCall::class => fn(ContactCall $call) => [
                    'id' => $call->getId(),
                    'contact_id' => $call->getContact()->getId(),
                    'status' => $call->getStatus()->value,
                    'duration' => $call->getDuration(),
                    'created_at' => $call->getCreatedAt()->format('c'),
                ],
            ]);
        });
    }
} 