# PIETECH Events Platform Deployment Guide

## Deployment Information

The PIETECH Events Platform has been configured for deployment to:

**Primary Domain**: [pietech-events.is-best.net](https://pietech-events.is-best.net/)

## Database Configuration

The application has been configured to automatically detect which domain it's running on and use the appropriate database credentials:

- When running on the production domain, it uses these credentials:
  - Database Name: mseet_38774389_events
  - Username: mseet_38774389_events
  - Password: ridhan93

- When running locally, it uses the local database credentials from the `.env` file

## Testing Deployment

To verify the deployment is working correctly, you can use the following test scripts:

1. **Database Connection Test**: [domain_test.php](domain_test.php)
   - This script will show detailed information about the database connection
   - It will verify tables exist and count records
   - It will display which domain configuration is being used

## Troubleshooting

If you encounter issues with the deployment, check the following:

1. **Database Connection**:
   - Verify the database credentials in `config/database.php` are correct
   - Ensure the database server is accessible from the hosting environment
   - Check for any firewall restrictions that might block database connections

2. **File Permissions**:
   - Ensure the web server has appropriate read/write permissions
   - Check that uploaded files directory is writable

3. **Environment Variables**:
   - If using environment variables, ensure they are properly set in the hosting environment
   - For shared hosting without `.env` support, the application will fall back to the domain detection method

## Maintenance

To update the application on the production servers:

1. Make and test changes locally
2. Upload the changed files to both domains using FTP/SFTP
3. Run the database connection test to verify everything is working

## Security Notes

- Database credentials are stored securely and only used when needed
- The application detects the domain to use appropriate credentials
- Error logging has been enhanced to help diagnose any issues