<?php
/**
 * Email Configuration
 * 
 * This file defines email-related configuration settings.
 */

// Helper function to get environment variables safely
function getEnvOrDefault($key, $default) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// Email server settings
define('MAIL_HOST', getEnvOrDefault('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', getEnvOrDefault('MAIL_PORT', 587));
define('MAIL_USERNAME', getEnvOrDefault('MAIL_USERNAME', 'pietsuvathi@gmail.com'));
define('MAIL_PASSWORD', getEnvOrDefault('MAIL_PASSWORD', 'scnfozhgcirqwvvm'));
define('MAIL_ENCRYPTION', getEnvOrDefault('MAIL_ENCRYPTION', 'tls'));
define('MAIL_FROM_ADDRESS', getEnvOrDefault('MAIL_FROM_ADDRESS', 'pietsuvathi@gmail.com'));
define('MAIL_FROM_NAME', getEnvOrDefault('MAIL_FROM_NAME', 'PIETECH Events Platform'));

// Email debug level (0 = off, 1 = client, 2 = client and server)
define('MAIL_DEBUG', getEnvOrDefault('MAIL_DEBUG', 0));

// SMTP authentication
define('MAIL_AUTH', getEnvOrDefault('MAIL_AUTH', 'true') === 'true');

// Enable/disable email sending
define('MAIL_ENABLED', getEnvOrDefault('MAIL_ENABLED', 'false') === 'true');
?>