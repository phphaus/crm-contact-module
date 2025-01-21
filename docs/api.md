# API Documentation

## Overview
This document provides detailed information on the API endpoints for managing contacts in the CRM Contact Module. The API is designed with RESTful principles and includes OpenAPI specifications.

## Directory Structure
```
src/
├── Config/
│   └── crm-contact.php          # Module configuration
├── Console/
│   └── Commands/                # CLI commands
├── Contracts/                   # Service interfaces and DTOs
├── DataTransfer/               # Data transfer objects
├── Database/
│   ├── Migrations/             # Database migrations
│   └── Seeders/               # Database seeders
├── Entities/                   # Doctrine entities
├── Exceptions/                 # Custom exceptions
├── Http/
│   ├── Controllers/           # HTTP controllers
│   ├── Middleware/           # Custom middleware
│   ├── Requests/            # Form requests
│   └── Responses/          # API responses
├── Providers/               # Service providers
├── Routes/                 # Route definitions
└── Services/              # Business logic services
```

## Key Components
1. **Controllers**: `src/Http/Controllers/ContactController.php`
2. **Responses**: `src/Http/Responses/ApiResponse.php`, `src/Http/Responses/ContactResponse.php`
3. **Services**: `src/Services/ContactService.php`
4. **DTOs**: `src/DataTransfer/ContactSearchCriteria.php`
5. **Contracts**: `src/Contracts/ApiResponseInterface.php`, `src/Contracts/CallResponseInterface.php`

## Base URL
The base URL for all API requests:
```
http://{host}/api/v1
```

## Endpoints

### 1. List Contacts
**Endpoint:** `GET /contacts`

**Description:** Retrieves all contacts. Supports optional filters for searching.

**Query Parameters:**
- `phone` (string): Filter by phone number.
- `email_domain` (string): Filter by email domain.
- `page` (integer): Page number for pagination
- `per_page` (integer): Items per page (max: 100)

---

### 2. Retrieve Contact
**Endpoint:** `GET /contacts/{id}`

**Description:** Retrieves a specific contact by ID.

---

### 3. Create Contact
**Endpoint:** `POST /contacts`

**Description:** Creates a new contact.

---

### 4. Update Contact
**Endpoint:** `PUT /contacts/{id}`

**Description:** Updates an existing contact.

---

### 5. Delete Contact
**Endpoint:** `DELETE /contacts/{id}`

**Description:** Deletes a contact.

---

### 6. Call Contact
**Endpoint:** `POST /contacts/{id}/call`

**Description:** Simulates calling a contact.

Refer to the readme for more details.

## Response Structure

### Success Response
```json
{
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "phones": [
            { "number": "+61412345678" }
        ],
        "emails": [
            { "email": "john@example.com" }
        ]
    },
    "meta": {
        "timestamp": "2024-03-19T12:00:00Z"
    }
}
```

### Error Response
```json
{
    "error": {
        "code": "contact_not_found",
        "message": "Contact not found",
        "details": {
            "id": 123
        }
    },
    "meta": {
        "timestamp": "2024-03-19T12:00:00Z"
    }
}
```

### Pagination Response
```json
{
    "data": [...],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 50,
        "last_page": 4
    },
    "links": {
        "first": "http://api/v1/contacts?page=1",
        "last": "http://api/v1/contacts?page=4",
        "prev": null,
        "next": "http://api/v1/contacts?page=2"
    }
}
```
