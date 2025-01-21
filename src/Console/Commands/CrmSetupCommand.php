<?php

namespace Example\CrmExample\Console\Commands;

use Doctrine\DBAL\Connection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrmSetupCommand extends Command
{
    protected $signature = 'crm:setup
        {--tenant-id= : The tenant ID to set up}
        {--refresh : Refresh the database tables}';

    protected $description = 'Set up or refresh the CRM database tables';

    public function handle(Connection $connection): int
    {
        if ($this->option('refresh')) {
            if (!$this->confirm('This will delete all CRM data. Are you sure?')) {
                return self::FAILURE;
            }

            $this->info('Dropping existing tables...');
            $this->dropTables($connection);
        }

        $this->info('Creating tables...');
        $this->createTables($connection);

        if ($tenantId = $this->option('tenant-id')) {
            $this->info("Setting up tenant $tenantId...");
            $this->setupTenant($connection, (int) $tenantId);
        }

        $this->info('CRM setup completed successfully.');
        return self::SUCCESS;
    }

    private function dropTables(Connection $connection): void
    {
        $tables = [
            'audit_log',
            'contact_calls',
            'contact_emails',
            'contact_phones',
            'contacts'
        ];

        foreach ($tables as $table) {
            $connection->executeStatement("DROP TABLE IF EXISTS $table CASCADE");
        }
    }

    private function createTables(Connection $connection): void
    {
        $migrationPath = database_path('Migrations/2024_01_01_000000_create_contacts_tables.php');
        require_once $migrationPath;

        $migration = new \CreateContactsTables();
        $migration->up();
    }

    private function setupTenant(Connection $connection, int $tenantId): void
    {
        // Add any tenant-specific setup here
        // For example, creating default contacts, etc.
    }
} 