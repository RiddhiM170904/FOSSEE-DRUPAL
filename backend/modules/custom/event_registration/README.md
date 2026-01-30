# Event Registration Module for Drupal 10

A custom Drupal 10 module that provides a complete event registration system with email notifications, admin management, and CSV export functionality.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Database Tables](#database-tables)
- [URLs](#urls)
- [Technical Details](#technical-details)
- [Validation Logic](#validation-logic)
- [Email Notifications](#email-notifications)
- [Permissions](#permissions)
- [Troubleshooting](#troubleshooting)

## Features

- **Event Configuration**: Administrators can create and configure events with registration dates
- **Public Registration Form**: Users can register for events with AJAX-powered dynamic dropdowns
- **Duplicate Prevention**: Prevents duplicate registrations based on email + event date
- **Email Notifications**: Sends confirmation emails to users and administrators
- **Admin Dashboard**: View all registrations with filtering and export capabilities
- **CSV Export**: Export registration data for analysis
- **Custom Permissions**: Role-based access control for different features

## Requirements

- Drupal 10.x
- PHP 8.1 or higher
- MySQL/MariaDB database

## Installation

### Step 1: Copy Module Files

Copy the `event_registration` directory to your Drupal installation:

```bash
cp -r event_registration /path/to/drupal/modules/custom/
```

### Step 2: Import Database Schema

You can either:

**Option A: Use Drupal's module installation (Recommended)**

1. Navigate to **Extend** (`/admin/modules`)
2. Find "Event Registration" in the module list
3. Check the checkbox next to it
4. Click "Install"
5. Drupal will automatically create the database tables

**Option B: Import SQL file manually**

If you prefer to import the SQL schema manually:

```bash
mysql -u your_username -p your_database_name < event_registration_schema.sql
```

### Step 3: Clear Cache

Clear Drupal's cache to recognize the new module:

```bash
drush cr
```

Or via the admin interface: **Configuration** → **Development** → **Performance** → **Clear all caches**

### Step 4: Set Permissions

1. Go to **People** → **Permissions** (`/admin/people/permissions`)
2. Assign the following permissions to appropriate roles:
   - **Administer Event Registration**: For site administrators
   - **Configure Events**: For event managers
   - **View Event Registrations**: For staff who need to view registrations

## Configuration

### Admin Email Settings

1. Navigate to `/admin/config/event-registration/settings`
2. Enter the admin notification email address
3. Enable/disable admin notifications as needed
4. Click "Save configuration"

**Configuration Details:**
- Admin email is stored using Drupal's Config API
- No hard-coded values are used
- Settings can be modified at any time

## Usage

### Creating Events (Admin)

1. Navigate to `/admin/event-registration/event-config`
2. Fill in the event details:
   - **Event Name**: Name of the event (alphanumeric only, no special characters)
   - **Category**: Select from: Online Workshop, Hackathon, Conference, One-day Workshop
   - **Event Date**: Date when the event will occur
   - **Registration Start Date**: When registration opens
   - **Registration End Date**: When registration closes
3. Click "Save Event Configuration"

**Important Notes:**
- Registration end date must be after the start date
- Event date cannot be before the registration start date
- Only events with active registration periods will appear in the public form

### Public Registration

1. Users navigate to `/event-registration/register`
2. Available events are displayed based on current date
3. Fill in the registration form:
   - **Full Name**: Alphanumeric characters only
   - **Email Address**: Valid email format
   - **College Name**: Alphanumeric characters only
   - **Department**: Alphanumeric characters only
   - **Category**: Select from available categories (AJAX-updated)
   - **Event Date**: Select from dates for chosen category (AJAX-updated)
   - **Event Name**: Select from events for chosen category and date (AJAX-updated)
4. Submit the form
5. Confirmation email is sent automatically

### Viewing Registrations (Admin)

1. Navigate to `/admin/event-registration/registrations`
2. Select an **Event Date** from the dropdown
3. Select an **Event Name** (filtered by selected date via AJAX)
4. View the registration table with:
   - Total participant count
   - Detailed registration information
   - Submission timestamps
5. Click **Export to CSV** to download the data

## Database Tables

### event_config

Stores event configuration details.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (Primary Key) | Unique event configuration ID |
| event_name | VARCHAR(255) | Name of the event |
| event_category | VARCHAR(100) | Category (Online Workshop, Hackathon, etc.) |
| event_date | VARCHAR(20) | Date of the event |
| registration_start_date | VARCHAR(20) | Registration opening date |
| registration_end_date | VARCHAR(20) | Registration closing date |
| created | INT | Unix timestamp of creation |

**Indexes:**
- Primary key on `id`
- Index on `event_category`
- Index on `event_date`

### event_registration

Stores event registration submissions.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (Primary Key) | Unique registration ID |
| full_name | VARCHAR(255) | Registrant's full name |
| email | VARCHAR(255) | Registrant's email address |
| college_name | VARCHAR(255) | Registrant's college |
| department | VARCHAR(255) | Registrant's department |
| event_category | VARCHAR(100) | Event category |
| event_date | VARCHAR(20) | Event date |
| event_config_id | INT (Foreign Key) | Reference to event_config table |
| created | INT | Unix timestamp of registration |

**Indexes:**
- Primary key on `id`
- Unique constraint on (`email`, `event_date`)
- Index on `email`
- Index on `event_date`
- Index on `event_config_id`

## URLs

| Purpose | URL | Permission Required |
|---------|-----|---------------------|
| Public Registration Form | `/event-registration/register` | Anonymous access |
| Event Configuration | `/admin/event-registration/event-config` | Configure events |
| Admin Settings | `/admin/config/event-registration/settings` | Administer event registration |
| View Registrations | `/admin/event-registration/registrations` | View event registrations |
| Export CSV | `/admin/event-registration/export` | View event registrations |

## Technical Details

### Architecture

- **PSR-4 Autoloading**: All classes follow PSR-4 standards
- **Dependency Injection**: Services are injected via constructor, no `\Drupal::service()` in business logic
- **Drupal Coding Standards**: Code follows Drupal's coding standards
- **Service Layer**: Business logic is separated into `EventRegistrationService`

### File Structure

```
event_registration/
├── src/
│   ├── Controller/
│   │   └── AdminListingController.php
│   ├── Form/
│   │   ├── AdminConfigForm.php
│   │   ├── EventConfigForm.php
│   │   └── EventRegistrationForm.php
│   └── Service/
│       └── EventRegistrationService.php
├── composer.json
├── composer.lock
├── event_registration.info.yml
├── event_registration.install
├── event_registration.module
├── event_registration.permissions.yml
├── event_registration.routing.yml
├── event_registration.services.yml
├── event_registration_schema.sql
└── README.md
```

### AJAX Implementation

The module uses Drupal's AJAX API for dynamic form updates:

1. **Category Selection**: Triggers update of available event dates
2. **Date Selection**: Triggers update of available event names
3. **Admin Listing**: Dynamically filters registrations without page reload

### Dependency Injection

All controllers and forms use dependency injection:

```php
public function __construct(
  Connection $database,
  MailManagerInterface $mail_manager,
  ConfigFactoryInterface $config_factory
) {
  $this->database = $database;
  $this->mailManager = $mail_manager;
  $this->configFactory = $config_factory;
}
```

## Validation Logic

### Registration Form Validation

1. **Email Format**: Validated using PHP's `filter_var()` with `FILTER_VALIDATE_EMAIL`
2. **Special Characters**: Text fields (name, college, department) must contain only alphanumeric characters and spaces
   - Regex pattern: `/^[a-zA-Z0-9\s]+$/`
3. **Duplicate Prevention**: Checks database for existing registration with same email + event date
   - Unique constraint in database: `UNIQUE KEY email_event_date (email, event_date)`

### Event Configuration Validation

1. **Date Logic**:
   - Registration end date must be after start date
   - Event date cannot be before registration start date
2. **Event Name**: No special characters allowed

### Validation Messages

User-friendly error messages are displayed for:
- Invalid email format
- Special characters in text fields
- Duplicate registrations
- Invalid date combinations

## Email Notifications

### Implementation

Email functionality is implemented using Drupal's Mail API:

1. **hook_mail()**: Defined in `event_registration.module`
2. **MailManagerInterface**: Injected into the service
3. **Template**: Plain text email template with event details

### Email Content

Confirmation emails include:
- Registrant's name
- Event name
- Event date
- Event category

### Recipients

- **User**: Receives confirmation at the email address provided in the form
- **Administrator**: Receives notification if enabled in admin settings

### Configuration

- Admin email address is configurable via `/admin/config/event-registration/settings`
- Admin notifications can be enabled/disabled
- No hard-coded email addresses

## Permissions

| Permission | Machine Name | Description |
|------------|--------------|-------------|
| Administer Event Registration | `administer event registration` | Full admin access |
| Configure Events | `configure events` | Create event configurations |
| View Event Registrations | `view event registrations` | View and export registrations |

**Assign Permissions:**
1. Go to `/admin/people/permissions`
2. Check appropriate boxes for each role
3. Save permissions

## Troubleshooting

### Module Not Appearing

- Clear cache: `drush cr`
- Check file permissions
- Verify module is in `modules/custom/` directory

### Database Tables Not Created

- Manually import SQL file
- Or uninstall and reinstall the module
- Check database user permissions

### AJAX Not Working

- Clear cache
- Check browser console for JavaScript errors
- Ensure jQuery is loaded

### Emails Not Sending

1. Check admin configuration at `/admin/config/event-registration/settings`
2. Verify Drupal's email settings at `/admin/config/system/site-information`
3. Check server mail configuration (SMTP)
4. Review logs at `/admin/reports/dblog`

### Duplicate Registration Error

This is by design. Each email can only register once per event date. To register again:
- Use a different email address
- Or contact an administrator to remove the existing registration

## Development

### Coding Standards

This module follows:
- PSR-4 autoloading standards
- Drupal coding standards
- Dependency injection best practices

### Testing

To test the module:

1. Create sample events via event configuration
2. Test public registration form with various inputs
3. Verify duplicate prevention
4. Check email delivery
5. Test admin listing and filtering
6. Export CSV and verify data

## Support

For issues or questions:
- Check Drupal logs: `/admin/reports/dblog`
- Review error messages in the browser
- Verify permissions are set correctly

## License

GPL-2.0-or-later

## Author

Developed as part of FOSSEE Drupal Fellowship Program

---

**Last Updated**: January 29, 2026
