<?php
/**
 * Utility Functions
 * 
 * This file provides common utility functions used throughout the application.
 */

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Set a flash message to be displayed on the next page
 * 
 * @param string $message Message to display
 * @param string $type Message type (success, info, warning, danger)
 * @return void
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message from session
 * 
 * @return array|null Message and type, or null if no message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return [
            'message' => $message,
            'type' => $type
        ];
    }
    
    return null;
}

/**
 * Display a flash message
 * 
 * @param string $message Message to display
 * @param string $type Message type (success, danger, warning, info)
 * @return void
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show' role='alert'>
                {$flash['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format a date in a user-friendly way
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format a time in 12-hour format
 * 
 * @param string $time Time string
 * @return string Formatted time
 */
function formatTime($time) {
    return date('g:i A', strtotime($time));
}

/**
 * Check if the current request is a POST request
 * 
 * @return bool Whether the request is a POST request
 */
function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Get base URL of the application
 * 
 * @return string Base URL
 */
function getBaseUrl() {
    // Get the base URL from the APP_URL constant
    $baseUrl = defined('APP_URL') ? APP_URL : 'https://pietech-events.is-best.net';
    
    // Remove trailing slash if present
    return rtrim($baseUrl, '/');
}

/**
 * Generate a random token
 * 
 * @param int $length Token length
 * @return string Random token
 */
function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Debug function to print variables in a readable format
 * 
 * @param mixed $var Variable to debug
 * @param bool $die Whether to die after printing
 * @return void
 */
function debug($var, $die = true) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

/**
 * Get event category badge HTML
 * 
 * @param string $category Event category
 * @return string HTML for the category badge
 */
function getCategoryBadge($category) {
    $badgeClass = '';
    
    switch ($category) {
        case 'technical':
            $badgeClass = 'bg-primary';
            break;
        case 'cultural':
            $badgeClass = 'bg-success';
            break;
        case 'sports':
            $badgeClass = 'bg-warning text-dark';
            break;
        default:
            $badgeClass = 'bg-secondary';
    }
    
    return '<span class="badge ' . $badgeClass . '">' . ucfirst($category) . '</span>';
}

/**
 * Check if an event date is in the past
 * 
 * @param string $date Event date
 * @param string $time Event time
 * @return bool True if in the past, false otherwise
 */
function isEventPast($date, $time) {
    // Make sure we have valid date and time
    if (empty($date) || empty($time)) {
        error_log("isEventPast called with empty date or time: date=$date, time=$time");
        return false; // Assume not past if we don't have valid data
    }
    
    // Format the date and time to ensure proper formatting
    try {
        $dateObj = new DateTime("$date $time");
        $eventDateTime = $dateObj->getTimestamp();
        $currentTime = time();
        
        return $eventDateTime < $currentTime;
    } catch (Exception $e) {
        error_log("Error in isEventPast: " . $e->getMessage());
        return false;
    }
}

/**
 * Get registration status badge HTML
 * 
 * @param string $status Registration status
 * @return string HTML for the status badge
 */
function getStatusBadge($status) {
    $badgeClass = '';
    
    switch ($status) {
        case 'approved':
            $badgeClass = 'bg-success';
            break;
        case 'pending':
            $badgeClass = 'bg-warning text-dark';
            break;
        case 'rejected':
            $badgeClass = 'bg-danger';
            break;
        default:
            $badgeClass = 'bg-secondary';
    }
    
    return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
}

/**
 * Get attendance badge HTML
 * 
 * @param bool $checkIn Check-in status
 * @return string HTML for the attendance badge
 */
function getAttendanceBadge($checkIn) {
    if ($checkIn) {
        return '<span class="badge bg-success">Present</span>';
    }
    return '<span class="badge bg-danger">Absent</span>';
} 