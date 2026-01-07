# Multi-Tenant SaaS CRM

A production-ready multi-tenant CRM platform built with Laravel 12 and React, featuring advanced subscription management, role-based access control, and comprehensive compliance features.

## 🚀 Features

### Core Features
- **Multi-Tenancy**: Subdomain-based tenant isolation with single database strategy
- **Role-Based Access Control**: Admin, Manager, and Staff roles with granular permissions
- **Subscription Management**: Stripe-powered billing with trial periods and plans
- **CRM Functionality**: Contacts, Leads, Deals, and Tasks management
- **Team Management**: User invitations and role assignments

### Advanced Features
- **GDPR Compliance**: Data export, deletion, and consent tracking
- **Data Retention**: Automated cleanup policies for old data
- **Advanced Caching**: Multi-level caching for optimal performance
- **Email Templates**: Transactional emails for invitations and notifications
- **Security**: Hard subscription enforcement and tenant data isolation

## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Database**: MySQL/PostgreSQL
- **Caching**: Redis
- **Queues**: Laravel Queues
- **Payment Processing**: Stripe
- **Testing**: PestPHP

### Frontend
- **Framework**: React 18 + TypeScript
- **Routing**: React Router v6
- **Styling**: Tailwind CSS
- **HTTP Client**: Axios
- **Icons**: Heroicons

## 🏗 Architecture

### Multi-Tenancy Strategy
Single Database with tenant_id on all tenant-specific tables
Tenant Resolution via subdomain middleware
Global Scopes for automatic data isolation


### Security & Compliance
- **Data Isolation**: Tenant-specific queries via Global Scopes
- **Subscription Enforcement**: Hard blocking when expired
- **GDPR Ready**: Export/deletion endpoints
- **Role-Based Access**: Policies for each resource type

## 📋 Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+
- npm
- MySQL
- Redis
- Mailpit (for local development)

### Database Setup

# Configure your database in .env
# Then run migrations
php artisan migrate
php artisan db:seed --class=PlanSeeder


6. Local Development Setup with Laravel Herd
Using Laravel Herd (Recommended)
Install Laravel Herd from https://herd.laravel.com
Add your project to Herd
Configure subdomain routing in Herd settings:
Main domain: app.test
Wildcard subdomains: *.app.test
Manual Configuration
If not using Herd, configure your local server for:

Main domain: http://app.test
Wildcard subdomains: http://*.app.test


7. Environment Variables
Update .env with:
APP_URL=http://app.test
TENANT_SUBDOMAIN_SUFFIX=.app.test

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_tenant_crm
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Stripe
STRIPE_KEY=pk_test_your_key
STRIPE_SECRET=sk_test_your_secret
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# Mail (using Mailpit for local development)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@app.test"

# Data Retention (days)
DATA_RETENTION_CONTACTS_DAYS=365
DATA_RETENTION_LEADS_DAYS=365
DATA_RETENTION_DEALS_DAYS=365
DATA_RETENTION_TASKS_DAYS=180


8. Running the Application

Development Mode
# Terminal 1: Run Laravel server
php artisan serve

# Terminal 2: Build and watch assets
npm run dev

# Terminal 3: Run queue worker (for emails and jobs)
php artisan queue:work

Production Build
npm run build
php artisan config:cache
php artisan route:cache
php artisan event:cache

9. Testing

# Run all tests
./vendor/bin/pest

# Run specific test suites
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Unit

# Generate coverage report
./vendor/bin/pest --coverage-html=coverage


10. Project Structure

app/
├── Models/                 # Eloquent models
│   ├── Tenant/            # Tenant-specific models
│   └── ...
├── Http/
│   ├── Controllers/       # API controllers
│   ├── Middleware/        # Custom middleware
│   └── ...
├── Services/              # Business logic services
│   ├── Billing/          # Stripe integration
│   ├── Compliance/       # GDPR features
│   ├── Caching/          # Advanced caching
│   └── ...
├── Policies/              # Authorization policies
└── ...

