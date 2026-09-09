<?php

/*
|--------------------------------------------------------------------------
| SMART DUET
| Admin Department Action
|--------------------------------------------------------------------------
| Purpose:
| - Update department seat number
|--------------------------------------------------------------------------
*/


// --------------------------------------------------
// Admin Authentication
// --------------------------------------------------

require_once __DIR__ . '/../auth.php';


// --------------------------------------------------
// Configuration + Functions
// --------------------------------------------------

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';


// --------------------------------------------------
// Only POST Request Allowed
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: ' .
        APP_URL .
        '/admin/departments.php'
    );

    exit;

}


// ==================================================
// CSRF VALIDATION
// ==================================================

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token']
    )
) {

    setFlashMessage(
        'danger',
        'Invalid security token. Please try again.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/departments.php'
    );

    exit;

}


// ==================================================
// ACTION
// ==================================================

$action =
    trim($_POST['action'] ?? '');


// ==================================================
// ONLY UPDATE SEATS
// ==================================================

if ($action !== 'update_seats') {

    setFlashMessage(
        'danger',
        'Invalid department action.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/departments.php'
    );

    exit;

}


// ==================================================
// GET INPUT
// ==================================================

$departmentId =
    filter_input(
        INPUT_POST,
        'department_id',
        FILTER_VALIDATE_INT
    );


$seats =
    filter_input(
        INPUT_POST,
        'seats',
        FILTER_VALIDATE_INT
    );


// ==================================================
// VALIDATE DEPARTMENT ID
// ==================================================

if (
    $departmentId === false ||
    $departmentId === null ||
    $departmentId <= 0
) {

    setFlashMessage(
        'danger',
        'Invalid department.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/departments.php'
    );

    exit;

}


// ==================================================
// VALIDATE SEAT NUMBER
// ==================================================
//
// Seat number must be:
// - Integer
// - 0 or greater
// - Maximum 10,000
//

if (
    $seats === false ||
    $seats === null ||
    $seats < 0 ||
    $seats > 10000
) {

    setFlashMessage(
        'danger',
        'Please enter a valid seat number.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/departments.php'
    );

    exit;

}


// ==================================================
// CHECK DEPARTMENT EXISTS
// ==================================================

$sql = "
    SELECT
        id,
        name
    FROM departments
    WHERE id = ?
    LIMIT 1
";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    setFlashMessage(
        'danger',
        'Unable to process the request.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/departments.php'
    );

    exit;

}


$stmt->bind_param(
    "i",
    $departmentId
);


$stmt->execute();


$result =
    $stmt->get_result();


$department =
    $result->fetch_assoc();


$stmt->close();


// ==================================================
// DEPARTMENT NOT FOUND
// ==================================================

if (!$department) {

    setFlashMessage(
        'danger',
        'Department not found.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/departments.php'
    );

    exit;

}


// ==================================================
// UPDATE SEATS
// ==================================================

$sql = "
    UPDATE departments
    SET seats = ?
    WHERE id = ?
    LIMIT 1
";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    setFlashMessage(
        'danger',
        'Failed to update seat number.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/departments.php'
    );

    exit;

}


$stmt->bind_param(
    "ii",
    $seats,
    $departmentId
);


if ($stmt->execute()) {

    setFlashMessage(
        'success',
        'Seat number updated successfully.'
    );

} else {

    setFlashMessage(
        'danger',
        'Failed to update seat number.'
    );

}


$stmt->close();


// ==================================================
// REDIRECT
// ==================================================

header(
    'Location: ' .
    APP_URL .
    '/admin/departments.php'
);

exit;

?>