<?php
/**
 * Smart DUET Admission Management System
 * Admin Application Action
 *
 * Purpose:
 * - Update application status
 * - Uses existing applications table
 * - No extra table required
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';


/* =========================================================
   ADMIN AUTHENTICATION
   ========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


/* =========================================================
   ONLY POST REQUEST ALLOWED
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: ' .
        APP_URL .
        '/admin/applications.php'
    );

    exit;
}


/* =========================================================
   CSRF TOKEN CHECK
   ========================================================= */

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    empty($csrfToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $csrfToken
    )
) {

    setFlashMessage(
        'danger',
        'Invalid security token. Please try again.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applications.php'
    );

    exit;
}


/* =========================================================
   GET FORM DATA
   ========================================================= */

$applicationId = (int) (
    $_POST['application_id'] ?? 0
);

$applicationNo = trim(
    $_POST['application_no'] ?? ''
);

$status = trim(
    $_POST['status'] ?? ''
);


/* =========================================================
   BASIC VALIDATION
   ========================================================= */

if ($applicationId <= 0) {

    setFlashMessage(
        'danger',
        'Invalid application.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applications.php'
    );

    exit;
}


/* =========================================================
   ALLOWED APPLICATION STATUS
   ========================================================= */

$allowedStatuses = [
    'submitted',
    'under_review',
    'accepted',
    'rejected'
];


if (!in_array($status, $allowedStatuses, true)) {

    setFlashMessage(
        'danger',
        'Invalid application status.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applications.php'
    );

    exit;
}


/* =========================================================
   CHECK APPLICATION EXISTS
   ========================================================= */

$sql = "
    SELECT
        id,
        application_no,
        status
    FROM applications
    WHERE id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    setFlashMessage(
        'danger',
        'Database error occurred.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applications.php'
    );

    exit;
}


$stmt->bind_param(
    'i',
    $applicationId
);

$stmt->execute();

$result = $stmt->get_result();

$application = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   APPLICATION NOT FOUND
   ========================================================= */

if (!$application) {

    setFlashMessage(
        'danger',
        'Application not found.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applications.php'
    );

    exit;
}


/* =========================================================
   UPDATE APPLICATION STATUS
   ========================================================= */

$updateSql = "
    UPDATE applications
    SET status = ?
    WHERE id = ?
";

$stmt = $conn->prepare($updateSql);

if (!$stmt) {

    setFlashMessage(
        'danger',
        'Unable to update application status.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applications.php'
    );

    exit;
}


$stmt->bind_param(
    'si',
    $status,
    $applicationId
);


$success = $stmt->execute();

$stmt->close();


/* =========================================================
   RESULT
   ========================================================= */

if (!$success) {

    setFlashMessage(
        'danger',
        'Failed to update application status.'
    );

} else {

    setFlashMessage(
        'success',
        'Application status updated successfully.'
    );
}


/* =========================================================
   REDIRECT BACK TO SEARCH RESULT
   ========================================================= */

if ($applicationNo === '') {
    $applicationNo = $application['application_no'];
}


header(
    'Location: ' .
    APP_URL .
    '/admin/applications.php?search=' .
    urlencode($applicationNo)
);

exit;