resources/
├── js/                   # React frontend
│   ├── Components/       # React components
│   ├── Contexts/         # React contexts
│   └── ...
├── views/                # Email templates
└── ...

routes/
├── api.php               # API routes
└── ...

tests/
├── Feature/             # Feature tests
└── Unit/               # Unit tests



🌐 API Endpoints
Public Endpoints
POST /api/register - Company registration
POST /api/login - User login
GET /api/invitations/{token} - Accept team invitation
POST /api/invitations/{token} - Complete team invitation
Authenticated Endpoints (require token)
GET /api/me - Get current user
POST /api/logout - Logout user
GET /api/compliance/export - GDPR data export
DELETE /api/compliance/delete-account - GDPR account deletion
Tenant-Specific Endpoints (require subdomain)
GET /api/dashboard - Dashboard statistics
GET/POST/PUT/DELETE /api/contacts - Contact management
GET/POST/PUT/DELETE /api/leads - Lead management
GET/POST/PUT/DELETE /api/deals - Deal management
GET/POST/PUT/DELETE /api/tasks - Task management
GET/POST/PUT /api/billing/* - Billing management
GET/POST/PUT /api/users/* - User management

🚀 Usage Guide

1. Company Registration
Visit http://app.test/register
Enter company details and subdomain
Complete registration flow
You'll be redirected to your tenant subdomain

2. User Management
Admin users can invite team members via /team page
Invited users receive email with registration link
Admins can assign roles (Admin, Manager, Staff)

3. CRM Operations
Contacts: Store and manage customer information
Leads: Track potential customers through pipeline
Deals: Manage sales opportunities
Tasks: Organize activities and follow-ups

4. Subscription Management
-View current plan and billing status
-Upgrade/downgrade between plans
-Cancel subscription if needed
-Automatic trial period for new companies

🔒 Security & Compliance
-Data Protection
-Tenant Isolation: Each tenant's data is completely isolated
-Subscription Enforcement: Hard blocking when subscription expires
-Role-Based Access: Granular permissions for each user role
-GDPR Compliance: Data export and deletion features

Privacy Features
-Consent Tracking: Track user consents for different purposes
-Data Retention: Automated cleanup of old data
-Audit Logging: Track important user actions

🧪 Testing

Test Coverage
-Feature Tests: End-to-end testing of user flows
-Unit Tests: Individual component testing
-Integration Tests: API and service integration
-Security Tests: Authorization and data isolation

# All tests
./vendor/bin/pest

# With coverage
./vendor/bin/pest --coverage

# Specific test
./vendor/bin/pest tests/Feature/CRMTest.php


🚀 Deployment

Production Requirements
- PHP 8.4+ with required extensions
- MySQL/PostgreSQL database
- Redis for caching and queues
- Queue worker process
- SSL certificates for HTTPS
- Proper subdomain DNS configuration

Deployment Steps
- Upload application files
- Install dependencies: composer install --optimize-autoloader --no-dev
- Build frontend: npm run build
- Configure environment variables
- Run migrations: php artisan migrate
- Cache configuration: php artisan config:cache
- Set up queue workers
- Configure subdomain routing

🤝 Contributing
- Fork the repository
- Create a feature branch (git checkout -b feature/amazing-feature)
- Make changes and add tests
- Run tests (./vendor/bin/pest)
- Commit changes (git commit -m 'Add amazing feature')
- Push to the branch (git push origin feature/amazing-feature)
- Open a Pull Request

📄 License
This project is open-source software licensed under the MIT license.

🆘 Support
For support, please open an issue in the GitHub repository.

### **2. Technical Documentation**

**`docs/technical-documentation.md`**
```markdown
# Technical Documentation

## Architecture Overview

### Multi-Tenancy Implementation

#### Database Strategy
- **Single Database**: Uses one database with tenant isolation
- **tenant_id Column**: Added to all tenant-specific tables
- **Global Scopes**: Automatically applies tenant filter to queries
- **Middleware**: Resolves tenant from subdomain

#### Tenant Resolution Flow
1. Request comes in with subdomain (e.g., `company.app.test`)
2. `TenantMiddleware` extracts subdomain
3. Looks up company in cache/database
4. Sets tenant context for the request
5. Global scopes automatically filter queries

#### Code Implementation
```php
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $tenant = app('current_tenant');
        if ($tenant) {
            $builder->where('company_id', $tenant->id);
        }
    }
}


Authentication & Authorization

Sanctum Configuration

- SPA Mode: Cookie-based authentication
- CSRF Protection: Enabled for security
- Token Management: Personal access tokens
- Stateless: API-only authentication

Role-Based Access Control
- Policies: One policy per model
- Middleware: Role-based access checks
- Permissions: Defined per role type
- Hierarchical: Admin > Manager > Staff


Subscription Management

Stripe Integration

- Customer Creation: Automatic when company registers
- Subscription Handling: Through Laravel Cashier
- Webhooks: Real-time subscription updates
- Proration: Smart billing adjustments

 Hard Enforcement Strategy
- Middleware: Blocks access when subscription expires
- Immediate Effect: No grace period
- API Responses: 402 Payment Required
- User Experience: Clear messaging


Advanced Caching Strategy

Multi-Level Caching
- Level 1: Tenant Resolution (5 minutes)
- Level 2: Subscription Status (1 minute)
- Level 3: User Permissions (15 minutes)
- Level 4: CRM Data (Variable TTL)

Cache Service Implementation
class AdvancedCacheService
{
    public function cacheTenantResolution(string $subdomain, $tenant, int $ttl = 300): void
    {
        Cache::put("tenant:{$subdomain}", $tenant, $ttl);
    }

    public function cacheSubscriptionStatus(int $companyId, bool $status, int $ttl = 60): void
    {
        Cache::put("tenant:{$companyId}:subscribed", $status, $ttl);
    }
}



GDPR Compliance Implementation

Data Export

- JSON Format: Structured data export
- All Related Data: User, contacts, leads, deals, tasks
- Sensitive Filtering: Passwords and tokens excluded
- File Generation: Temporary file with download

Data Deletion
- Anonymization: Data is anonymized rather than deleted
- Transaction Safety: Database transactions ensure consistency
- Related Cleanup: All related records handled
- Audit Trail: Deletion logged for compliance

Consent Tracking
- Purpose-Based: Different consent types
- Time Stamped: When consent was given/revoked
- Flexible: Easy to add new consent purposes
- Queryable: Check consent status anytime

API Authentication Flow

React ↔ Laravel Communication
- User logs in via React form
- Credentials sent to Laravel API
- Laravel creates Sanctum token
- Token stored in localStorage
- Token added to axios headers
- Subsequent requests include token


Error Handling Strategy

HTTP Status Codes
- 200: Success
- 401: Unauthorized
- 402: Subscription Expired
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error


Performance Optimization

Database Optimization
- Indexing: Proper indexes on foreign keys and search fields
- Eager Loading: Prevent N+1 queries
- Pagination: Efficient data loading
- Caching: Reduce database load

Frontend Optimization
- Code Splitting: Dynamic imports
- Memoization: React.memo and useMemo
- Lazy Loading: Route-level code splitting
- Caching: Browser caching strategies

Security Measures
Input Validation
- Laravel Validation: Built-in validation rules
- Sanitization: Input cleaning
- XSS Prevention: Output escaping
- SQL Injection: Query parameterization

Rate Limiting
-API Throttling: Per-user rate limits
-Brute Force Protection: Login attempt limits
-DDoS Prevention: Infrastructure-level protection
-Testing Strategy

Test Types
-Unit Tests: Individual functions/methods
-Feature Tests: User flows and interactions
-Integration Tests: API and service integration
-Security Tests: Authorization and data isolation


Deployment Considerations

Production Optimizations
-OPcache: PHP opcode caching
-Autoloader: Optimized class loading
-Configuration: Cached configs and routes
-Assets: Minified and versioned

Environment Configuration
-Environment Variables: Secure credential management
-Caching: Production cache drivers
-Queue Workers: Background job processing
-SSL: HTTPS enforcement

This technical documentation covers the core architectural decisions and implementation details of the multi-tenant SaaS CRM platform.


### **3. User Guide**

**`docs/user-guide.md`**
```markdown
# User Guide

## Getting Started

### Company Registration
1. Go to `http://app.test/register`
2. Fill in company information:
   - **Company Name**: Your organization name
   - **Subdomain**: Unique identifier for your tenant (e.g., `mycompany` creates `mycompany.app.test`)
   - **Email**: Owner/administrator email
   - **Password**: Secure password
3. Click "Create Account"
4. You'll be automatically redirected to your tenant subdomain

### First Login
- After registration, you'll be logged in automatically
- If returning later, visit your tenant URL: `https://your-subdomain.app.test`
- Use the email and password you registered with

## Dashboard Overview

### Statistics Cards
- **Total Contacts**: Number of contacts in your CRM
- **Total Leads**: Number of leads being tracked
- **Total Deals**: Number of deals in your pipeline
- **Total Tasks**: Number of tasks assigned

### Recent Activity
- **Recent Contacts**: Latest contacts added to your CRM
- **Upcoming Tasks**: Tasks due soon for your team

### Subscription Status
- Shows your current plan and subscription status
- Alerts if subscription is expiring or has expired

## CRM Management

### Contacts
**Adding a Contact:**
1. Click "Contacts" in the sidebar
2. Click "Add Contact" button
3. Fill in contact details:
   - First Name
   - Last Name  
   - Email Address
   - Phone Number
   - Company Name
   - Position
   - Source (how you found them)
   - Notes
   - Tags (for categorization)
   - Status (active, inactive, lead, client)

**Managing Contacts:**
- View contact list with search/filter
- Edit contact information
- Delete contacts (admin only)

### Leads
**Creating a Lead:**
1. Click "Leads" in the sidebar
2. Click "Add Lead" button
3. Select an existing contact or create a new one
4. Fill in lead details:
   - Title
   - Description
   - Value (potential deal size)
   - Source
   - Status (new, contacted, qualified, proposal, scheduled, closed won/lost)
   - Priority (low, medium, high, urgent)
   - Assigned To (team member)
   - Estimated Close Date
   - Pipeline Stage

**Lead Pipeline:**
- Track leads through different stages
- Convert leads to deals when qualified
- Monitor conversion rates

### Deals
**Creating a Deal:**
1. Click "Deals" in the sidebar
2. Click "Add Deal" button
3. Select contact and optionally lead
4. Fill in deal details:
   - Title
   - Description
   - Value
   - Currency
   - Status (proposed, negotiating, approved, closed won/lost)
   - Probability (% chance of closing)
   - Estimated Close Date
   - Actual Close Date
   - Pipeline Stage
   - Assigned To

**Closing Deals:**
- Mark deals as "closed won" when successful
- Mark as "closed lost" when unsuccessful
- Track deal values and success rates

### Tasks
**Creating a Task:**
1. Click "Tasks" in the sidebar
2. Click "Add Task" button
3. Fill in task details:
   - Title
   - Description
   - Assigned To (team member)
   - Due Date
   - Priority (low, medium, high, urgent)
   - Status (pending, in progress, completed, cancelled)
   - Related To (contact, lead, or deal)

**Managing Tasks:**
- Assign tasks to team members
- Track task completion
- Filter tasks by assignee or status

## User Management

### Inviting Team Members (Admin Only)
1. Click "Team" in the sidebar
2. Click "Invite Member" button
3. Enter team member's email
4. Select role:
   - **Admin**: Full access to all features
   - **Manager**: Can manage leads, deals, tasks; manage other users
   - **Staff**: Can view and update assigned records only

### Managing User Roles
- Admins can promote/demote users
- Role changes take effect immediately
- Users maintain access to previously assigned records

### Profile Management
1. Click on your profile picture/name in bottom left
2. Select "Your Profile"
3. Update personal information:
   - Name
   - Email
   - Phone
   - Job Title
   - Bio
   - Settings

### Changing Password
1. Go to "Your Profile" page
2. Find password change section
3. Enter current password
4. Enter new password twice
5. Click "Update Password"

## Billing & Subscription

### Viewing Current Plan
1. Click "Billing" in the sidebar
2. See current plan details
3. Check subscription status
4. View billing history

### Changing Plans
1. Go to "Billing" page
2. Click "Change Plan" button
3. Select new plan from available options
4. Confirm plan change
5. Billing will be adjusted prorated

### Payment Information
- Payment details managed securely by Stripe
- Automatic renewal based on subscription interval
- Invoice history available for review

## Compliance Features

### Data Export
1. Go to "Your Profile" page
2. Look for GDPR section
3. Click "Export My Data"
4. Download JSON file containing all your information

### Account Deletion
⚠️ **Warning**: This action is irreversible
1. Go to "Your Profile" page
2. Look for GDPR section
3. Click "Delete My Account"
4. Confirm deletion
5. Account and data will be anonymized

### Consent Management
- Marketing emails: Opt in/out via profile settings
- Analytics: Managed through browser preferences
- Essential cookies: Required for platform operation

## Troubleshooting

### Common Issues

**Can't Access Tenant Subdomain:**
- Check that subdomain matches what you registered
- Ensure your hosts file or DNS points to the correct server
- Clear browser cache

**Login Problems:**
- Verify email and password are correct
- Check if account is still active
- Try resetting password

**Subscription Expired Message:**
- Go to billing page to renew subscription
- Contact administrator if you believe this is an error

### Getting Help
- Contact your system administrator
- Check billing status if experiencing access issues
- Review subscription requirements with your plan

## Best Practices

### Data Management
- Keep contact information up to date
- Use tags consistently for organization
- Regularly review and clean up old data
- Assign tasks appropriately to team members

### Team Collaboration
- Clearly assign ownership of leads and deals
- Use task descriptions for context
- Keep notes updated for team visibility
- Respect role-based permissions

### Security
- Use strong, unique passwords
- Don't share login credentials
- Log out when using shared computers
- Report suspicious activity immediately

This user guide provides comprehensive information for using the multi-tenant SaaS CRM platform effectively.


class UserService
{
    public function createUser(array $userData): User
    {
        // Business logic here
    }
}

Repository Pattern

Data access should be abstracted through repositories:

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
}

Event-Driven Architecture
Use Laravel events and listeners for decoupled operations:

// Event
class UserRegistered
{
    public function __construct(public User $user) {}
}

// Listener
class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
    {
        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }
}

Testing Guidelines

Test Organization
- Unit Tests: Individual class/function tests in tests/Unit/
- Feature Tests: User flow tests in tests/Feature/
- Integration Tests: API/service integration tests in tests/Feature/

Pest Test Structure
test('user can register', function () {
    // Arrange
    $userData = ['name' => 'John Doe', 'email' => 'john@example.com'];
    
    // Act
    $response = $this->post('/api/register', $userData);
    
    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});


Test Coverage Goals

- Minimum 80% code coverage
- Test all public methods
- Test edge cases and error conditions
- Test authorization and validation

Database Design

Migration Guidelines
- Always backup production before running migrations
- Write reversible migrations
- Use meaningful migration names
- Add indexes for frequently queried columns

Model Guidelines
- Use factory states for complex model creation
- Implement proper relationships
- Use accessors and mutators for data formatting
- Add proper casts for data types

Seeding Strategy
- Use model factories for test data
- Create specific seeders for production data
- Keep seeds fast and reliable


Frontend Development

React Component Structure
resources/js/
├── Components/
│   ├── Auth/
│   ├── CRM/
│   ├── Layout/
│   └── Shared/
├── Contexts/
├── Hooks/
└── Utils/


State Management
Use React Context for global state
Use React hooks for component state
Consider Redux for complex state management needs

API Integration
Use Axios for HTTP requests
Implement request/response interceptors
Handle loading and error states properly

Deployment
Environment Configuration
Use environment-specific configuration
Secure sensitive data with environment variables
Configure proper logging levels

Database Migrations
Always test migrations locally first
Backup database before deploying
Have rollback plans ready

Asset Compilation
Run npm run build for production
Clear asset cache after deployment
Use versioning for cache busting
Performance Optimization

Database Queries
Use eager loading to prevent N+1 queries
Add proper indexes to frequently queried columns
Use query caching for expensive operations

Caching Strategy
Cache tenant resolution (5 minutes)
Cache subscription status (1 minute)
Cache user permissions (15 minutes)
Cache CRM data as needed

Frontend Optimization
Implement code splitting
Use lazy loading for components
Optimize images and assets
Implement proper memoization

Security Considerations
Input Validation
Validate all user inputs
Sanitize data before storage
Use prepared statements for database queries

Authentication
Use HTTPS in production
Implement proper session management
Use secure password hashing
Implement rate limiting

Authorization
Implement proper role-based access control
Use policies for resource authorization
Validate permissions on every request
Monitoring and Maintenance

Logging
Log important events and errors
Use structured logging
Monitor logs regularly
Set up alerts for critical errors

Backups
Schedule regular database backups
Test backup restoration procedures
Store backups securely

Updates
Keep dependencies updated
Test updates in staging first
Plan for breaking changes
Maintain changelog

Troubleshooting

Common Issues

Subdomain routing: Ensure proper DNS configuration
Caching issues: Clear cache when making changes
Database connections: Check connection settings
Queue workers: Ensure workers are running

Debugging Tips
Use Laravel Telescope for debugging
Enable debug mode in development
Use proper logging levels
Check server logs for errors

This development documentation provides guidelines for maintaining and extending the multi-tenant SaaS CRM platform.

### **6. Environment Setup Guide for macOS**

**`docs/setup-macos.md`**
```markdown
# macOS Setup Guide

## Prerequisites

### Install Homebrew (if not already installed)
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

Laravel Herd Setup (Recommended)
Install Laravel Herd
Download Laravel Herd from https://herd.laravel.com

Install the application
Launch Herd
Configure Herd
Add your project folder to Herd
Click on your project in Herd
Configure the site to use:
PHP 8.4
MySQL
Redis

Set up Subdomain Routing
In Herd, click on your site
Go to "Site Details"
Add wildcard subdomain support:
Main domain: app.test
Wildcard: *.app.test


Project Setup

Clone and Install Dependencies
# Clone the repository
git clone <repository-url>
cd multi-tenant-crm

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install


Environment Configuration
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

Database Setup
# Create MySQL database
mysql -u root -p
CREATE DATABASE multi_tenant_crm;
EXIT;

# Update .env with database credentials
# DB_DATABASE=multi_tenant_crm
# DB_USERNAME=root
# DB_PASSWORD=your_mysql_password

# Run migrations
php artisan migrate

# Seed with default plans
php artisan db:seed --class=PlanSeeder


Testing the Setup

Verify Services
# Check PHP version
php -v

# Check MySQL connection
mysql -u root -p -e "SHOW DATABASES;"

# Check Redis connection
redis-cli ping

# Check if Mailpit is running
curl http://localhost:8025/api/v1/messages


Test Application

Visit http://app.test in your browser
Register a new company
Verify you can access the dashboard
Check that subdomain routing works
Troubleshooting macOS Issues
Common Issues and Solutions

Permission Issues with Composer:
# Fix composer permissions
sudo chown -R $(whoami) ~/.composer

Node.js Memory Issues:
# Increase Node.js memory limit
export NODE_OPTIONS="--max-old-space-size=4096"

# Increase Node.js memory limit
export NODE_OPTIONS="--max-old-space-size=4096"
# Flush DNS cache
sudo dscacheutil -flushcache
sudo killall -HUP mDNSResponder

# Restart networking
sudo ifconfig lo0 down
sudo ifconfig lo0 up


Herd Specific Troubleshooting

Check Herd logs for errors
-Ensure PHP version matches requirements
-Verify site is properly linked
-Restart Herd if needed

Valet Specific Troubleshooting
# Reinstall Valet
valet uninstall
composer global require laravel/valet
valet install

# Re-park sites
valet park


Production Considerations

For Production Deployment
-Use proper SSL certificates
-Configure proper DNS records
-Set up monitoring
-Implement backup strategies
-Use environment-specific configurations

This macOS setup guide provides comprehensive instructions for setting up the multi-tenant SaaS CRM platform on macOS systems.

### **7. Update Package.json for Better Scripts**

**`package.json`** (updated)
```json
{
    "private": true,
    "type": "module",
    "scripts": {
        "dev": "vite",
        "build": "tsc && vite build",
        "preview": "vite preview",
        "test": "vitest",
        "lint": "eslint resources/js --ext .ts,.tsx --report-unused-disable-directives --max-warnings 0",
        "format": "prettier --write resources/js/**/*.{ts,tsx,json,css,scss,md}"
    },
    "devDependencies": {
        "@tailwindcss/forms": "^0.5.2",
        "@types/react": "^18.2.0",
        "@types/react-dom": "^18.2.0",
        "@vitejs/plugin-react": "^4.2.0",
        "autoprefixer": "^10.4.2",
        "axios": "^1.6.0",
        "laravel-vite-plugin": "^1.0.0",
        "postcss": "^8.4.31",
        "react": "^18.2.0",
        "react-dom": "^18.2.0",
        "tailwindcss": "^3.2.0",
        "typescript": "^5.0.2",
        "vite": "^5.0.0",
        "@heroicons/react": "^1.0.6",
        "react-router-dom": "^6.8.0"
    }
}

