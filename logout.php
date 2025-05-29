<?php
/**
 * Logout Script
 * 
 * This script handles user logout by destroying the session and redirecting to the homepage.
 */

// Include initialization file
require_once 'includes/init.php';

// Destroy session
session_destroy();

// Redirect to home page with success message
setFlashMessage('You have been successfully logged out.', 'success');
redirect('index.php'); 