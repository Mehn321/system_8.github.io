<?php
/**
 * Configuration File
 * Contains system-wide configuration settings
 */

// Start session for user login management
session_start();

// Database configuration - connection details
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'borrowing_system';

// System settings - basic information
$system_name = 'Borrowing & Inventory System';
$system_version = '1.0.0';

// File upload settings - limits and allowed file types
$max_file_size = 5242880; // 5MB in bytes
$upload_path = 'uploads/';
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

// Pagination settings - how many records to show per page
$items_per_page = 10;
$borrowers_per_page = 10;
$transactions_per_page = 10;

// Date and time settings - formats for displaying dates
$date_format = 'Y-m-d';
$datetime_format = 'Y-m-d H:i:s';
$display_date_format = 'M d, Y';
$display_datetime_format = 'M d, Y H:i';

// Security settings - limits for login attempts and session time
$password_min_length = 8;
$session_lifetime = 3600; // 1 hour in seconds
$login_attempts_max = 5;
$login_lockout_time = 900; // 15 minutes

// Error reporting - show errors during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Timezone - set to Philippines time
date_default_timezone_set('Asia/Manila');
?>