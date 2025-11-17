<?php
/**
 * Database Connection Handler
 * 
 * This file establishes the PDO connection to the MySQL database.
 * Configuration is loaded from config.php
 */

// Load configuration
require_once __DIR__ . '/config.php';

try {
    // Create PDO connection with configuration from config.php
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    // Log the error instead of displaying it in production
    if (ENVIRONMENT === 'production') {
        error_log("Database connection failed: " . $e->getMessage());
        // Display user-friendly error message
        die("We're experiencing technical difficulties. Please try again later.");
    } else {
        // Show detailed error in development
        die("DB connection failed: " . $e->getMessage());
    }
}
?>
