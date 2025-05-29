<?php
/**
 * Export Attendance Data
 * 
 * This script generates a downloadable CSV or PDF file of attendance records.
 */

// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Include the AttendanceExport class
require_once '../app/libraries/AttendanceExport.php';

// Check if an event ID is provided
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    setFlashMessage('Invalid event ID.', 'danger');
    redirect('attendance.php');
}

$eventId = $_GET['event_id'];
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

// Get event details
$event = $eventModel->getById($eventId);
if (!$event) {
    setFlashMessage('Event not found.', 'danger');
    redirect('attendance.php');
}

// Get sorting preference if provided
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'asc';

// Get all approved registrations for this event
$registrations = $registrationModel->getApprovedByEventId($eventId, $sortBy, $sortOrder);

// Handle TCPDF dependency check for PDF exports
if ($format === 'pdf') {
    // Check if TCPDF is installed via Composer
    $tcpdfPath = '../vendor/tecnickcom/tcpdf/tcpdf.php';
    
    if (file_exists($tcpdfPath)) {
        require_once $tcpdfPath;
    } else {
        // Alternative path for manually installed TCPDF
        $altPath = '../app/libraries/tcpdf/tcpdf.php';
        
        if (file_exists($altPath)) {
            require_once $altPath;
        } else {
            // If TCPDF is not available, fallback to CSV
            setFlashMessage('PDF library not found. Falling back to CSV format.', 'warning');
            $format = 'csv';
        }
    }
}

// Create instance of AttendanceExport and export the data
$exporter = new AttendanceExport($event, $registrations, $format);
$exporter->export();

// Note: The script execution ends in the export method
?>
