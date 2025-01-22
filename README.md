# CRM Contact Module

A Laravel standalone module package for managing contacts with multi-tenant support, phone/email validation, and call tracking.
+ [![Test and Publish](https://github.com/example/crm-contact-module/actions/workflows/test-and-publish.yml/badge.svg)](https://github.com/example/crm-contact-module/actions/workflows/test-and-publish.yml)
 
## Assumptions
- The task mentions many fixed requirements and well as the word "production grade code", which as a consequence makes "time" the only variable within the scope-quality-time triad. Given the limited time allocated to this task, and the open-ended nature of "production grade code" the author has decided that the main assessment criteria would be around architecture and code quality, rather than having a functional and working prototype.
- There are many different ways to develop and integrate Laravel modules. All come with their own pros and cons and the decision to go with each approach depends heavily on existing processes, tooling and ways of working. For this example, I made the decision to develop this module as a standalone package.
  - This module can be tested in isolation and published and integrated into the wider CRM solution once the pipeline and tests for the module pass.
  - While in development, a lean Laravel installation could be added to the codebase (effectively making it a microservice) to have access to artesan and the same tooling the wider CRM would have. Alterntively, the team could work with symlinks or locally published modules instead.
  - A completely alternative approach would be to have a monolithic repository for the CRM, with all modules being developed alongside each other. This approach has all the typical advantages and disadvantages of monoliths.
  - Given the elasticity of the requirements and its "esoteric" nature, it is not clear which approach would be better suited.
- For scalability reason we have assumed that the wider CRM would be using Doctrine rather than Eloquent, due to it using the Object Mapper pattern and not Active Record. This approach is seen as overall more flexible as it is more loosely coupled.
- I have decided for a more DDD heavy approach by utilising the Doctrine ORM, rather than simply the DBAL. While this can have minor performance impacts, this decision was made for simplicity of maintenance. To quote Martin Fowler: "Any fool can write code that a computer can understand. Good programmers write code that humans can understand.”
- Object inheritance could be improved and native Laravel classes and objects could be utilised more. Limited time did not allow for this.

## Notes for Reviewers
- For all the above reasons, note that this README.md contains the commands that could be run on an integrated version of this module with the wider Laravel app. However, this was not tested and is almost certainly not working yet, given the time constraints.
- Some functionality, such as Migrations, Auth + JWT, CI/CD, Async Requests and OpenAPI/Swagger have been mocked or scaffolded as examples.
- Many different areas are not final and would need improvements to be production grade.
- A secret store is not used, which would be highly recommended in a production-grade code base.
- Tests are not complete, and would require additional test cases, fuzz testing and even types of tests (indicated by the folders in `src/Tests`).
- Cross-endpoint ACIDity in database transactions was not implemented in this example. It could be done using [Laravel Workflow and the Saga Pattern](https://laravel-workflow.com/docs/features/sagas/).
- I embraced AI to collaborate on this codebase, leveraging it as a tool to accelerate development and refine precision. My instructions and design decisions are captured in:
  - [App Docs](./docs/app.md) 
  - [API Docs](./docs/api.md) 
  - [CLI Docs](./docs/cli.md)
  - [Features Docs](./docs/features.md) 
  - [Technology Docs](./docs/technologies.md) 
- Additionally, the following documents provide an overview of the current feature set and outline the key decisions and processes that shaped the project:
  - [Decisions](./docs/decisions.md)
  - [Handover](./docs/handover.md)

## Summary of Features
- Multi-tenant contact management
- Phone number validation (AU/NZ)
- Email validation and management
- Call tracking and history
- RESTful API with JWT authentication
- Test Examples
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
