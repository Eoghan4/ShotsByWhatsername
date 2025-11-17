<?php
/**
 * Configuration File Example
 * 
 * Copy this file to config.php and fill in your actual credentials
 * IMPORTANT: Never commit config.php to version control
 */

// Environment Setting
define('ENVIRONMENT', 'development'); // Change to 'production' when deploying

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'shots_by_whatsername');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your database password

// Imgur API Configuration
// Get your Client ID from: https://api.imgur.com/oauth2/addclient
define('IMGUR_CLIENT_ID', 'YOUR_IMGUR_CLIENT_ID_HERE');

// File Upload Settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Error Reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
?>
