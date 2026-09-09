<?php

// ======================================================
// Smart DUET Admission Management System
// Admin Login Action
// ======================================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// ------------------------------------------------------
// Only POST request allowed
// ------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


// ------------------------------------------------------
// Get form data
// ------------------------------------------------------

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';


// ------------------------------------------------------
// Validation
// ------------------------------------------------------

if ($email === '' || $password === '') {

    setFlashMessage(
        'danger',
        'Please enter your email and password.'
    );

    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


// ------------------------------------------------------
// Check Admin from admins table
// ------------------------------------------------------

$stmt = $conn->prepare(
    "SELECT
        id,
        full_name,
        email,
        mobile,
        password,
        status
     FROM admins
     WHERE email = ?
     LIMIT 1"
);


if (!$stmt) {

    setFlashMessage(
        'danger',
        'Database error. Please try again.'
    );

    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


$stmt->bind_param('s', $email);

$stmt->execute();

$result = $stmt->get_result();


// ------------------------------------------------------
// Admin not found
// ------------------------------------------------------

if ($result->num_rows !== 1) {

    $stmt->close();

    setFlashMessage(
        'danger',
        'Invalid admin email or password.'
    );

    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


$admin = $result->fetch_assoc();

$stmt->close();


// ------------------------------------------------------
// Check Admin Status
// ------------------------------------------------------

if ($admin['status'] !== 'active') {

    setFlashMessage(
        'danger',
        'Your admin account is blocked. Please contact the authority.'
    );

    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


// ------------------------------------------------------
// Verify Password
// ------------------------------------------------------

if (!password_verify($password, $admin['password'])) {

    setFlashMessage(
        'danger',
        'Invalid admin email or password.'
    );

    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


// ------------------------------------------------------
// Login Successful
// ------------------------------------------------------

// Regenerate session ID for security
session_regenerate_id(true);


// Store admin session
$_SESSION['user_id'] = $admin['id'];

$_SESSION['role'] = 'admin';

$_SESSION['admin_id'] = $admin['id'];

$_SESSION['admin_email'] = $admin['email'];

$_SESSION['admin_name'] = $admin['full_name'];


// ------------------------------------------------------
// Redirect to Admin Dashboard
// ------------------------------------------------------

header(
    'Location: ' . APP_URL . '/admin/dashboard.php'
);

exit;