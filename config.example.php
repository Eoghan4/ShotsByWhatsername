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

// File Upload Storage
// UPLOAD_DIR: absolute path on the server where images are saved
// UPLOAD_URL_BASE: the public URL prefix used to serve images
define('UPLOAD_DIR', '/var/www/shotsbywhatsername/uploads');
define('UPLOAD_URL_BASE', '/uploads');

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
