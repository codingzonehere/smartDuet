<?php
/**
 * Smart DUET Admission Management System
 *
 * File: actions/signup_action.php
 *
 * Purpose:
 * Process applicant registration.
 */


// --------------------------------------------------
// Load Configuration and Common Functions
// --------------------------------------------------

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';


// --------------------------------------------------
// Only POST Request Allowed
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(APP_URL . '/signup.php');

}


// --------------------------------------------------
// Get Form Data
// --------------------------------------------------

$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';


// --------------------------------------------------
// Basic Validation
// --------------------------------------------------

if (
    empty($email) ||
    empty($mobile) ||
    empty($password) ||
    empty($confirmPassword)
) {

    setFlashMessage(
        'danger',
        'Please fill in all required fields.'
    );

    redirect(APP_URL . '/signup.php');

}


// --------------------------------------------------
// Validate Email
// --------------------------------------------------

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    setFlashMessage(
        'danger',
        'Please enter a valid email address.'
    );

    redirect(APP_URL . '/signup.php');

}


// --------------------------------------------------
// Validate Bangladesh Mobile Number
// --------------------------------------------------

if (!preg_match('/^01[3-9][0-9]{8}$/', $mobile)) {

    setFlashMessage(
        'danger',
        'Please enter a valid Bangladeshi mobile number.'
    );

    redirect(APP_URL . '/signup.php');

}


// --------------------------------------------------
// Validate Password Length
// --------------------------------------------------

if (strlen($password) < 6) {

    setFlashMessage(
        'danger',
        'Password must be at least 6 characters long.'
    );

    redirect(APP_URL . '/signup.php');

}


// --------------------------------------------------
// Check Password Match
// --------------------------------------------------

if ($password !== $confirmPassword) {

    setFlashMessage(
        'danger',
        'Password and confirm password do not match.'
    );

    redirect(APP_URL . '/signup.php');

}


// --------------------------------------------------
// Check Existing Email or Mobile
// --------------------------------------------------

$sql = "
    SELECT id
    FROM users
    WHERE email = ? OR mobile = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ss",
    $email,
    $mobile
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows > 0) {

    setFlashMessage(
        'danger',
        'An account already exists with this email or mobile number.'
    );

    $stmt->close();

    redirect(APP_URL . '/signup.php');

}

$stmt->close();


// --------------------------------------------------
// Hash Password
// --------------------------------------------------

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// --------------------------------------------------
// Create User Account
// --------------------------------------------------

$sql = "
    INSERT INTO users
    (
        email,
        mobile,
        password
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sss",
    $email,
    $mobile,
    $hashedPassword
);


// --------------------------------------------------
// Insert User
// --------------------------------------------------

if ($stmt->execute()) {

    $stmt->close();

    setFlashMessage(
        'success',
        'Account created successfully. Please login.'
    );

    redirect(APP_URL . '/login.php');

}


// --------------------------------------------------
// Registration Failed
// --------------------------------------------------

$stmt->close();

setFlashMessage(
    'danger',
    'Account creation failed. Please try again.'
);

redirect(APP_URL . '/signup.php');