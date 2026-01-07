

# API Documentation

## Authentication

### Token-Based Authentication
All authenticated endpoints require a Bearer token in the Authorization header:


Authorization: Bearer {token}

### Public Endpoints (No Authentication)

#### Register New Company

POST /api/register

**Request Body:**
```json
{
    "company_name": "string",
    "subdomain": "string",
    "email": "email",
    "password": "string",
    "password_confirmation": "string"
}

Response:
{
    "message": "Company created successfully",
    "company": {},
    "user": {},
    "token": "string",
    "subdomain": "string",
    "redirect_url": "string"
}

Login:
POST /api/login

Request Body:
{
    "email": "email",
    "password": "string"
}

Response:
{
    "message": "Login successful",
    "user": {},
    "token": "string",
    "company": {},
    "role": "string",
    "redirect_url": "string"
}


Authenticated Endpoints (Require Token)

Get Current User
GET /api/me

Response:
{
    "user": {},
    "company": {},
    "role": "string"
}

Logout
POST /api/logout

Rsponse:
{
    "message": "Logged out successfully"
}

Tenant-Specific Endpoints (Require Subdomain)

Dashboard Statistics
GET /api/dashboard

Response:
{
    "stats": {
        "contacts": 0,
        "leads": 0,
        "deals": 0,
        "tasks": 0
    },
    "recent": {
        "contacts": [],
        "leads": [],
        "deals": [],
        "tasks": []
    },
    "upcoming_tasks": [],
    "pipeline": [],
    "conversion_rate": 0
}


Contacts Management
GET /api/contacts
POST /api/contacts
GET /api/contacts/{id}
PUT /api/contacts/{id}
DELETE /api/contacts/{id}

Contact Object:
json:
{
    "id": 1,
    "first_name": "string",
    "last_name": "string",
    "email": "email",
    "phone": "string",
    "company_name": "string",
    "position": "string",
    "source": "string",
    "notes": "string",
    "tags": ["tag1", "tag2"],
    "status": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
}


Leads Management
GET /api/leads
POST /api/leads
GET /api/leads/{id}
PUT /api/leads/{id}
DELETE /api/leads/{id}
POST /api/leads/{id}/convert

json:
{
    "id": 1,
    "contact_id": 1,
    "title": "string",
    "description": "string",
    "value": 0,
    "source": "string",
    "status": "string",
    "priority": "string",
    "assigned_to": 1,
    "estimated_close_date": "date",
    "pipeline_stage": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
}


Lead Object:

json
G{
    "id": 1,
    "contact_id": 1,
    "title": "string",
    "description": "string",
    "value": 0,
    "source": "string",
    "status": "string",
    "priority": "string",
    "assigned_to": 1,
    "estimated_close_date": "date",
    "pipeline_stage": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
}


Deals Management
GET /api/deals
POST /api/deals
GET /api/deals/{id}
PUT /api/deals/{id}
DELETE /api/deals/{id}
POST /api/deals/{id}/close}


Deal Object:

json
{
    "id": 1,
    "contact_id": 1,
    "lead_id": 1,
    "title": "string",
    "description": "string",
    "value": 0,
    "currency": "string",
    "status": "string",
    "probability": 0,
    "estimated_close_date": "date",
    "actual_close_date": "date",
    "pipeline_stage": "string",
    "assigned_to": 1,
    "created_at": "datetime",
    "updated_at": "datetime"
}
Tasks Management
GET /api/tasks
POST /api/tasks
GET /api/tasks/{id}
PUT /api/tasks/{id}
DELETE /api/tasks/{id}
POST /api/tasks/{id}/complete
POST /api/tasks/{id}/incomplete

Task Object:

json
{
    "id": 1,
    "title": "string",
    "description": "string",
    "assigned_to": 1,
    "due_date": "datetime",
    "priority": "string",
    "status": "string",
    "related_to_type": "string",
    "related_to_id": 1,
    "created_at": "datetime",
    "updated_at": "datetime"
}


Billing Management
GET /api/billing/plans
POST /api/billing/subscribe
POST /api/billing/unsubscribe
POST /api/billing/change-plan
GET /api/billing/subscription
GET /api/billing/invoices
POST /api/billing/sync-status

User Management
GET /api/users
GET /api/users/{id}
PUT /api/users/{id}
PUT /api/users/{id}/role
PUT /api/users/{id}/deactivate
PUT /api/users/{id}/activate
DELETE /api/users/{id}

Team Management
POST /api/team/invite

Error Responses
Validation Error (422)
{
    "message": "Validation failed",
    "errors": {
        "field_name": ["Error message"]
    }
}

Unauthorized (401)
{
    "message": "Unauthenticated"
}
json
Subscription Expired (402)
{
    "message": "Subscription expired. Please renew your subscription to continue.",
    "status": "subscription_expired"
}
Forbidden (403)
{
  {
    "message": "This action is unauthorized."
}
}
Not Found (404)
{
    "message": "Not Found"
}

Rate Limiting
API requests are rate-limited to prevent abuse. Exceeding limits will result in a 429 status code.

CORS
The API supports CORS for secure cross-origin requests. Allowed origins are configured in the backend.


### **5. Development Documentation**

**`docs/development.md`**
```markdown
# Development Documentation

## Project Setup

### Prerequisites
- PHP 8.4+ with extensions: [list required extensions]
- Composer
- Node.js 18+
- npm/yarn
- MySQL/PostgreSQL
- Redis
- Mailpit (for local email testing)

### Initial Setup
```bash
# Clone repository
git clone <repo-url>
cd multi-tenant-crm

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed --class=PlanSeeder



