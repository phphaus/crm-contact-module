# Example Application Design for Principal Developer Role

## Project Overview
This project demonstrates a CRM module using Laravel to manage contacts. The application supports API and CLI interfaces, adheres to best practices for multi-tenant setups, and includes comprehensive documentation and testing. The design reflects realistic production scenarios, addressing performance, scalability, and maintainability.

---

## Project Structure
### Core Directories
- **`app/`**
  - **`Http/`**
    - **`Controllers/`**
      - `Api/ContactController.php` - Handles HTTP requests for contacts.
    - **`Requests/`**
      - `StoreContactRequest.php` - Validates contact creation requests.
      - `UpdateContactRequest.php` - Validates contact update requests.
  - **`Services/`**
    - `ContactService.php` - Core business logic for contact management.
  - **`Console/`**
    - **`Commands/`**
      - `ManageContactsCommand.php` - Provides CLI-based contact management.
  - **`Models/`**
    - `Contact.php` - Defines the Eloquent model for contacts.

- **`database/`**
  - **`migrations/`** - Schema definitions for the database.
  - **`seeders/`** - Initial seed data for testing.

- **`routes/`**
  - `api.php` - Defines API routes for the module.
  - `console.php` - Defines CLI commands for contact operations.

- **`docs/`**
  - `api.md` - OpenAPI/Swagger documentation for APIs.
  - `cli.md` - Instructions for CLI commands.
  - `readme.md` - Project rationale and technical decisions.

---

Refer to the other markdown files for detailed documentation.
