<?php

/*
|--------------------------------------------------------------------------
| SETTINGS ACTION
|--------------------------------------------------------------------------
| Applicant Account Settings
|
| Handles:
| 1. Email update
| 2. Password change
|--------------------------------------------------------------------------
*/


// --------------------------------------------------
// Authentication + Common Functions
// --------------------------------------------------

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';


// --------------------------------------------------
// Only POST Request Allowed
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(APP_URL . '/settings.php');

}


// --------------------------------------------------
// Get Logged-in User ID
// --------------------------------------------------

$userId = getUserId();


// --------------------------------------------------
// Get Requested Action
// --------------------------------------------------

$action = trim($_POST['action'] ?? '');


// ==================================================
// 1. UPDATE EMAIL
// ==================================================

if ($action === 'update_email') {


    // --------------------------------------------------
    // Get Email
    // --------------------------------------------------

    $email = trim($_POST['email'] ?? '');


    // --------------------------------------------------
    // Check Empty
    // --------------------------------------------------

    if (empty($email)) {

        setFlashMessage(
            'danger',
            'Email address is required.'
        );

        redirect(APP_URL . '/settings.php');

    }


    // --------------------------------------------------
    // Validate Email
    // --------------------------------------------------

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        setFlashMessage(
            'danger',
            'Please enter a valid email address.'
        );

        redirect(APP_URL . '/settings.php');

    }


    // --------------------------------------------------
    // Check Whether Email Already Exists
    // --------------------------------------------------

    $sql = "
        SELECT id
        FROM users
        WHERE email = ?
        AND id != ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "si",
        $email,
        $userId
    );

    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows > 0) {

        $stmt->close();

        setFlashMessage(
            'danger',
            'This email address is already used by another account.'
        );

        redirect(APP_URL . '/settings.php');

    }

    $stmt->close();


    // --------------------------------------------------
    // Update Email
    // --------------------------------------------------

    $sql = "
        UPDATE users
        SET email = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "si",
        $email,
        $userId
    );


    if ($stmt->execute()) {

        // Update email stored in current session
        $_SESSION['user_email'] = $email;

        $stmt->close();

        setFlashMessage(
            'success',
            'Email address updated successfully.'
        );

        redirect(APP_URL . '/settings.php');

    }


    // --------------------------------------------------
    // Email Update Failed
    // --------------------------------------------------

    $stmt->close();

    setFlashMessage(
        'danger',
        'Unable to update email address. Please try again.'
    );

    redirect(APP_URL . '/settings.php');
}



// ==================================================
// 2. CHANGE PASSWORD
// ==================================================

if ($action === 'change_password') {


    // --------------------------------------------------
    // Get Password Data
    // --------------------------------------------------

    $currentPassword =
        $_POST['current_password'] ?? '';

    $newPassword =
        $_POST['new_password'] ?? '';

    $confirmPassword =
        $_POST['confirm_password'] ?? '';


    // --------------------------------------------------
    // Check Empty Fields
    // --------------------------------------------------

    if (
        empty($currentPassword) ||
        empty($newPassword) ||
        empty($confirmPassword)
    ) {

        setFlashMessage(
            'danger',
            'Please fill in all password fields.'
        );

        redirect(APP_URL . '/settings.php');

    }


    // --------------------------------------------------
    // Check New Password Length
    // --------------------------------------------------

    if (strlen($newPassword) < 6) {

        setFlashMessage(
            'danger',
            'New password must be at least 6 characters long.'
        );

        redirect(APP_URL . '/settings.php');

    }


    // --------------------------------------------------
    // Check Password Match
    // --------------------------------------------------

    if ($newPassword !== $confirmPassword) {

        setFlashMessage(
            'danger',
            'New password and confirm password do not match.'
        );

        redirect(APP_URL . '/settings.php');

    }


    // --------------------------------------------------
    // Get Current Password from Database
    // --------------------------------------------------

    $sql = "
        SELECT password
        FROM users
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $userId
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
            'User account could not be found.'
        );

        redirect(APP_URL . '/settings.php');

    }


    $user = $result->fetch_assoc();

    $stmt->close();


    // --------------------------------------------------
    // Verify Current Password
    // --------------------------------------------------

    if (
        !password_verify(
            $currentPassword,
            $user['password']
        )
    ) {

        setFlashMessage(
            'danger',
            'Current password is incorrect.'
        );

        redirect(APP_URL . '/settings.php');

    }


    // --------------------------------------------------
    // Prevent Same Password
    // --------------------------------------------------

    if (
        password_verify(
            $newPassword,
            $user['password']
        )
    ) {

        setFlashMessage(
            'danger',
            'New password must be different from your current password.'
        );

        redirect(APP_URL . '/settings.php');

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

    $sql = "
        UPDATE users
        SET password = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "si",
        $hashedPassword,
        $userId
    );


    // --------------------------------------------------
    // Password Update Successful
    // --------------------------------------------------

    if ($stmt->execute()) {

        $stmt->close();

        setFlashMessage(
            'success',
            'Password changed successfully.'
        );

        redirect(APP_URL . '/settings.php');

    }


    // --------------------------------------------------
    // Password Update Failed
    // --------------------------------------------------

    $stmt->close();

    setFlashMessage(
        'danger',
        'Unable to change password. Please try again.'
    );

    redirect(APP_URL . '/settings.php');
}



// ==================================================
// INVALID ACTION
// ==================================================

setFlashMessage(
    'danger',
    'Invalid settings request.'
);

redirect(APP_URL . '/settings.php');