8. Create .github Directory Structure

.github/FUNDING.yml
github: [your-username]
patreon: your-username
open_collective: your-project
ko_fi: your-username
tidelift: npm/your-package
custom: ['https://paypal.me/your-username']

.github/CONTRIBUTING.md

# Contributing to Multi-Tenant SaaS CRM

First off, thank you for considering contributing to Multi-Tenant SaaS CRM! 

## Code of Conduct

This project and everyone participating in it is governed by the [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

This section guides you through submitting a bug report for Multi-Tenant SaaS CRM. Following these guidelines helps maintainers and the community understand your report, reproduce the behavior, and find related reports.

* Use a clear and descriptive title for the issue
* Describe the exact steps which reproduce the problem
* Provide specific examples to demonstrate the steps
* Describe the behavior you observed after following the steps
* Explain which behavior you expected to see instead
* Include screenshots and animated GIFs if possible

### Suggesting Enhancements

This section guides you through submitting an enhancement suggestion for Multi-Tenant SaaS CRM, including completely new features and minor improvements to existing functionality.

* Use a clear and descriptive title for the issue
* Provide a step-by-step description of the suggested enhancement
* Provide specific examples to demonstrate the steps
* Describe the current behavior and explain which behavior you expected to see instead
* Explain why this enhancement would be useful

### Pull Requests

* Fill in the required template
* Do not include issue numbers in the PR title
* Include screenshots and animated GIFs in your pull request when adding new features
* Make sure your code follows the existing style
* End all files with a newline
* Avoid platform-dependent code

## Styleguides

### Git Commit Messages

* Use the present tense ("Add feature" not "Added feature")
* Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
* Limit the first line to 72 characters or less
* Reference issues and pull requests liberally after the first line

### PHP Styleguide

All PHP must adhere to PSR-12 standards.

### JavaScript Styleguide

All JavaScript must adhere to Airbnb JavaScript Style Guide.

### Documentation Styleguide

* Use Markdown for documentation
* Use semantic versioning