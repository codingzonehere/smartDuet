<?php

/*
|--------------------------------------------------------------------------
| PROFILE ACTION
|--------------------------------------------------------------------------
| Save / Update Applicant Profile
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';


// --------------------------------------------------
// Only POST Request
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(APP_URL . '/profile.php');

}


// --------------------------------------------------
// Logged-in User
// --------------------------------------------------

$userId = getUserId();


// --------------------------------------------------
// Get Form Data
// --------------------------------------------------

$fullName = trim($_POST['full_name'] ?? '');

$fatherName = trim($_POST['father_name'] ?? '');

$motherName = trim($_POST['mother_name'] ?? '');

$mobile = trim($_POST['mobile'] ?? '');

$dateOfBirth = trim($_POST['date_of_birth'] ?? '');

$gender = trim($_POST['gender'] ?? '');

$identityNumber = trim($_POST['identity_number'] ?? '');


// --------------------------------------------------
// Required Field Validation
// --------------------------------------------------

if (
    empty($fullName) ||
    empty($fatherName) ||
    empty($motherName) ||
    empty($mobile) ||
    empty($dateOfBirth) ||
    empty($gender) ||
    empty($identityNumber)
) {

    setFlashMessage(
        'danger',
        'Please fill in all required fields.'
    );

    redirect(APP_URL . '/profile.php');

}


// --------------------------------------------------
// Mobile Validation
// --------------------------------------------------

if (!preg_match('/^01[3-9][0-9]{8}$/', $mobile)) {

    setFlashMessage(
        'danger',
        'Please enter a valid Bangladeshi mobile number.'
    );

    redirect(APP_URL . '/profile.php');

}


// --------------------------------------------------
// Gender Validation
// --------------------------------------------------

$allowedGenders = [
    'Male',
    'Female',
    'Other'
];

if (!in_array($gender, $allowedGenders, true)) {

    setFlashMessage(
        'danger',
        'Invalid gender selection.'
    );

    redirect(APP_URL . '/profile.php');

}


// --------------------------------------------------
// Check Whether Mobile Belongs to Another User
// --------------------------------------------------

$sql = "
    SELECT id
    FROM users
    WHERE mobile = ?
    AND id != ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "si",
    $mobile,
    $userId
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows > 0) {

    $stmt->close();

    setFlashMessage(
        'danger',
        'This mobile number is already used by another account.'
    );

    redirect(APP_URL . '/profile.php');

}

$stmt->close();


// --------------------------------------------------
// Update Mobile in Users Table
// --------------------------------------------------

$sql = "
    UPDATE users
    SET mobile = ?
    WHERE id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "si",
    $mobile,
    $userId
);

$stmt->execute();

$stmt->close();


// --------------------------------------------------
// Check Existing Applicant Profile
// --------------------------------------------------

$sql = "
    SELECT id
    FROM applicants
    WHERE user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$profileExists = ($result->num_rows === 1);

$existingProfile = null;

if ($profileExists) {

    $existingProfile = $result->fetch_assoc();

}

$stmt->close();


// --------------------------------------------------
// Insert or Update Applicant
// --------------------------------------------------

if ($profileExists) {

    // ----------------------------------------------
    // Update existing profile
    // ----------------------------------------------

    $sql = "
        UPDATE applicants

        SET
            full_name = ?,
            father_name = ?,
            mother_name = ?,
            date_of_birth = ?,
            gender = ?,
            identity_number = ?

        WHERE user_id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssssssi",
        $fullName,
        $fatherName,
        $motherName,
        $dateOfBirth,
        $gender,
        $identityNumber,
        $userId
    );


} else {

    // ----------------------------------------------
    // Create new applicant profile
    // ----------------------------------------------

    $sql = "
        INSERT INTO applicants
        (
            user_id,
            full_name,
            father_name,
            mother_name,
            date_of_birth,
            gender,
            identity_number
        )

        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "issssss",
        $userId,
        $fullName,
        $fatherName,
        $motherName,
        $dateOfBirth,
        $gender,
        $identityNumber
    );

}


// --------------------------------------------------
// Execute Profile Save
// --------------------------------------------------

if ($stmt->execute()) {

    $stmt->close();

    // Update session mobile
    $_SESSION['user_mobile'] = $mobile;

    setFlashMessage(
        'success',
        'Profile information updated successfully.'
    );

    redirect(APP_URL . '/profile.php');

}


// --------------------------------------------------
// Failed
// --------------------------------------------------

$stmt->close();

setFlashMessage(
    'danger',
    'Unable to update profile. Please try again.'
);

redirect(APP_URL . '/profile.php');