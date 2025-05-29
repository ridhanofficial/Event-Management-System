<?php
/**
 * Authentication Helper Functions
 * 
 * This file provides functions for authentication and session management.
 */

/**
 * Check if a user is logged in
 * 
 * @return bool Whether the user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the currently logged in user's data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    global $userModel;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    return $userModel->findById($_SESSION['user_id']);
}

/**
 * Get the currently logged in user's ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Login a user
 * 
 * @param int $userId User ID
 * @param string $role User role
 * @return void
 */
function login($userId, $role = 'user') {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = $role;
    $_SESSION['authenticated'] = true;
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Logout the current user
 * 
 * @return void
 */
function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if the current user has a specified role
 * 
 * @param string $requiredRole Required role
 * @return bool Whether the user has the required role
 */
function hasRole($requiredRole) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $requiredRole;
}

/**
 * Check if the current user is an admin
 * 
 * @return bool Whether the user is an admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Generate a CSRF token
 * 
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify a CSRF token
 * 
 * @param string $token Token to verify
 * @return bool Whether the token is valid
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require authentication
 * Redirects to login page if not logged in
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('Please log in to access that page.', 'warning');
        redirect('login.php');
    }
}

/**
 * Require admin privileges
 * Redirects to dashboard if not an admin
 * 
 * @return void
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        setFlashMessage('You do not have permission to access that page.', 'danger');
        redirect('dashboard.php');
    }
} 