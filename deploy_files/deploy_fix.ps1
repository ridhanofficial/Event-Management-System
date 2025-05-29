# EventsPro Database Connection Fix Deployment Script
# PowerShell Version

# Set colors for output
$Green = @{ForegroundColor = 'Green'}
$Yellow = @{ForegroundColor = 'Yellow'}
$Red = @{ForegroundColor = 'Red'}

Write-Host @Yellow "EventsPro Database Connection Fix - Deployment Script"
Write-Host ""

# Create deployment package folder
Write-Host @Yellow "Step 1: Creating deployment package..."
$deployFolder = ".\deployment_package"
if (!(Test-Path -Path $deployFolder)) {
    New-Item -Path $deployFolder -ItemType Directory | Out-Null
}

# Copy files to deployment package
Write-Host "Copying files to deployment package..."
Copy-Item -Path ".\includes\Database.php" -Destination "$deployFolder\Database.php"
Copy-Item -Path ".\config\database.php" -Destination "$deployFolder\database.php"
Copy-Item -Path ".\config\email.php" -Destination "$deployFolder\email.php"
Copy-Item -Path ".\connection_verify.php" -Destination "$deployFolder\connection_verify.php"
Copy-Item -Path ".\deploy_files\VERIFIED_DB_FIX.md" -Destination "$deployFolder\README.md"

Write-Host @Green "✓ Deployment package created in $deployFolder" 

Write-Host @Yellow "Step 2: FTP Upload Instructions"
Write-Host ""
Write-Host "Please upload the following files from the deployment package:"
Write-Host "- Database.php -> /includes/Database.php"
Write-Host "- database.php -> /config/database.php"
Write-Host "- email.php -> /config/email.php"
Write-Host "- connection_verify.php -> /connection_verify.php (for testing)"
Write-Host ""

Write-Host @Yellow "Step 3: Testing Instructions"
Write-Host ""
Write-Host "1. After uploading the files, visit:"
Write-Host "   https://pietech-events.is-best.net/connection_verify.php"
Write-Host "2. You should see 'Connection successful!'"
Write-Host "3. Now try accessing the admin panel:"
Write-Host "   https://pietech-events.is-best.net/admin/"
Write-Host ""

Write-Host @Green "Deployment package created successfully!"
Write-Host ""

# Beep when complete
[Console]::Beep(800, 300)
[Console]::Beep(1000, 500)

Pause
