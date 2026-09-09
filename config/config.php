<?php
/**
 * Smart DUET Admission Management System
 *
 * File: config/config.php
 *
 * Purpose:
 * Common project configuration and database connection.
 */


// --------------------------------------------------
// Project Base URL
// --------------------------------------------------

// Change this if your project folder name is different.
define('APP_URL', '/smart-duet');


// --------------------------------------------------
// Project Name
// --------------------------------------------------

define('APP_NAME', 'Smart DUET Admission Management System');


// --------------------------------------------------
// Include Database Class
// --------------------------------------------------

require_once __DIR__ . '/database.php';


// --------------------------------------------------
// Create Database Connection
// --------------------------------------------------

$database = new Database();
$conn = $database->getConnection();


// --------------------------------------------------
// Start Session
// --------------------------------------------------

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}