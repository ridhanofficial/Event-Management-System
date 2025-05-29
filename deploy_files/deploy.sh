#!/bin/bash
# Database Fix Deployment Script

# Define colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}EventsPro Database Connection Fix - Deployment Script${NC}"
echo ""

# Define FTP details (you'll need to fill these in)
FTP_HOST="your-ftp-host.com"
FTP_USER="your-ftp-username"
FTP_PASS="your-ftp-password"
FTP_DIR="/public_html"  # Remote directory on the FTP server

echo -e "${YELLOW}Step 1: Preparing files for deployment...${NC}"
cp ../includes/Database.php ./Database.php.backup
cp ../config/database.php ./database.php.backup
echo -e "${GREEN}✓ Backup files created${NC}"

echo -e "${YELLOW}Step 2: Uploading files to server...${NC}"
echo "Please enter your FTP credentials when prompted."

# Upload files using command-line FTP
# Note: For security, it's better to enter credentials when prompted rather than in the script
ftp -n << EOF
open $FTP_HOST
user $FTP_USER $FTP_PASS
cd $FTP_DIR
put Database.php includes/Database.php
put config_database.php config/database.php
put connection_verify.php connection_verify.php
put db_diagnostics.php db_diagnostics.php
bye
EOF

echo -e "${GREEN}✓ Files uploaded to server${NC}"

echo -e "${YELLOW}Step 3: Testing database connection...${NC}"
echo "Please visit https://pietech-events.is-best.net/connection_verify.php"
echo "to verify the database connection is working."

echo ""
echo -e "${YELLOW}Deployment complete!${NC}"
echo "If you encounter any issues, please check the README.md file for troubleshooting steps."
