@echo off
rem EventsPro Database Connection Fix - Deployment Script
color 0E

echo EventsPro Database Connection Fix - Deployment Script
echo.

rem Create deployment package
echo Step 1: Creating deployment package...
mkdir deployment_package 2>nul
copy "deploy_files\Database.php" "deployment_package\Database.php"
copy "deploy_files\config_database.php" "deployment_package\database.php"
copy "connection_verify.php" "deployment_package\connection_verify.php"
copy "deploy_files\db_diagnostics.php" "deployment_package\db_diagnostics.php"
copy "deploy_files\README.md" "deployment_package\README.md"

echo Files prepared in the deployment_package folder.
echo.

echo Step 2: Upload these files to your server
echo   - Upload Database.php to includes/Database.php
echo   - Upload database.php to config/database.php
echo   - Upload connection_verify.php to the root folder
echo   - Upload db_diagnostics.php to the root folder
echo.

echo Use your FTP client (FileZilla, etc.) to upload the files.
echo.

echo Step 3: Testing
echo After uploading the files, visit:
echo   https://pietech-events.is-best.net/connection_verify.php
echo to verify the database connection is working.
echo.

echo Deployment package created successfully!
color 0A
echo.

pause
