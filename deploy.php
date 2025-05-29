<?php
/**
 * Deployment Script for EventsPro
 * 
 * This script helps to deploy the application to the hosting server via FTP.
 * It will connect to the FTP server and upload all the necessary files.
 */

// FTP Configuration
$ftpServer = 'ftpupload.net'; // Update this if your hosting provider has a different FTP server
$ftpUsername = 'mseet_38774389';
$ftpPassword = 'ridhan93';
$ftpPort = 21;
$remoteDir = '/htdocs'; // Remote directory where files should be uploaded for is-best.net

// Local directory to upload
$localDir = __DIR__;

// Files/directories to exclude from upload
$excludeList = [
    '.git',
    '.gitignore',
    'deploy.php',
    'README.md',
    'composer-setup.php',
    '.env'
];

echo "Starting deployment to {$ftpServer}...\n";

// Connect to FTP server
$conn = ftp_connect($ftpServer, $ftpPort);
if (!$conn) {
    die("Could not connect to {$ftpServer}\n");
}

// Login
echo "Logging in...\n";
if (!ftp_login($conn, $ftpUsername, $ftpPassword)) {
    die("Login failed!\n");
}

// Enable passive mode (often needed for firewalls and NAT)
ftp_pasv($conn, true);

echo "Connected successfully to {$ftpServer}\n";

/**
 * Upload a directory recursively
 * 
 * @param resource $ftpConn FTP connection resource
 * @param string $localPath Local path
 * @param string $remotePath Remote path
 * @param array $exclude Files/directories to exclude
 * @return void
 */
function uploadDirectory($ftpConn, $localPath, $remotePath, $exclude = []) {
    // Create remote directory if it doesn't exist
    try {
        if (!@ftp_chdir($ftpConn, $remotePath)) {
            ftp_mkdir($ftpConn, $remotePath);
            ftp_chdir($ftpConn, $remotePath);
            echo "Created directory: {$remotePath}\n";
        }
    } catch (Exception $e) {
        echo "Warning: " . $e->getMessage() . "\n";
    }
    
    $files = scandir($localPath);
    
    foreach ($files as $file) {
        if ($file == '.' || $file == '..' || in_array($file, $exclude)) {
            continue;
        }
        
        $localFilePath = $localPath . DIRECTORY_SEPARATOR . $file;
        $remoteFilePath = $remotePath . '/' . $file;
        
        if (is_dir($localFilePath)) {
            // Recursively upload subdirectories
            uploadDirectory($ftpConn, $localFilePath, $remoteFilePath, $exclude);
            ftp_chdir($ftpConn, $remotePath); // Go back to parent directory
        } else {
            // Upload file
            if (ftp_put($ftpConn, $file, $localFilePath, FTP_BINARY)) {
                echo "Uploaded: {$localFilePath} to {$remoteFilePath}\n";
            } else {
                echo "Failed to upload: {$localFilePath}\n";
            }
        }
    }
}

// Start the upload process
try {
    uploadDirectory($conn, $localDir, $remoteDir, $excludeList);
    echo "\nDeployment completed successfully!\n";
} catch (Exception $e) {
    echo "Error during deployment: " . $e->getMessage() . "\n";
}

// Close the connection
ftp_close($conn);
echo "FTP connection closed.\n";

echo "\n==============================================\n";
echo "Deployment Summary:\n";
echo "- FTP Server: {$ftpServer}\n";
echo "- Username: {$ftpUsername}\n";
echo "- Remote Directory: {$remoteDir}\n";
echo "- Application URL: https://pietech-events.is-best.net\n";
echo "==============================================\n";

echo "\nImportant Notes:\n";
echo "1. Make sure to verify the database connection works correctly\n";
echo "2. Check that email functionality is working properly\n";
echo "3. Test the application by accessing https://pietech-events.is-best.net\n";
echo "4. If the attendance export functionality doesn't work, make sure TCPDF is properly uploaded\n";
echo "5. If you encounter any issues, check the server error logs\n";

echo "\nThank you for using the EventsPro deployment script!\n";