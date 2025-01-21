<?php

namespace Example\CrmContactModule\Http\Controllers;

use Example\CrmContactModule\Contracts\ContactServiceInterface;
use Example\CrmContactModule\Contracts\ContactResponseInterface;
use Example\CrmContactModule\Contracts\ContactCollectionResponseInterface;
use Example\CrmContactModule\DataTransfer\ContactSearchCriteria;
use Example\CrmContactModule\DataTransfer\CreateContactCommand;
use Example\CrmContactModule\DataTransfer\UpdateContactCommand;
use Example\CrmContactModule\Http\Requests\CreateContactRequest;
use Example\CrmContactModule\Http\Requests\UpdateContactRequest;
use Example\CrmContactModule\Http\Requests\SearchContactRequest;
use Symfony\Component\HttpFoundation\Response;
use Example\CrmContactModule\Contracts\ApiResponseInterface;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactServiceInterface $contactService,
        private readonly ApiResponseInterface $response
    ) {
    }

    public function index(SearchContactRequest $request): Response
    {
        $contacts = $this->contactService->getAllContacts($request->toCriteria());
        return $this->response->fromData($contacts)->toResponse($request);
    }

    public function show(int $id): Response
    {
        $contact = $this->contactService->getContact($id);
        return $this->response->fromData($contact)->toResponse(request());
    }

    public function store(CreateContactRequest $request): Response
    {
        $command = new CreateContactCommand(
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            emails: $request->input('emails'),
            phones: $request->input('phones')
        );

        $contact = $this->contactService->createContact($command);

        return $this->response->fromData($contact)->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateContactRequest $request, int $id): Response
    {
        $contact = $this->contactService->getContact($id);
        
        $command = new UpdateContactCommand(
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            emails: $request->input('emails'),
            phones: $request->input('phones')
        );

        $updatedContact = $this->contactService->updateContact($contact, $command);

        return $this->response->fromData($updatedContact)->toResponse($request);
    }

    public function destroy(int $id): Response
    {
        $contact = $this->contactService->getContact($id);
        $this->contactService->deleteContact($contact);

        return response()->noContent();
    }
} 