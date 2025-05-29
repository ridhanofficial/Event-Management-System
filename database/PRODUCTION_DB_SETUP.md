# EventsPro - Database Setup for Production

This file contains instructions for setting up the EventsPro database on the production server.

## Database Information
- Database Name: mseet_38774389_events
- Username: mseet_38774389_events
- Password: ridhan93

## Setup Instructions

1. Access your hosting control panel
2. Navigate to the MySQL Databases section
3. Create a new database or use the existing mseet_38774389_events
4. Import the SQL schema from database/schema.sql

## Manual Database Setup

If you need to manually create the database structure, use the following SQL commands:

```sql
CREATE TABLE IF NOT EXISTS users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') DEFAULT 'user',
  department VARCHAR(100) DEFAULT NULL,
  reg_no VARCHAR(20) DEFAULT NULL,
  semester INT(2) DEFAULT NULL,
  phone VARCHAR(15) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY (email)
);

CREATE TABLE IF NOT EXISTS events (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  date DATE NOT NULL,
  time TIME NOT NULL,
  venue VARCHAR(255) NOT NULL,
  room_no VARCHAR(50) DEFAULT NULL,
  category VARCHAR(100) DEFAULT NULL,
  capacity INT(5) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  registration_deadline DATETIME DEFAULT NULL,
  status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
  team_based TINYINT(1) DEFAULT 0,
  max_participants INT(3) DEFAULT 1,
  created_by INT(11) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS registrations (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  event_id INT(11) NOT NULL,
  team_name VARCHAR(100) DEFAULT NULL,
  members JSON DEFAULT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  check_in TINYINT(1) DEFAULT 0,
  winner_position INT(11) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS event_updates (
  id INT(11) NOT NULL AUTO_INCREMENT,
  event_id INT(11) NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS settings (
  id INT(11) NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY (setting_key)
);

-- Add initial admin user
INSERT INTO users (name, email, password, role, created_at) 
VALUES ('Admin User', 'admin@example.com', '$2y$10$EncryptedPasswordHash', 'admin', NOW());

-- Add initial settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('site_name', 'PIETECH Events Platform'),
('site_description', 'Manage and participate in events organized by PIETECH.'),
('contact_email', 'contact@pietech-events.is-best.net'),
('max_team_size', '5'),
('enable_winner_selection', '1');
```

## After Import

After successfully importing or creating the database schema:

1. Make sure the database user (mseet_38774389_events) has proper permissions:
   - SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX

2. Test the database connection using the domain_test.php script

3. Create at least one admin user if not already exists

For any database-related issues, please contact your hosting provider for assistance.
