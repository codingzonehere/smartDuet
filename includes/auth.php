<?php
/**
 * Smart DUET Admission Management System
 *
 * File: includes/auth.php
 *
 * Purpose:
 * Check whether the user is logged in.
 */


// --------------------------------------------------
// Load Configuration
// --------------------------------------------------

require_once __DIR__ . '/../config/config.php';


// --------------------------------------------------
// Check User Login
// --------------------------------------------------

if (!isset($_SESSION['user_id'])) {

    // User is not logged in
    header('Location: ' . APP_URL . '/login.php');

    // Stop executing the current page
    exit;
}