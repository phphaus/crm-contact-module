# CRM Package

A Laravel package for managing contacts with multi-tenant support, phone/email validation, and call tracking.

## Features
- Multi-tenant contact management
- Phone number validation (AU/NZ)
- Email validation and management
- Call tracking and history
- RESTful API with JWT authentication
- Comprehensive test coverage
- OpenAPI/Swagger documentation

## Requirements

### Database
This package is optimized for PostgreSQL and it's the recommended database engine for production use:
- PostgreSQL 12.0 or higher
- JSON/JSONB support
- Array type support
- Proper indexing capabilities for multi-tenant queries

While SQLite is used for testing, PostgreSQL is required for production due to:
- Better multi-tenant isolation
- More efficient JSON operations
- Better indexing strategies
- Array type support for phone/email collections

## Installation

1. Add the package to your Laravel project:
```bash
composer require example/crm-contact-module
```

2. Ensure your PostgreSQL database is configured in your .env:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

3. Publish the configuration:
```bash
php artisan vendor:publish --tag=crm-config
```

4. Run the migrations:
```bash
php artisan migrate
```

5. Set up your environment variables:
```env
CRM_MULTI_TENANT=true
CRM_MAX_PHONES=10
CRM_MAX_EMAILS=10
JWT_SECRET=your-secret-key
```

## API Documentation

### Swagger UI
The API documentation is available via Swagger UI. To view it:

1. Install the Swagger UI package:
```bash
composer require darkaonline/l5-swagger
```

2. Publish the Swagger assets:
```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

3. Generate the documentation:
```bash
php artisan l5-swagger:generate
```

4. View the documentation at:
```
http://your-app/api/documentation
```

### Available Endpoints

#### Contacts
- `GET /api/v1/contacts` - List contacts (with pagination and filters)
- `GET /api/v1/contacts/{id}` - Get a specific contact
- `POST /api/v1/contacts` - Create a new contact
- `PUT /api/v1/contacts/{id}` - Update a contact
- `DELETE /api/v1/contacts/{id}` - Delete a contact
- `POST /api/v1/contacts/{id}/call` - Record a call

## Testing

The package supports both SQLite (for quick tests) and PostgreSQL (for full integration tests).

### Quick Tests with SQLite
```bash
php artisan test
# or
composer test
```

### Full Integration Tests with PostgreSQL
First, ensure you have a PostgreSQL database for testing and update the test configuration in `phpunit.xml`:
```xml
<env name="TEST_DB_CONNECTION" value="pgsql"/>
<env name="TEST_DB_HOST" value="127.0.0.1"/>
<env name="TEST_DB_PORT" value="5432"/>
<env name="TEST_DB_DATABASE" value="crm_test"/>
<env name="TEST_DB_USERNAME" value="postgres"/>
<env name="TEST_DB_PASSWORD" value=""/>
```

Then run:
```bash
composer test:pgsql
```

### Run All Test Configurations
```bash
composer test:all
```

### Generate Test Coverage Report
```bash
composer test:coverage
```

### Run Specific Test File
```bash
php artisan test --filter=ContactControllerTest
```

## Development

### API Documentation

Generate OpenAPI documentation:
```bash
php artisan l5-swagger:generate
```

Clear cache and regenerate documentation:
```bash
php artisan config:clear
php artisan l5-swagger:generate
```

### Code Style

This package follows PSR-12 coding standards. To check your code:

```bash
./vendor/bin/phpcs --standard=PSR12 src/
```

To automatically fix code style:
```bash
./vendor/bin/phpcbf --standard=PSR12 src/
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for your changes
4. Ensure all tests pass
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).