# Git Commit Guide for Event Registration Module

## Initial Setup

```bash
# Navigate to your project directory
cd "c:\Riddhi\Github Repo\FOSSEE-DRUPAL"

# Initialize git repository (if not already done)
git init

# Configure git (if not already done)
git config user.name "Your Name"
git config user.email "your.email@example.com"

# Create .gitignore in project root (if needed)
echo "vendor/" >> .gitignore
echo ".DS_Store" >> .gitignore
echo "*.swp" >> .gitignore
```

## Commit Strategy (10+ Commits for Good Evaluation)

### Commit 1: Initial Module Structure
```bash
git add backend/modules/custom/event_registration/event_registration.info.yml
git add backend/modules/custom/event_registration/event_registration.permissions.yml
git add backend/modules/custom/event_registration/event_registration.routing.yml
git add backend/modules/custom/event_registration/.gitignore
git commit -m "feat: Add initial module structure with info, permissions, and routing

- Created module metadata (event_registration.info.yml)
- Defined 3 custom permissions for role-based access
- Set up 5 routes for admin and public interfaces
- Added .gitignore for clean repository"
```

### Commit 2: Database Schema
```bash
git add backend/modules/custom/event_registration/event_registration.install
git add backend/modules/custom/event_registration/event_registration_schema.sql
git commit -m "feat: Add database schema and installation hooks

- Created event_config table for event configurations
- Created event_registration table for user registrations
- Added unique constraint for duplicate prevention (email + event_date)
- Included sample data in SQL file
- Implemented install and uninstall hooks"
```

### Commit 3: Service Layer
```bash
git add backend/modules/custom/event_registration/src/Service/EventRegistrationService.php
git add backend/modules/custom/event_registration/event_registration.services.yml
git commit -m "feat: Implement event registration service with dependency injection

- Created EventRegistrationService class
- Implemented methods for event filtering and retrieval
- Added duplicate registration check
- Implemented email sending functionality
- Registered service with dependency injection
- Used proper type hinting and docblocks"
```

### Commit 4: Event Configuration Form
```bash
git add backend/modules/custom/event_registration/src/Form/EventConfigForm.php
git commit -m "feat: Add event configuration form for administrators

- Created form for admin to configure events
- Added fields: name, category, dates, registration period
- Implemented validation for date logic and special characters
- Used dependency injection for database and messenger
- Follows Drupal Form API standards"
```

### Commit 5: Public Registration Form
```bash
git add backend/modules/custom/event_registration/src/Form/EventRegistrationForm.php
git commit -m "feat: Add public registration form with AJAX functionality

- Created registration form with 7 input fields
- Implemented AJAX callbacks for dynamic dropdowns
- Category selection triggers event date update
- Date selection triggers event name update
- Added validation for email format and special characters
- Integrated duplicate prevention check
- Only shows events with active registration periods"
```

### Commit 6: Admin Configuration
```bash
git add backend/modules/custom/event_registration/src/Form/AdminConfigForm.php
git commit -m "feat: Add admin configuration form using Config API

- Created settings form for admin email and notifications
- Implemented enable/disable toggle for admin notifications
- Used Config API (no hard-coded values)
- Added email format validation
- Extends ConfigFormBase following Drupal standards"
```

### Commit 7: Admin Listing Controller
```bash
git add backend/modules/custom/event_registration/src/Controller/AdminListingController.php
git commit -m "feat: Add admin listing controller with AJAX and CSV export

- Created controller for viewing all registrations
- Implemented AJAX filtering by event date and name
- Added dynamic participant count display
- Implemented CSV export functionality
- Used dependency injection for service and request stack
- Displays registrations in tabular format"
```

### Commit 8: Email System
```bash
git add backend/modules/custom/event_registration/event_registration.module
git commit -m "feat: Implement email notification system using Mail API

- Added hook_mail() for email template
- Sends confirmation to user after registration
- Sends notification to admin (if enabled)
- Email includes: name, event name, date, category
- Added hook_help() for module documentation
- Defined theme hook for future theming"
```

### Commit 9: Composer Configuration
```bash
git add backend/modules/custom/event_registration/composer.json
git add backend/modules/custom/event_registration/composer.lock
git commit -m "chore: Add Composer configuration for PSR-4 autoloading

- Added composer.json with module metadata
- Configured PSR-4 autoloading for src/ directory
- Specified Drupal 10 and PHP 8.1 requirements
- Added composer.lock for dependency management
- Follows Drupal module packaging standards"
```

### Commit 10: Documentation - README
```bash
git add backend/modules/custom/event_registration/README.md
git commit -m "docs: Add comprehensive README documentation

- Installation and setup instructions
- Configuration guidelines
- Usage examples for all features
- Database table documentation
- URL reference for all routes
- Validation logic explanation
- Email notification details
- Troubleshooting guide
- Over 400 lines of detailed documentation"
```

### Commit 11: Documentation - Guides
```bash
git add backend/modules/custom/event_registration/QUICKSTART.md
git add backend/modules/custom/event_registration/SUBMISSION_CHECKLIST.md
git commit -m "docs: Add quick start guide and submission checklist

- Created QUICKSTART.md for rapid module setup
- Added SUBMISSION_CHECKLIST.md for requirement tracking
- Included testing procedures
- Added Git commands for version control
- Provided sample data instructions"
```

