<?php
/**
 * Smart DUET Admission Management System
 *
 * File: actions/login_action.php
 *
 * Purpose:
 * Process applicant login.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';


// --------------------------------------------------
// Only POST Request Allowed
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(APP_URL . '/login.php');

}


// --------------------------------------------------
// Get Form Data
// --------------------------------------------------

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';


// --------------------------------------------------
// Validate Input
// --------------------------------------------------

if (empty($login) || empty($password)) {

    setFlashMessage(
        'danger',
        'Please enter your email/mobile and password.'
    );

    redirect(APP_URL . '/login.php');

}


// --------------------------------------------------
// Find User by Email OR Mobile
// --------------------------------------------------

$sql = "
    SELECT
        id,
        email,
        mobile,
        password,
        role,
        status
    FROM users
    WHERE email = ? OR mobile = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ss",
    $login,
    $login
);

$stmt->execute();

$result = $stmt->get_result();


// --------------------------------------------------
// User Not Found
// --------------------------------------------------

if ($result->num_rows !== 1) {

    $stmt->close();

    setFlashMessage(
        'danger',
        'Invalid email/mobile number or password.'
    );

    redirect(APP_URL . '/login.php');

}


$user = $result->fetch_assoc();

$stmt->close();


// --------------------------------------------------
// Check Account Status
// --------------------------------------------------

if ($user['status'] !== 'active') {

    setFlashMessage(
        'danger',
        'Your account is currently blocked. Please contact the administrator.'
    );

    redirect(APP_URL . '/login.php');

}


// --------------------------------------------------
// Verify Password
// --------------------------------------------------

if (!password_verify($password, $user['password'])) {

    setFlashMessage(
        'danger',
        'Invalid email/mobile number or password.'
    );

    redirect(APP_URL . '/login.php');

}


// --------------------------------------------------
// Regenerate Session ID
// --------------------------------------------------

session_regenerate_id(true);


// --------------------------------------------------
// Store User Information in Session
// --------------------------------------------------

$_SESSION['user_id'] = (int) $user['id'];

$_SESSION['user_email'] = $user['email'];

$_SESSION['user_mobile'] = $user['mobile'];

$_SESSION['user_role'] = $user['role'];


// --------------------------------------------------
// Login Successful
// --------------------------------------------------

setFlashMessage(
    'success',
    'Login successful. Welcome to Smart DUET.'
);


// --------------------------------------------------
// Redirect According to Role
// --------------------------------------------------

if ($user['role'] === 'admin') {

    redirect(APP_URL . '/admin/dashboard.php');

}


// Applicant

redirect(APP_URL . '/dashboard.php');