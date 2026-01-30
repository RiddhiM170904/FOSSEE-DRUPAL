# How to Run the Event Registration Module

## Prerequisites Check

Before running the module, ensure you have:

- ✅ Drupal 10.x installed
- ✅ PHP 8.1 or higher
- ✅ MySQL/MariaDB database
- ✅ Web server (Apache/Nginx)
- ✅ Composer installed

---

## Option 1: Quick Setup with DDEV (Recommended)

### Step 1: Install DDEV (if not installed)

**Windows:**
```powershell
choco install ddev
```

**Or download from:** https://ddev.readthedocs.io/en/stable/users/install/

### Step 2: Create Drupal 10 Project

```bash
cd "c:\Riddhi\Github Repo\FOSSEE-DRUPAL\backend"

# Create new Drupal 10 site (if not exists)
composer create-project drupal/recommended-project drupal10
cd drupal10

# Initialize DDEV
ddev config --project-type=drupal10 --docroot=web
ddev start

# Install Drupal
ddev drush site:install --account-name=admin --account-pass=admin -y

# Open in browser
ddev launch
```

### Step 3: Copy Module to Drupal

```bash
# Create custom modules directory
mkdir -p web/modules/custom

# Copy the event_registration module
xcopy /E /I "..\modules\custom\event_registration" "web\modules\custom\event_registration"
```

### Step 4: Enable the Module

```bash
# Enable the module
ddev drush en event_registration -y

# Clear cache
ddev drush cr

# Check module is enabled
ddev drush pm:list | findstr event_registration
```

---

## Option 2: Local XAMPP/WAMP Setup

### Step 1: Install Drupal 10

1. Download Drupal 10: https://www.drupal.org/download
2. Extract to: `C:\xampp\htdocs\drupal10`
3. Create database:
   ```sql
   CREATE DATABASE drupal10;
   CREATE USER 'drupal'@'localhost' IDENTIFIED BY 'password';
   GRANT ALL PRIVILEGES ON drupal10.* TO 'drupal'@'localhost';
   ```
4. Visit: `http://localhost/drupal10`
5. Follow installation wizard

### Step 2: Copy Module

```bash
# Copy module to Drupal
xcopy /E /I "c:\Riddhi\Github Repo\FOSSEE-DRUPAL\backend\modules\custom\event_registration" "C:\xampp\htdocs\drupal10\modules\custom\event_registration"
```

### Step 3: Enable Module

1. Go to: `http://localhost/drupal10/admin/modules`
2. Find "Event Registration" in the list
3. Check the checkbox
4. Click "Install" button
5. Wait for confirmation

---

## Post-Installation Setup (Required)

### Step 1: Set Permissions

1. Navigate to: `/admin/people/permissions`
2. Find these permissions:
   - ☐ Administer Event Registration
   - ☐ Configure Events
   - ☐ View Event Registrations
3. Assign to "Administrator" role
4. Click "Save permissions"

### Step 2: Configure Admin Email

1. Navigate to: `/admin/config/event-registration/settings`
2. Fill in:
   - **Admin Email**: `admin@example.com` (use your email)
   - **Enable Admin Notifications**: ✓ Check this
3. Click "Save configuration"

### Step 3: Configure Site Email (Important for Email Sending)

1. Navigate to: `/admin/config/system/site-information`
2. Set **Email address**: `noreply@yourdomain.com`
3. Click "Save configuration"

---

## Testing the Module (Step-by-Step)

### Test 1: Create an Event

1. **Navigate to**: `/admin/event-registration/event-config`

2. **Fill in the form**:
   ```
   Event Name: Introduction to Drupal
   Category: Online Workshop
   Event Date: 2026-02-15 (select future date)
   Registration Start Date: 2026-01-29 (today)
   Registration End Date: 2026-02-14 (before event)
   ```

3. **Click**: "Save Event Configuration"

4. **Expected Result**: ✅ Success message appears

### Test 2: Test Public Registration

1. **Open new incognito/private browser window**

2. **Navigate to**: `/event-registration/register`

