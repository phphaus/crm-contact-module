# CRM Example

A production-grade Laravel package that provides CRM functionality for managing contacts through both API and CLI interfaces.

## Features
- RESTful API for contact management
- CLI interface for contact operations
- Multi-tenant support
- Comprehensive validation
- OpenAPI documentation
- Extensive test coverage

## Installation

You can install the package via composer:

```bash
composer require example/crm-example
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Example\CrmExample\CrmExampleServiceProvider"
```

## Usage

### API Usage
```php
use Example\CrmExample\Facades\Contact;

// Create a contact
$contact = Contact::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+61412345678'
]);

// Retrieve contacts
$contacts = Contact::all();

// Find a specific contact
$contact = Contact::find($id);

// Update a contact
Contact::update($id, [
    'name' => 'Jane Doe'
]);

// Delete a contact
Contact::delete($id);
```

### CLI Usage
```bash
# List all contacts
php artisan contacts:manage list

# Create a new contact
php artisan contacts:manage create --name="John Doe" --phone="+61412345678" --email="john@example.com"
```

See [CLI Documentation](./cli.md) for more commands.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@example.com instead of using the issue tracker.

## Credits

- [Example Team](https://github.com/example)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
