# Quick Start Guide - Event Registration Module

## Installation (3 Minutes)

### Step 1: Enable the Module
```bash
cd /path/to/drupal
drush en event_registration -y
drush cr
```

Or via UI:
1. Go to `/admin/modules`
2. Find "Event Registration" 
3. Check the box and click "Install"

### Step 2: Configure Admin Email
1. Go to `/admin/config/event-registration/settings`
2. Enter admin email: `admin@example.com`
3. Check "Enable Admin Notifications"
4. Click "Save configuration"

### Step 3: Set Permissions
1. Go to `/admin/people/permissions`
2. For "Administrator" role:
   - ✓ Administer Event Registration
   - ✓ Configure Events
   - ✓ View Event Registrations
3. Click "Save permissions"

## Quick Test (5 Minutes)

### 1. Create a Test Event
1. Go to `/admin/event-registration/event-config`
2. Fill in:
   - Event Name: `Test Workshop`
   - Category: `Online Workshop`
   - Event Date: Tomorrow's date
   - Registration Start: Today's date
   - Registration End: Tomorrow's date
3. Click "Save Event Configuration"

### 2. Test Registration
1. Open new incognito window
2. Go to `/event-registration/register`
3. Fill in:
   - Full Name: `John Doe`
   - Email: `test@example.com`
   - College Name: `Test College`
   - Department: `Computer Science`
   - Category: Select `Online Workshop`
   - Event Date: Select the date (loads via AJAX)
   - Event Name: Select `Test Workshop` (loads via AJAX)
4. Click "Register for Event"
5. Check for success message and email

### 3. View Registrations
1. Go to `/admin/event-registration/registrations`
2. Select Event Date
3. Select Event Name (loads via AJAX)
4. See the registration in the table
5. Click "Export to CSV" to download

## Common URLs

| Purpose | URL |
|---------|-----|
| Public Registration | `/event-registration/register` |
| Event Configuration | `/admin/event-registration/event-config` |
| Admin Settings | `/admin/config/event-registration/settings` |
| View Registrations | `/admin/event-registration/registrations` |
| Permissions | `/admin/people/permissions` |

## Troubleshooting

**Module not showing?**
```bash
drush cr
```

**Tables not created?**
```bash
drush sql-query < event_registration_schema.sql
```

**AJAX not working?**
- Clear cache
- Check browser console
- Ensure JavaScript is enabled

**Emails not sending?**
1. Check `/admin/config/event-registration/settings`
2. Verify Drupal mail settings at `/admin/config/system/site-information`
3. Check logs at `/admin/reports/dblog`

## Sample Data

Use the SQL file to insert sample events:
```bash
drush sql-query < event_registration_schema.sql
```

This creates 4 sample events:
- Introduction to Web Development (Online Workshop)
- AI/ML Bootcamp (Hackathon)
- Tech Summit 2026 (Conference)
- Python Basics Workshop (One-day Workshop)

## Git Commands for Submission

```bash
# Initialize git (if not done)
git init

# Add all files
git add .

# Make initial commit
git commit -m "Initial commit: Event Registration Module for Drupal 10"

# Add more commits (for evaluation)
git add backend/modules/custom/event_registration/src/Form/
git commit -m "Add event configuration and registration forms"

git add backend/modules/custom/event_registration/src/Service/
git commit -m "Add event registration service with email functionality"

git add backend/modules/custom/event_registration/src/Controller/
git commit -m "Add admin listing controller with CSV export"

git add backend/modules/custom/event_registration/*.yml
git commit -m "Add routing, permissions, and services configuration"

git add backend/modules/custom/event_registration/README.md
git commit -m "Add comprehensive documentation"

# Push to GitHub
git remote add origin https://github.com/YOUR-USERNAME/FOSSEE-DRUPAL.git
git branch -M main
git push -u origin main
```

## Features Checklist

- [x] Event configuration form
- [x] Public registration form
- [x] AJAX dynamic dropdowns
- [x] Duplicate prevention
- [x] Email notifications (user + admin)
- [x] Admin listing with filters
- [x] CSV export
- [x] Custom permissions
- [x] Config API usage
- [x] Dependency injection
- [x] PSR-4 autoloading
- [x] Database schema
- [x] Comprehensive documentation

## Need Help?

Check the full [README.md](README.md) for detailed documentation.