3. **You should see**: Registration form with fields

4. **Fill in the form**:
   ```
   Full Name: John Doe
   Email: john.doe@example.com
   College Name: Test College
   Department: Computer Science
   Category: [Select "Online Workshop"]
   ```

5. **Watch**: Event Date dropdown loads automatically (AJAX)

6. **Select**: The date you created (2026-02-15)

7. **Watch**: Event Name dropdown loads (AJAX)

8. **Select**: "Introduction to Drupal"

9. **Click**: "Register for Event"

10. **Expected Result**: 
    - ✅ Success message appears
    - ✅ Email sent confirmation

### Test 3: Test Duplicate Prevention

1. **Same incognito window**, refresh the page

2. **Fill form with SAME email and event**:
   ```
   Email: john.doe@example.com (same as before)
   Select same event and date
   ```

3. **Click**: "Register for Event"

4. **Expected Result**: 
   - ❌ Error message: "You have already registered for this event"

### Test 4: Test Special Characters Validation

1. **Fill form with special characters**:
   ```
   Full Name: John@Doe#123
   ```

2. **Click**: "Register for Event"

3. **Expected Result**:
   - ❌ Error: "Full name should not contain special characters"

### Test 5: View Registrations (Admin)

1. **Navigate to**: `/admin/event-registration/registrations`

2. **Select Event Date**: Choose `2026-02-15`

3. **Watch**: Event Name dropdown loads (AJAX)

4. **Select Event**: "Introduction to Drupal"

5. **Expected Result**:
   - ✅ Table shows with registration details
   - ✅ Total Participants: 1
   - ✅ Shows: Name, Email, College, Department, Date

### Test 6: Export to CSV

1. **On the registrations page** (from Test 5)

2. **Click**: "Export to CSV" button

3. **Expected Result**:
   - ✅ CSV file downloads
   - ✅ Contains all registration data
   - ✅ Filename: `event_registrations_2026-02-15.csv`

### Test 7: Test Email (Check Logs)

**If emails aren't being sent**, check Drupal logs:

1. Navigate to: `/admin/reports/dblog`
2. Look for "mail" entries
3. Check if emails were attempted

**To actually receive emails**, configure SMTP:

```bash
# Install SMTP module (for testing)
composer require drupal/smtp
drush en smtp -y

# Configure at: /admin/config/system/smtp
# Use Gmail/Mailgun/SendGrid for testing
```

---

## Quick Test Script

Copy and paste this into your browser console after navigating to registration form:

```javascript
// Auto-fill registration form for quick testing
document.querySelector('input[name="full_name"]').value = 'Test User';
document.querySelector('input[name="email"]').value = 'test' + Date.now() + '@example.com';
document.querySelector('input[name="college_name"]').value = 'Test College';
document.querySelector('input[name="department"]').value = 'Computer Science';
```

---

## Troubleshooting

### Problem: Module not appearing in list

**Solution**:
```bash
# Clear cache
drush cr

# Or via UI
# Visit: /admin/config/development/performance
# Click "Clear all caches"
```

### Problem: Database tables not created

**Solution**:
```bash
# Import SQL manually
cd "c:\Riddhi\Github Repo\FOSSEE-DRUPAL\backend\modules\custom\event_registration"

# Using drush
drush sql-query --file=event_registration_schema.sql

# Or using MySQL directly
mysql -u drupal -p drupal10 < event_registration_schema.sql
```

### Problem: AJAX not working

**Solutions**:
1. Clear browser cache (Ctrl + Shift + Del)
2. Clear Drupal cache
3. Check browser console (F12) for JavaScript errors
4. Disable browser extensions

### Problem: Permissions denied

**Solution**:
1. Log in as admin (user 1)
2. Go to: `/admin/people/permissions`
3. Assign permissions to Administrator role
4. Clear cache

### Problem: Emails not sending

**Solutions**:

**Option 1: Check Drupal logs**
```bash
drush watchdog:show --type=mail
```

