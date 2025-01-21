# Features and Detailed Requirements

## Overview
This document provides detailed requirements for the features outlined in the coding exercise instructions. It highlights data formats, specific validations, transactional behavior, JWT token usage, and additional requirements to ensure the implementation meets the given criteria.

---

## Data Format
### Contact Entity
- **Attributes:**
  - `id` (integer): Unique identifier for the contact.
  - `first_name` (string): First name of the contact.
  - `last_name` (string): Last name of the contact.
  - Relationships:
    - `phones` (array): List of associated phone numbers.
    - `emails` (array): List of associated email addresses.
    - `calls` (array): List of recorded call logs.

### Phone Entity
- **Attributes:**
  - `id` (integer): Unique identifier for the phone.
  - `contact_id` (integer): ID of the associated contact.
  - `number` (string): Phone number in E164 format.

### Email Entity
- **Attributes:**
  - `id` (integer): Unique identifier for the email.
  - `contact_id` (integer): ID of the associated contact.
  - `email` (string): Valid email address.

### Call Entity
- **Attributes:**
  - `id` (integer): Unique identifier for the call log.
  - `contact_id` (integer): ID of the associated contact.
  - `status` (string): Status of the call (e.g., `successful`, `busy`, `failed`).
  - `timestamp` (datetime): Time of the call.

### Example JSON:
#### Contact with Relationships
```json
{
  "id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "phones": [
    { "id": 1, "number": "+61412345678" },
    { "id": 2, "number": "+64212345679" }
  ],
  "emails": [
    { "id": 1, "email": "john.doe@example.com" },
    { "id": 2, "email": "j.doe@workmail.com" }
  ],
  "calls": [
    { "id": 1, "status": "successful", "timestamp": "2023-12-01T12:00:00Z" }
  ]
}
```

---

## JWT Authentication
### Overview
- **Purpose:**
  - Secure endpoints with JSON Web Tokens (JWT).
  - Extract `TenantId` and `UserId` from the token payload.

### Token Requirements
- Must include:
  - `tenant_id` (integer): The ID of the tenant making the request.
  - `user_id` (integer): The ID of the user making the request.

### Middleware
- Use middleware to:
  - Validate the token.
  - Extract `tenant_id` and `user_id`.
  - Inject these values into the request for use in queries and business logic.

### Example Payload:
```json
{
  "tenant_id": 123,
  "user_id": 456,
  "exp": 1672531199
}
```

### Implementation Example
- **Middleware:**
  ```php
  public function handle($request, Closure $next)
  {
      $token = $request->bearerToken();
      $decoded = JWT::decode($token, $key, ['HS256']);

      $request->merge([
          'tenant_id' => $decoded->tenant_id,
          'user_id' => $decoded->user_id,
      ]);

      return $next($request);
  }
  ```

---

## Validation Requirements
### Phone Numbers
- Must follow E164 format.
- Only Australian and New Zealand phone numbers are valid.
- Maximum of 10 phone numbers per contact.
- Example valid formats:
  - `+61412345678` (Australia)
  - `+64212345678` (New Zealand)

### Emails
- Must be valid email addresses.
- Maximum of 10 email addresses per contact.
- Example valid format:
  - `example@domain.com`

### Names
- **First Name:**
  - Cannot be null or empty.
  - Minimum length: 2 characters.
- **Last Name:**
  - Cannot be null or empty.
  - Minimum length: 2 characters.

---

## Transactional Behavior
### Requirements
- Operations involving multiple resources (e.g., creating a contact with multiple phones, emails, and calls) must occur within a single database transaction.
- If an error occurs during any part of the operation, the transaction must roll back to maintain data integrity.

### Example Scenario
- **Action:** Create a contact with phones, emails, and log the creation action in an audit table.
- **Behavior:**
  - Contact creation succeeds.
  - Logging the action fails.
  - **Result:** Both actions are rolled back.

### Implementation
- Use Laravel's database transaction methods:
  ```php
  DB::transaction(function () {
      // Perform multiple operations
  });
  ```

---

## Error Messages
### API Consumers
- Provide clear and informative error messages for API consumers, especially third-party integrators.

### Examples
- **Validation Error:**
  ```json
  {
    "error": "Validation Error",
    "details": {
      "phones": "One or more phone numbers are invalid."
    }
  }
  ```

- **Not Found:**
  ```json
  {
    "error": "Contact Not Found",
    "details": "No contact found with the given ID."
  }
  ```

---

## Additional Requirements
### Search Functionality
- **Filters:**
  - Search by phone number.
  - Search by email domain.
- Must be performant for large datasets.
- Use indexed database columns for filters.

### API Consistency
- Adhere to RESTful conventions for HTTP methods:
  - `GET` for retrieval.
  - `POST` for creation.
  - `PUT` for updates.
  - `DELETE` for deletions.

### Multi-Tenant Support
- Ensure all operations respect tenant isolation.
- Use `stancl/tenancy` package for tenant-aware routing and database configuration.

### Logging and Auditing
- Log all create, update, and delete actions in an audit table.
- Include:
  - Action performed.
  - Timestamp.
  - User performing the action.

### Call Recording
- Maintain a call log for each contact.
- Store call outcomes (`successful`, `busy`, `failed`) and timestamps.
- Expose an endpoint for recording call details:
  - **POST /contacts/{id}/calls**
    - Request Body:
      ```json
      {
        "status": "successful",
        "timestamp": "2023-12-01T12:00:00Z"
      }
      ```
    - Response Codes:
      - `201 Created`: Call log recorded.
      - `400 Bad Request`: Validation error.

---

## Testing Requirements
### Unit Tests
- Test individual methods and functions.

### Feature Tests
- Test full HTTP request-response lifecycle.

### Edge Cases
- Validation failures.
- Transaction rollbacks.

---

## Documentation
- **Swagger/OpenAPI:** Provide comprehensive API documentation.
- **README:** Include usage examples for both API and CLI.
