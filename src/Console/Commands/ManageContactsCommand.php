<?php

namespace Example\CrmContactModule\Console\Commands;

use Example\CrmContactModule\Contracts\ContactServiceInterface;
use Example\CrmContactModule\Exceptions\ContactNotFoundException;
use Example\CrmContactModule\Exceptions\ValidationException;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

class ManageContactsCommand extends Command
{
    protected $signature = 'contacts:manage
        {operation : Operation to perform (list/create/update/delete/call)}
        {--id= : Contact ID for update/delete/call operations}
        {--first-name= : First name for create/update}
        {--last-name= : Last name for create/update}
        {--phones=* : Phone numbers in E.164 format}
        {--emails=* : Email addresses}
        {--call-status= : Call status (successful/busy/failed)}';

    protected $description = 'Manage contacts through CLI';

    public function __construct(
        private readonly ContactServiceInterface $contactService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            return match($this->argument('operation')) {
                'list' => $this->listContacts(),
                'create' => $this->createContact(),
                'update' => $this->updateContact(),
                'delete' => $this->deleteContact(),
                'call' => $this->recordCall(),
                default => $this->error("Unknown operation: {$this->argument('operation')}")
            };
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }

    private function listContacts(): int
    {
        $contacts = $this->contactService->getAllContacts();

        $rows = array_map(fn($contact) => [
            $contact['id'],
            $contact['first_name'],
            $contact['last_name'],
            implode(', ', array_column($contact['phones'], 'number')),
            implode(', ', array_column($contact['emails'], 'email'))
        ], $contacts);

        (new Table($this->output))
            ->setHeaders(['ID', 'First Name', 'Last Name', 'Phones', 'Emails'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }

    private function createContact(): int
    {
        $this->validateRequiredOptions(['first-name', 'last-name']);

        $data = [
            'first_name' => $this->option('first-name'),
            'last_name' => $this->option('last-name'),
            'phones' => array_map(
                fn($number) => ['number' => $number],
                $this->option('phones') ?? []
            ),
            'emails' => array_map(
                fn($email) => ['email' => $email],
                $this->option('emails') ?? []
            )
        ];

        $contact = $this->contactService->createContact($data);
        $this->info("Contact created with ID: {$contact['id']}");

        return Command::SUCCESS;
    }

    private function updateContact(): int
    {
        $this->validateRequiredOptions(['id']);
        $id = (int) $this->option('id');

        $data = array_filter([
            'first_name' => $this->option('first-name'),
            'last_name' => $this->option('last-name')
        ]);

        if ($phones = $this->option('phones')) {
            $data['phones'] = array_map(fn($number) => ['number' => $number], $phones);
        }

        if ($emails = $this->option('emails')) {
            $data['emails'] = array_map(fn($email) => ['email' => $email], $emails);
        }

        $this->contactService->updateContact($id, $data);
        $this->info("Contact updated successfully");

        return Command::SUCCESS;
    }

    private function deleteContact(): int
    {
        $this->validateRequiredOptions(['id']);
        $id = (int) $this->option('id');

        $this->contactService->deleteContact($id);
        $this->info("Contact deleted successfully");

        return Command::SUCCESS;
    }

    private function recordCall(): int
    {
        $this->validateRequiredOptions(['id', 'call-status']);

        $id = (int) $this->option('id');
        $status = $this->option('call-status');

        if (!in_array($status, ['successful', 'busy', 'failed'])) {
            throw new ValidationException('Invalid call status. Must be: successful, busy, or failed');
        }

        $this->contactService->recordCall($id, $status);
        $this->info("Call recorded successfully");

        return Command::SUCCESS;
    }

    private function validateRequiredOptions(array $options): void
    {
        foreach ($options as $option) {
            if (!$this->option($option)) {
                throw new ValidationException("Option --{$option} is required");
            }
        }
    }
}
