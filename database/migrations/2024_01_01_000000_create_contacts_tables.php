<?php

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $connection = app('doctrine.dbal.connection');
        $schema = new Schema();

        // Create contacts table
        $contacts = $schema->createTable('contacts');
        $contacts->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $contacts->addColumn('first_name', Types::STRING, ['length' => 255]);
        $contacts->addColumn('last_name', Types::STRING, ['length' => 255]);
        $contacts->addColumn('tenant_id', Types::INTEGER);
        $contacts->addColumn('created_at', Types::DATETIME_MUTABLE);
        $contacts->addColumn('updated_at', Types::DATETIME_MUTABLE);
        $contacts->addColumn('deleted_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
        
        $contacts->setPrimaryKey(['id']);
        $contacts->addIndex(['tenant_id', 'deleted_at']);
        $contacts->addIndex(['first_name', 'last_name']);

        // Create contact_phones table
        $phones = $schema->createTable('contact_phones');
        $phones->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $phones->addColumn('contact_id', Types::BIGINT);
        $phones->addColumn('number', Types::STRING, ['length' => 20]);
        $phones->addColumn('tenant_id', Types::INTEGER);
        
        $phones->setPrimaryKey(['id']);
        $phones->addForeignKeyConstraint('contacts', ['contact_id'], ['id']);
        $phones->addIndex(['tenant_id', 'number']);
        $phones->addUniqueIndex(['tenant_id', 'contact_id', 'number']);

        // Create contact_emails table
        $emails = $schema->createTable('contact_emails');
        $emails->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $emails->addColumn('contact_id', Types::BIGINT);
        $emails->addColumn('email', Types::STRING, ['length' => 255]);
        $emails->addColumn('tenant_id', Types::INTEGER);
        
        $emails->setPrimaryKey(['id']);
        $emails->addForeignKeyConstraint('contacts', ['contact_id'], ['id']);
        $emails->addIndex(['tenant_id', 'email']);
        $emails->addUniqueIndex(['tenant_id', 'contact_id', 'email']);

        // Create contact_calls table
        $calls = $schema->createTable('contact_calls');
        $calls->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $calls->addColumn('contact_id', Types::BIGINT);
        $calls->addColumn('status', Types::STRING, ['length' => 20]);
        $calls->addColumn('timestamp', Types::DATETIME_MUTABLE);
        $calls->addColumn('phone_number', Types::STRING, ['length' => 20]);
        $calls->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $calls->addColumn('tenant_id', Types::INTEGER);
        
        $calls->setPrimaryKey(['id']);
        $calls->addForeignKeyConstraint('contacts', ['contact_id'], ['id']);
        $calls->addIndex(['tenant_id', 'contact_id', 'timestamp']);
        $calls->addIndex(['status']);

        // Create audit_log table
        $audit = $schema->createTable('audit_log');
        $audit->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $audit->addColumn('tenant_id', Types::INTEGER);
        $audit->addColumn('user_id', Types::INTEGER);
        $audit->addColumn('action', Types::STRING, ['length' => 50]);
        $audit->addColumn('entity_type', Types::STRING, ['length' => 50]);
        $audit->addColumn('entity_id', Types::BIGINT);
        $audit->addColumn('changes', Types::JSON);
        $audit->addColumn('created_at', Types::DATETIME_MUTABLE);
        
        $audit->setPrimaryKey(['id']);
        $audit->addIndex(['tenant_id', 'entity_type', 'entity_id']);
        $audit->addIndex(['created_at']);

        // Execute the schema
        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->executeStatement($sql);
        }
    }

    public function down(): void
    {
        $connection = app('doctrine.dbal.connection');
        
        // Drop tables in reverse order to handle foreign key constraints
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
}; 