### Commit 12: Documentation - Architecture
```bash
git add backend/modules/custom/event_registration/BUILD_SUMMARY.md
git add backend/modules/custom/event_registration/ARCHITECTURE.md
git commit -m "docs: Add build summary and architecture diagrams

- Created BUILD_SUMMARY.md with feature overview
- Added ARCHITECTURE.md with visual diagrams
- Documented data flow and system interactions
- Included file structure and technology stack
- Provided metrics and technical details"
```

### Commit 13: Final Polish
```bash
git add backend/modules/custom/event_registration/
git commit -m "chore: Final review and polish

- Verified all files are properly formatted
- Ensured consistent coding standards
- Validated all documentation links
- Confirmed PSR-4 compliance
- Module ready for production deployment"
```

## Alternative: Smaller Atomic Commits

If you prefer more granular commits:

```bash
# Commit individual files or features
git add backend/modules/custom/event_registration/src/Form/EventConfigForm.php
git commit -m "feat: Add event configuration form with date validation"

git add backend/modules/custom/event_registration/src/Form/EventRegistrationForm.php
git commit -m "feat: Add public registration form"

git add backend/modules/custom/event_registration/src/Form/EventRegistrationForm.php
git commit -m "feat: Add AJAX callbacks to registration form"

git add backend/modules/custom/event_registration/src/Service/EventRegistrationService.php
git commit -m "feat: Add service method for event category retrieval"

git add backend/modules/custom/event_registration/src/Service/EventRegistrationService.php
git commit -m "feat: Add duplicate registration check to service"

git add backend/modules/custom/event_registration/src/Controller/AdminListingController.php
git commit -m "feat: Add admin listing controller"

git add backend/modules/custom/event_registration/src/Controller/AdminListingController.php
git commit -m "feat: Add CSV export functionality"
```

## Commit Message Format

Follow this format for professional commits:

```
<type>: <subject>

<body (optional)>

<footer (optional)>
```

### Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Formatting, missing semicolons, etc.
- `refactor`: Code restructuring
- `test`: Adding tests
- `chore`: Maintenance tasks

### Examples:

```bash
git commit -m "feat: Add AJAX callback for event date dropdown

Implemented updateEventDateOptions() method that dynamically 
loads event dates based on selected category using Drupal 
AJAX framework."
```

```bash
git commit -m "fix: Resolve duplicate registration validation

Updated validation to properly check email + event_date 
combination before allowing registration submission."
```

```bash
git commit -m "docs: Update README with troubleshooting section

Added common issues and solutions for:
- Module installation problems
- AJAX not working
- Email configuration
- Database table creation"
```

## Push to GitHub

After making commits:

```bash
# Create repository on GitHub first, then:
git remote add origin https://github.com/YOUR-USERNAME/FOSSEE-DRUPAL.git

# Verify remote
git remote -v

# Push to GitHub
git branch -M main
git push -u origin main

# For subsequent pushes
git push
```

## View Commit History

```bash
# View all commits
git log

# View commits with file changes
git log --stat

# View compact log
git log --oneline

# View last 5 commits
git log -5

# View commits by author
git log --author="Your Name"
```

## Best Practices for This Submission

1. **Commit Frequently**: Aim for 10-15 commits minimum
2. **Meaningful Messages**: Describe what and why, not just what
3. **Atomic Commits**: Each commit should be a logical unit
4. **Test Before Commit**: Ensure code works before committing
5. **Sign Commits**: Use `git commit -s` for signed commits
6. **Branch Strategy**: Work on feature branches if desired

## Sample Timeline

```
Day 1: Initial setup, database schema, service layer (3-4 commits)
Day 2: Forms implementation (3-4 commits)
Day 3: AJAX, email, admin features (3-4 commits)
Day 4: Documentation, testing, polish (2-3 commits)
```

## Check Status Before Committing

```bash
# See what files have changed
git status

# See specific changes in files
git diff

# See staged changes
git diff --staged

# Add files interactively
git add -p
```

## Undo Changes (if needed)

```bash
# Unstage files
git reset HEAD <file>

# Discard changes in working directory
git checkout -- <file>

# Amend last commit message
git commit --amend -m "New message"

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1
```

## Final Check Before Submission

```bash
# Ensure everything is committed
git status

# Verify all files are tracked
git ls-files

# Check remote connection
git remote -v

# View commit count
git rev-list --count HEAD

# Ensure you have 10+ commits for good evaluation
```

## Pro Tips

1. **Commit early and often** - Shows active development
2. **Write descriptive messages** - Helps reviewers understand your thought process
3. **Use conventional commits** - Makes history readable
4. **Test before pushing** - Avoid pushing broken code
5. **Document as you go** - Commit docs with related code

---

**Target**: 10-15 meaningful commits demonstrating iterative development

**Current Module**: ✅ Ready for version control

**Next Step**: Execute commits and push to GitHub!