**Option 2: Use maillog module for testing**
```bash
composer require drupal/maillog
drush en maillog -y

# View captured emails at: /admin/reports/maillog
```

**Option 3: Configure SMTP (for real emails)**
```bash
composer require drupal/smtp
drush en smtp -y

# Configure at: /admin/config/system/smtp
# Use Gmail SMTP:
# - SMTP Server: smtp.gmail.com
# - Port: 587
# - Username: your-email@gmail.com
# - Password: app password (not regular password)
```

### Problem: "Access denied" errors

**Solution**:
```bash
# Reset admin password
drush user:password admin "admin"

# Or create new admin user
drush user:create testadmin --mail="test@example.com" --password="admin"
drush user:role:add administrator testadmin
```

---

## Complete Testing Checklist

Use this to verify everything works:

- [ ] Module installs without errors
- [ ] Permissions page shows 3 new permissions
- [ ] Admin settings form loads and saves
- [ ] Event configuration form loads
- [ ] Can create new event
- [ ] Public registration form loads
- [ ] Category dropdown populates
- [ ] Event date loads via AJAX when category selected
- [ ] Event name loads via AJAX when date selected
- [ ] Can submit registration successfully
- [ ] Duplicate registration is prevented
- [ ] Special character validation works
- [ ] Email format validation works
- [ ] Admin listing page loads
- [ ] Date dropdown populates in admin listing
- [ ] Event name dropdown updates via AJAX
- [ ] Registration table displays correctly
- [ ] Participant count is correct
- [ ] CSV export downloads successfully
- [ ] CSV contains correct data
- [ ] Email sent confirmation message appears
- [ ] (Optional) Actual email received

---

## Demo Data Setup (Optional)

To quickly populate with test data:

```bash
# Import the SQL file with sample events
drush sql-query --file=web/modules/custom/event_registration/event_registration_schema.sql
```

This creates 4 sample events:
1. Introduction to Web Development (Online Workshop) - 2026-02-15
2. AI/ML Bootcamp (Hackathon) - 2026-03-01
3. Tech Summit 2026 (Conference) - 2026-04-10
4. Python Basics Workshop (One-day Workshop) - 2026-02-20

---

## Development Mode (For Further Development)

### Enable development settings:

1. Copy `sites/default/default.settings.php` to `sites/default/settings.local.php`

2. Add at the end of `settings.php`:
```php
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
```

3. In `settings.local.php`:
```php
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
```

4. Rebuild cache:
```bash
drush cr
```

---

## URLs Summary

After setup, these URLs will be available:

| URL | What to Test |
|-----|-------------|
| `/admin/modules` | Module enabled |
| `/admin/people/permissions` | Permissions set |
| `/admin/config/event-registration/settings` | Admin email config |
| `/admin/event-registration/event-config` | Create events |
| `/event-registration/register` | Public registration |
| `/admin/event-registration/registrations` | View registrations |
| `/admin/reports/dblog` | Error logs |

---

## Video Tutorial Steps (Record Your Testing)

1. **Start**: Show Drupal login
2. **Enable**: Show module installation
3. **Configure**: Show admin settings
4. **Create Event**: Show event creation
5. **Register**: Show public registration with AJAX
6. **Duplicate**: Show duplicate prevention
7. **View**: Show admin listing
8. **Export**: Show CSV download
9. **Done**: Show success message

---

## Getting Help

If you encounter issues:

1. **Check logs**: `/admin/reports/dblog`
2. **Review README**: See main [README.md](README.md)
3. **Check status**: `/admin/reports/status`
4. **Verify requirements**: PHP 8.1+, MySQL, proper permissions

---

## Success Criteria

✅ You know the module is working when:

1. You can create events without errors
2. Public form shows only active events
3. AJAX dropdowns populate correctly
4. Registrations save to database
5. Duplicate prevention works
6. Admin listing shows registrations
7. CSV export downloads with data
8. No errors in Drupal logs

---

**Ready to run!** Start with Option 1 (DDEV) or Option 2 (XAMPP), then follow the Testing section step-by-step.
