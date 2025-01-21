# CLI Documentation

## Available Commands

### 1. Setup Command
**Location:** `src/Console/Commands/CrmSetupCommand.php`

```bash
php artisan crm:setup [--tenant-id=] [--refresh]
```

**Options:**
- `--tenant-id`: The tenant ID to set up
- `--refresh`: Refresh the database tables

**Examples:**
```bash
# Setup for a specific tenant
php artisan crm:setup --tenant-id=1

# Refresh all tables
php artisan crm:setup --refresh
```

### 2. Contact Management Command
**Location:** `src/Console/Commands/ManageContactsCommand.php`

```bash
php artisan contacts:manage {operation} [options]
```

**Operations:**
- `list`: List all contacts
- `create`: Create a new contact
- `update`: Update an existing contact
- `delete`: Delete a contact
- `call`: Record a call

**Options:**
- `--id=`: Contact ID for update/delete/call operations
- `--first-name=`: First name for create/update
- `--last-name=`: Last name for create/update
- `--phones=*`: Phone numbers in E.164 format
- `--emails=*`: Email addresses
- `--call-status=`: Call status (successful/busy/failed)

**Examples:**
```bash
# Create a contact
php artisan contacts:manage create --first-name="John" --last-name="Doe" --phones="+61412345678" --emails="john@example.com"

# Update a contact
php artisan contacts:manage update --id=1 --first-name="Johnny"

# Record a call
php artisan contacts:manage call --id=1 --call-status="successful"
```
