# Architecture Decisions Record

## 1. Database Access Layer
**Date:** 2024-01-01

**Decision:** Use Doctrine DBAL as the primary database access layer.

**Context:**
- Need robust database abstraction layer
- Must support complex queries and transactions
- Should provide type safety and query building
- Must work well with PostgreSQL features

**Consequences:**
- Better type safety and query validation
- More explicit database operations
- Reduced magic methods and hidden behavior
- Better integration with PostgreSQL features
- Learning curve for developers used to Eloquent

## 2. Multi-tenancy Implementation
**Date:** 2024-01-01

**Decision:** Use stancl/tenancy with Doctrine DBAL integration.

**Context:**
- Need reliable multi-tenant support
- Must work with Doctrine DBAL
- Should support domain-based tenant identification
- Must handle database separation effectively

**Consequences:**
- Clean separation of tenant data
- Explicit tenant context in queries
- More complex setup and configuration
- Need for custom Doctrine tenant resolvers

## 3. API Documentation
**Date:** 2024-01-01

**Decision:** Use OpenAPI/Swagger for API documentation.

**Context:**
- Need standardized API documentation
- Must be maintainable and auto-generated
- Should support API testing tools

**Consequences:**
- Self-documenting APIs
- Interactive API documentation
- Additional development overhead for annotations
- Improved API consistency

## 4. Contact Management Implementation
**Date:** 2024-01-02

**Decision:** Implement comprehensive contact management with phone/email validation and call tracking

**Context:**
- Need to manage contacts with multiple phones and emails
- Must support AU/NZ phone number formats
- Should track call history and status
- Must maintain audit logs
- Must enforce tenant isolation

**Consequences:**
- Robust phone number validation for AU/NZ
- Configurable limits for phones/emails
- Complete audit trail of all changes
- Proper soft deletion support
- Comprehensive test coverage

## 5. Testing Strategy
**Date:** 2024-01-02

**Decision:** Implement multi-layer testing approach with unit, integration, and performance tests

**Context:**
- Need comprehensive test coverage
- Must verify tenant isolation
- Should test performance under load
- Must validate business rules

**Consequences:**
- High test coverage across all layers
- Performance benchmarks established
- Tenant isolation verified
- Increased development time
- Better code quality assurance

## 6. Configuration Management
**Date:** 2024-01-02

**Decision:** Use environment-based configuration with sensible defaults

**Context:**
- Need flexible configuration
- Must support multiple environments
- Should be easily overridable
- Must maintain security

**Consequences:**
- Environment-specific settings
- Configurable limits and rules
- Easy local development
- Clear configuration documentation

## 8. PostgreSQL as Primary Database
**Date:** 2024-03-19

**Decision:** Use PostgreSQL as the primary database for production environments

**Context:**
- Need robust multi-tenant data isolation
- Complex querying requirements for contacts
- JSON storage for audit logs
- Array types for phone/email collections
- Performance considerations for large datasets

**Consequences:**
- Better performance for multi-tenant queries
- More efficient JSON operations
- Native array type support
- More complex deployment requirements
- SQLite remains for testing only

## 9. Remove Eloquent Factory
**Date:** 2024-03-19

**Decision:** Remove ContactFactory and use Doctrine's test data creation

**Context:**
- We're using Doctrine DBAL/ORM for database access
- The existing ContactFactory was using Eloquent patterns
- Test data creation should be consistent with our data access layer

**Consequences:**
- More consistent test data creation using Doctrine
- Removed unnecessary Eloquent dependencies
- Tests will use repository pattern for data creation
- Better alignment with chosen architecture
