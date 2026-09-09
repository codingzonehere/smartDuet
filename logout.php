<?php
/**
 * Smart DUET Admission Management System
 *
 * File: logout.php
 *
 * Purpose:
 * Safely log out the currently logged-in user.
 */


// --------------------------------------------------
// Load Configuration
// --------------------------------------------------

require_once __DIR__ . '/config/config.php';


// --------------------------------------------------
// Remove All Session Data
// --------------------------------------------------

$_SESSION = [];


// --------------------------------------------------
// Destroy Session Cookie
// --------------------------------------------------

if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}


// --------------------------------------------------
// Destroy Session
// --------------------------------------------------

session_destroy();


// --------------------------------------------------
// Redirect to Login
// --------------------------------------------------

header('Location: ' . APP_URL . '/login.php');

exit;