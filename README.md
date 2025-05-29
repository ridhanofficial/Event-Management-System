# PIETECH Events Platform

PIETECH Events Platform is a comprehensive web application for managing technical, cultural, and sports events. It provides features for event creation, registration, attendance tracking, and more.

## Features

- **User Authentication**
  - Register/login with session-based authentication
  - Email verification system
  - User roles: admin, regular user
  - Profile management

- **Event Management (Admin only)**
  - Create, edit, and delete events
  - Multiple categories: technical, cultural, sports
  - Set venue, room number, date, time, and max participants

- **Event Listing**
  - Browse all upcoming events
  - Filter by category
  - Detailed event information

- **Registration System**
  - Individual or team registration
  - Automatic confirmation emails
  - Registration status tracking

- **Admin Dashboard**
  - Manage users and events
  - Approve or decline registrations
  - Attendance tracking
  - View statistics

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- SMTP server (for email functionality)

## Installation

1. **Clone the repository:**
   ```
   git clone https://github.com/yourusername/pietech-events.git
   cd pietech-events
   ```

2. **Create a MySQL database:**
   ```sql
   CREATE DATABASE pietech_events CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```

3. **Configure the application:**
   - Copy `.env.example` to `.env`
   - Update the `.env` file with your database and email credentials

4. **Import the database schema:**
   ```
   php database/init.php
   ```

5. **Set up proper permissions:**
   - Ensure the web server has write permissions to the necessary directories

6. **Configure your web server:**
   - Point your web server's document root to the project's public directory
   - Set up appropriate URL rewriting rules if needed

## Usage

### Admin Access

- Access the admin dashboard at `/admin`
- Default admin login:
  - Email: admin@pietechevents.com
  - Password: admin123
  - Remember to change this password after first login!

### Creating Events

1. Log in as an admin
2. Go to the admin dashboard
3. Click on "Create New Event"
4. Fill in the event details and submit

### Registering for Events

1. Log in as a regular user
2. Browse the events listing
3. Click on an event to view details
4. Click "Register" and follow the instructions

### Tracking Attendance

1. Log in as an admin
2. Navigate to the "Attendance Dashboard"
3. Select an event
4. Mark attendees as present or absent

## Customization

### Email Templates

Email templates are located in the `includes/Mailer.php` file. You can modify the HTML directly within the appropriate methods.

### Visual Theme

The application uses Bootstrap and custom CSS. To customize the look:

1. Edit the `public/css/style.css` file
2. Update color variables, typography, or spacing as needed

## Security Features

- Session-based authentication
- CSRF protection for forms
- Password hashing
- Input sanitization
- Role-based access control

## License

[MIT License](LICENSE)

## Credits

Developed for PIETECH by [Your Name] 