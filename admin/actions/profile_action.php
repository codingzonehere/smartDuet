<?php

// ======================================================
// Smart DUET Admission Management System
// Admin Profile Action
// ======================================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';


// ------------------------------------------------------
// Only POST request allowed
// ------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ' . APP_URL . '/admin/profile.php');
    exit;
}


// ------------------------------------------------------
// Admin Authentication
// ------------------------------------------------------

if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {

    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


// ------------------------------------------------------
// CSRF Protection
// ------------------------------------------------------

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {

    setFlashMessage(
        'danger',
        'Invalid security token. Please try again.'
    );

    header('Location: ' . APP_URL . '/admin/profile.php');
    exit;
}


// ------------------------------------------------------
// Admin ID
// ------------------------------------------------------

$adminId = (int) $_SESSION['admin_id'];


// ------------------------------------------------------
// Requested Action
// ------------------------------------------------------

$action = $_POST['action'] ?? '';


// ======================================================
// ACTION 1: UPDATE ACCOUNT INFORMATION
// ======================================================

if ($action === 'update_info') {

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');


    // --------------------------------------------------
    // Validation
    // --------------------------------------------------

    if ($fullName === '') {

        setFlashMessage(
            'danger',
            'Full name is required.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }


    if ($email === '') {

        setFlashMessage(
            'danger',
            'Email address is required.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        setFlashMessage(
            'danger',
            'Please enter a valid email address.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }


    if ($mobile === '') {

        setFlashMessage(
            'danger',
            'Mobile number is required.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }


    // --------------------------------------------------
    // Check Admin Exists
    // --------------------------------------------------

    $stmt = $conn->prepare(
        "SELECT id
         FROM admins
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->bind_param('i', $adminId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {

        $stmt->close();

        setFlashMessage(
            'danger',
            'Admin account not found.'
        );

        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }

    $stmt->close();


    // --------------------------------------------------
    // Check Duplicate Email
    // --------------------------------------------------

    $stmt = $conn->prepare(
        "SELECT id
         FROM admins
         WHERE email = ?
         AND id != ?
         LIMIT 1"
    );

    $stmt->bind_param(
        'si',
        $email,
        $adminId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $stmt->close();

        setFlashMessage(
            'danger',
            'This email is already used by another admin.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }

    $stmt->close();


    // --------------------------------------------------
    // Check Duplicate Mobile
    // --------------------------------------------------

    $stmt = $conn->prepare(
        "SELECT id
         FROM admins
         WHERE mobile = ?
         AND id != ?
         LIMIT 1"
    );

    $stmt->bind_param(
        'si',
        $mobile,
        $adminId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $stmt->close();

        setFlashMessage(
            'danger',
            'This mobile number is already used by another admin.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }

    $stmt->close();


    // --------------------------------------------------
    // Update Admin Information
    // --------------------------------------------------

    $stmt = $conn->prepare(
        "UPDATE admins
         SET full_name = ?,
             email = ?,
             mobile = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        'sssi',
        $fullName,
        $email,
        $mobile,
        $adminId
    );


    if ($stmt->execute()) {

        // Update session information
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_name'] = $fullName;

        setFlashMessage(
            'success',
            'Account information updated successfully.'
        );

    } else {

        setFlashMessage(
            'danger',
            'Failed to update account information.'
        );
    }

    $stmt->close();


    header('Location: ' . APP_URL . '/admin/profile.php');
    exit;
}


// ======================================================
// ACTION 2: CHANGE PASSWORD
// ======================================================

if ($action === 'change_password') {

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    // --------------------------------------------------
    // Validation
    // --------------------------------------------------

    if ($currentPassword === '') {

        setFlashMessage(
            'danger',
            'Please enter your current password.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }


    if ($newPassword === '') {

        setFlashMessage(
            'danger',
            'Please enter a new password.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }


    if (strlen($newPassword) < 6) {

        setFlashMessage(
            'danger',
            'New password must be at least 6 characters.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }


    if ($newPassword !== $confirmPassword) {

        setFlashMessage(
            'danger',
            'New passwords do not match.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }


    // --------------------------------------------------
    // Get Current Admin Password
    // --------------------------------------------------

    $stmt = $conn->prepare(
        "SELECT password
         FROM admins
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        'i',
        $adminId
    );

    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows !== 1) {

        $stmt->close();

        setFlashMessage(
            'danger',
            'Admin account not found.'
        );

        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }


    $admin = $result->fetch_assoc();

    $stmt->close();


    // --------------------------------------------------
    // Verify Current Password
    // --------------------------------------------------

    if (!password_verify(
        $currentPassword,
        $admin['password']
    )) {

        setFlashMessage(
            'danger',
            'Current password is incorrect.'
        );

        header('Location: ' . APP_URL . '/admin/profile.php');
        exit;
    }


    // --------------------------------------------------
    // Hash New Password
    // --------------------------------------------------

    $hashedPassword = password_hash(
        $newPassword,
        PASSWORD_DEFAULT
    );


    // --------------------------------------------------
    // Update Password
    // --------------------------------------------------

    $stmt = $conn->prepare(
        "UPDATE admins
         SET password = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        'si',
        $hashedPassword,
        $adminId
    );


    if ($stmt->execute()) {

        // Regenerate session ID
        session_regenerate_id(true);

        // Generate a new CSRF token
        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );

        setFlashMessage(
            'success',
            'Password changed successfully.'
        );

    } else {

        setFlashMessage(
            'danger',
            'Failed to change password.'
        );
    }

    $stmt->close();


    header('Location: ' . APP_URL . '/admin/profile.php');
    exit;
}


// ======================================================
// INVALID ACTION
// ======================================================

setFlashMessage(
    'danger',
    'Invalid request.'
);

header('Location: ' . APP_URL . '/admin/profile.php');
exit;