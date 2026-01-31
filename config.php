<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration for different environments
$is_localhost = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1' || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

if ($is_localhost) {
    // Localhost settings
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'the_fahad_space');
} else {
    // Deployed environment settings - InfinityFree
    define('DB_HOST', '127.0.0.1'); // Try localhost IP for InfinityFree
    define('DB_USER', 'if0_41032088');
    define('DB_PASS', 'f4020b51b');
    define('DB_NAME', 'if0_41032088_thefahadspace_db');
}

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    // For development: show error
    if ($is_localhost) {
        die("Connection failed: " . mysqli_connect_error());
    } else {
        // For production: log error and show user-friendly message
        error_log("Database connection failed: " . mysqli_connect_error());
        die("Database connection failed. Please contact administrator.");
    }
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Hardcoded admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// Simple admin authentication function
function authenticate_admin($username, $password) {
    return ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD);
}

// Set timezone
date_default_timezone_set('Asia/Karachi');

// Error reporting settings
if ($is_localhost) {
    // Show all errors for development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Hide errors for production
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', 0);
}
?>