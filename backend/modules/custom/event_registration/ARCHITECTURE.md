# Event Registration Module - Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                     EVENT REGISTRATION MODULE                        │
│                         (Drupal 10)                                  │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                          PUBLIC INTERFACE                            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  /event-registration/register                                        │
│  ┌────────────────────────────────────────────────────────────┐     │
│  │  EventRegistrationForm                                      │     │
│  │  • Full Name, Email, College, Department                   │     │
│  │  • Category (dropdown)                                      │     │
│  │  • Event Date (AJAX) ← loads based on category            │     │
│  │  • Event Name (AJAX) ← loads based on category + date     │     │
│  │  • Validation: email, duplicates, special chars            │     │
│  │  • Submit → Save + Send Emails                             │     │
│  └────────────────────────────────────────────────────────────┘     │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         ADMIN INTERFACE                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  1. /admin/event-registration/event-config                           │
│  ┌────────────────────────────────────────────────────────────┐     │
│  │  EventConfigForm                                            │     │
│  │  • Create events with dates and categories                 │     │
│  │  • Set registration start/end dates                        │     │
│  │  • Validation: date logic, special chars                   │     │
│  └────────────────────────────────────────────────────────────┘     │
│                                                                       │
│  2. /admin/config/event-registration/settings                        │
│  ┌────────────────────────────────────────────────────────────┐     │
│  │  AdminConfigForm                                            │     │
│  │  • Set admin notification email                            │     │
│  │  • Enable/disable admin notifications                      │     │
│  │  • Uses Config API (no hard-coded values)                  │     │
│  └────────────────────────────────────────────────────────────┘     │
│                                                                       │
│  3. /admin/event-registration/registrations                          │
│  ┌────────────────────────────────────────────────────────────┐     │
│  │  AdminListingController                                     │     │
│  │  • Select Event Date (dropdown)                            │     │
│  │  • Select Event Name (AJAX) ← loads based on date         │     │
│  │  • View registrations table                                │     │
│  │  • Show total participants                                 │     │
│  │  • Export to CSV button                                    │     │
│  └────────────────────────────────────────────────────────────┘     │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         SERVICE LAYER                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  EventRegistrationService (Dependency Injection)                     │
│  ┌────────────────────────────────────────────────────────────┐     │
│  │  Methods:                                                   │     │
│  │  • getEventCategories()                                     │     │
│  │  • getEventDatesByCategory($category)                      │     │
│  │  • getEventNamesByCategoryAndDate($cat, $date)             │     │
│  │  • checkDuplicateRegistration($email, $date)               │     │
│  │  • saveRegistration($data)                                 │     │
│  │  • sendConfirmationEmails($data)                           │     │
│  │  • getAllEventDates()                                      │     │
│  │  • getEventNamesByDate($date)                              │     │
│  │  • getRegistrationsByEvent($date, $id)                     │     │
│  │  • getEventNameById($id)                                   │     │
│  └────────────────────────────────────────────────────────────┘     │
│                                                                       │
│  Dependencies:                                                        │
│  • Database (Connection)                                             │
│  • Mail Manager (MailManagerInterface)                               │
│  • Config Factory (ConfigFactoryInterface)                           │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         DATABASE LAYER                               │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  event_config                          event_registration            │
│  ┌──────────────────────────┐          ┌──────────────────────────┐ │
│  │ • id (PK)                │          │ • id (PK)                │ │
│  │ • event_name             │          │ • full_name              │ │
│  │ • event_category         │◄─────────│ • email                  │ │
│  │ • event_date             │   FK     │ • college_name           │ │
│  │ • registration_start_date│          │ • department             │ │
│  │ • registration_end_date  │          │ • event_category         │ │
│  │ • created                │          │ • event_date             │ │
│  └──────────────────────────┘          │ • event_config_id (FK)   │ │
│                                         │ • created                │ │
│  Indexes:                               │ UNIQUE (email, date)     │ │
│  • event_category                       └──────────────────────────┘ │
│  • event_date                           Indexes:                     │
│                                         • email, event_date          │
│                                         • event_config_id            │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         EMAIL SYSTEM                                 │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  hook_mail() → Drupal Mail API                                       │
│  ┌────────────────────────────────────────────────────────────┐     │
│  │  Registration Confirmation Email                            │     │
│  │  • To: User email                                          │     │
│  │  • Subject: "Event Registration Confirmation"             │     │
│  │  • Body: Name, Event Name, Date, Category                 │     │
│  └────────────────────────────────────────────────────────────┘     │
│  ┌────────────────────────────────────────────────────────────┐     │
│  │  Admin Notification Email (optional)                        │     │
│  │  • To: Admin email (from config)                           │     │
│  │  • Subject: "Event Registration Confirmation"             │     │
│  │  • Body: Name, Event Name, Date, Category                 │     │
│  └────────────────────────────────────────────────────────────┘     │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    PERMISSIONS & ACCESS CONTROL                      │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  • Public Access:                                                    │
│    └─ Event Registration Form (anyone can register)                 │
│                                                                       │
│  • Configure Events:                                                 │
│    └─ Event Configuration Form                                       │
│                                                                       │
│  • Administer Event Registration:                                   │
│    └─ Admin Settings Form                                            │
│                                                                       │
│  • View Event Registrations:                                         │
│    └─ Admin Listing Page                                             │
│    └─ CSV Export                                                     │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         DATA FLOW                                    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  Registration Flow:                                                  │
│  ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐          │
│  │  User   │───▶│  Form   │───▶│ Service │───▶│Database │          │
│  │  Input  │    │Validate │    │  Save   │    │ Insert  │          │
│  └─────────┘    └─────────┘    └─────────┘    └─────────┘          │
│                                      │                                │
│                                      ▼                                │
│                                 ┌─────────┐                          │
│                                 │  Email  │                          │
│                                 │  Send   │                          │
│                                 └─────────┘                          │
│                                                                       │
│  AJAX Flow:                                                          │
│  ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐          │
│  │ Select  │───▶│  AJAX   │───▶│ Service │───▶│ Update  │          │
│  │Category │    │Callback │    │  Query  │    │Dropdown │          │
│  └─────────┘    └─────────┘    └─────────┘    └─────────┘          │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                      MODULE FILE STRUCTURE                           │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  event_registration/                                                 │
│  ├── src/                                                            │
│  │   ├── Controller/                                                 │
│  │   │   └── AdminListingController.php ← Listing + Export          │
│  │   ├── Form/                                                       │
│  │   │   ├── AdminConfigForm.php ← Config API                       │
│  │   │   ├── EventConfigForm.php ← Create events                    │
│  │   │   └── EventRegistrationForm.php ← Public form + AJAX         │
│  │   └── Service/                                                    │
│  │       └── EventRegistrationService.php ← Business logic          │
│  ├── composer.json ← PSR-4 autoloading                              │
│  ├── composer.lock                                                   │
│  ├── event_registration.info.yml ← Module metadata                  │
│  ├── event_registration.install ← Schema + hooks                    │
│  ├── event_registration.module ← Mail hook                          │
│  ├── event_registration.permissions.yml ← 3 permissions             │
│  ├── event_registration.routing.yml ← 5 routes                      │
│  ├── event_registration.services.yml ← Service DI                   │
│  ├── event_registration_schema.sql ← DB dump                        │
│  ├── .gitignore                                                      │
│  ├── README.md ← Full documentation                                 │
│  ├── QUICKSTART.md ← Quick guide                                    │
│  ├── BUILD_SUMMARY.md ← This file                                   │
│  └── SUBMISSION_CHECKLIST.md ← Requirements                         │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                      KEY TECHNOLOGIES                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ✓ Drupal 10 Form API                                               │
│  ✓ Drupal AJAX Framework                                            │
│  ✓ Drupal Mail API                                                   │
│  ✓ Drupal Config API                                                 │
│  ✓ Drupal Database API                                               │
│  ✓ Drupal Routing System                                             │
│  ✓ Drupal Permissions System                                         │
│  ✓ PSR-4 Autoloading                                                 │
│  ✓ Dependency Injection                                              │
│  ✓ Service Container                                                 │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

Legend:
PK = Primary Key
FK = Foreign Key
AJAX = Asynchronous JavaScript and XML
DI = Dependency Injection
```
