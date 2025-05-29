# EventsPro Platform Fix Deployment Instructions

This package contains all the necessary files to fix the database connection, URL handling, logo display, and UI issues on the production server at https://pietech-events.is-best.net.

## Files Included

1. **Database Configuration**
   - `database.php` - Updated database credentials for production
   - `Database.php` - Updated Database class with correct connection settings

2. **URL & Asset Handling**
   - `app.php` - Corrected APP_URL without trailing slash
   - `functions.php` - Fixed URL handling functions
   - `init.php` - Updated file loading sequence

3. **Email Configuration**
   - `email.php` - Fixed PHP syntax with proper closing tag

4. **Verification Scripts**
   - `logo_fix.php` - Checks and fixes logo display issues
   - `asset_check.php` - Verifies all assets are loading correctly
   - `connection_verify.php` - Tests database connection

## Deployment Instructions

### Option 1: FTP Upload (Recommended)

1. Use an FTP client (FileZilla, WinSCP, etc.) to connect to your server
2. Upload each file to its respective location:
   - `database.php` → `/config/database.php`
   - `Database.php` → `/includes/Database.php`
   - `app.php` → `/config/app.php`
   - `functions.php` → `/includes/functions.php`
   - `init.php` → `/includes/init.php`
   - `email.php` → `/config/email.php`
   - `logo_fix.php` → `/logo_fix.php` (root directory)
   - `asset_check.php` → `/asset_check.php` (root directory)
   - `connection_verify.php` → `/connection_verify.php` (root directory)

### Option 2: cPanel File Manager

If you have cPanel access:
1. Log in to cPanel
2. Open File Manager
3. Navigate to your website's root directory
4. Upload and overwrite files as described in Option 1

## Verification Steps

After deploying all files, verify everything is working correctly:

1. Visit https://pietech-events.is-best.net/connection_verify.php to confirm database connectivity
2. Visit https://pietech-events.is-best.net/logo_fix.php to check logo display
3. Visit https://pietech-events.is-best.net/asset_check.php to verify all assets
4. Visit https://pietech-events.is-best.net/verify_deployment.php for comprehensive verification

## Fixing Email Verification

To fix the email verification issues:

1. Visit https://pietech-events.is-best.net/email_config_fix.php
2. Configure the email settings:
   - Enable email functionality by checking the box
   - Enter your SMTP server details (host, port, username, password)
   - For Gmail, use an App Password instead of your regular password
   - Set the From Email Address to match your SMTP username
   - Click "Save Configuration" to update settings
   - The tool will send a test email to verify your settings are correct

**Recommended SMTP Settings:**
- Gmail: smtp.gmail.com (Port 587, TLS)
- Yahoo: smtp.mail.yahoo.com (Port 587, TLS)
- Outlook/Hotmail: smtp.office365.com (Port 587, TLS)
- Custom domain: Check with your email provider

## Troubleshooting Syntax Errors

If you encounter syntax errors (especially in app.php), run the syntax fix tool:

1. Visit https://pietech-events.is-best.net/fix_syntax_errors.php to automatically fix common syntax issues
2. This tool will:
   - Add missing PHP closing tags
   - Remove HTML code incorrectly added to PHP files
   - Create backups before making changes

## If Issues Persist

If you still encounter problems after deployment:
1. Clear your browser cache
2. Check the server error logs
3. Contact support with the results from the verification scripts

## Database Credentials (Verified Working)

- Host: sql205.hstn.me
- Database: mseet_38774389_events
- Username: mseet_38774389
- Password: ridhan93
