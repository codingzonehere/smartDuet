<?php

// ======================================================
// Smart DUET Admission Management System
// Centralized Admin Authentication
// ======================================================

require_once __DIR__ . '/../config/config.php';


// ------------------------------------------------------
// Check Admin Session
// ------------------------------------------------------

if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {

    header(
        'Location: ' . APP_URL . '/admin/login.php'
    );

    exit;
}


// ------------------------------------------------------
// Get Current Admin ID
// ------------------------------------------------------

$adminId = (int) $_SESSION['admin_id'];


// ------------------------------------------------------
// Verify Admin Account Still Exists and is Active
// ------------------------------------------------------

$stmt = $conn->prepare(
    "SELECT id, status
     FROM admins
     WHERE id = ?
     LIMIT 1"
);


if (!$stmt) {

    // Database error → logout
    $_SESSION = [];

    session_destroy();

    header(
        'Location: ' . APP_URL . '/admin/login.php'
    );

    exit;
}


$stmt->bind_param(
    'i',
    $adminId
);

$stmt->execute();

$result = $stmt->get_result();


// ------------------------------------------------------
// Admin Not Found
// ------------------------------------------------------

if ($result->num_rows !== 1) {

    $stmt->close();

    $_SESSION = [];

    session_destroy();

    header(
        'Location: ' . APP_URL . '/admin/login.php'
    );

    exit;
}


$admin = $result->fetch_assoc();

$stmt->close();


// ------------------------------------------------------
// Admin Blocked
// ------------------------------------------------------

if ($admin['status'] !== 'active') {

    $_SESSION = [];

    session_destroy();

    header(
        'Location: ' . APP_URL . '/admin/login.php'
    );

    exit;
}