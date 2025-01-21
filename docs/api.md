# API Documentation

## Overview
This document provides detailed information on the API endpoints for managing contacts in the CRM module. The API is designed with RESTful principles and includes OpenAPI specifications.

---

## Base URL
The base URL for all API requests:
```
http://{host}/api/v1
```

---

## Endpoints

### 1. List Contacts
**Endpoint:** `GET /contacts`

**Description:** Retrieves all contacts. Supports optional filters for searching.

**Query Parameters:**
- `phone` (string): Filter by phone number.
- `email_domain` (string): Filter by email domain.

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
