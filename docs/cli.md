# CLI Documentation

## Overview
This document provides instructions for managing contacts using the Laravel CLI. The `contacts:manage` command supports creating, updating, listing, and deleting contacts via the command line.

---

## Command Structure
The CLI command is defined as:
```
php artisan contacts:manage {operation} {--id=} {--name=} {--phone=} {--email=}
```

---

## Supported Operations

### 1. List Contacts
**Command:**
```
php artisan contacts:manage list
```

### 2. Create Contact
**Command:**
```
php artisan contacts:manage create --name="John Doe" --phone="+61412345678" --email="john.doe@example.com"
```

Refer to the main documentation for full instructions.
