<?php
/**
 * Smart DUET Admission Management System
 * Admin Applicant Action
 *
 * Purpose:
 * - Activate / Block applicant account
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
   ONLY POST REQUEST
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: ' .
        APP_URL .
        '/admin/applicants.php'
    );

    exit;
}


/* =========================================================
   CSRF CHECK
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
        '/admin/applicants.php'
    );

    exit;
}


/* =========================================================
   GET DATA
   ========================================================= */

$userId = (int) (
    $_POST['user_id'] ?? 0
);

$status = trim(
    $_POST['status'] ?? ''
);


/* =========================================================
   VALIDATION
   ========================================================= */

if ($userId <= 0) {

    setFlashMessage(
        'danger',
        'Invalid applicant account.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applicants.php'
    );

    exit;
}


$allowedStatuses = [
    'active',
    'blocked'
];


if (!in_array($status, $allowedStatuses, true)) {

    setFlashMessage(
        'danger',
        'Invalid account status.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applicants.php'
    );

    exit;
}


/* =========================================================
   CHECK USER
   ========================================================= */

$sql = "
    SELECT id, role, status
    FROM users
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
        '/admin/applicants.php'
    );

    exit;
}


$stmt->bind_param(
    'i',
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   USER NOT FOUND
   ========================================================= */

if (!$user) {

    setFlashMessage(
        'danger',
        'Applicant account not found.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applicants.php'
    );

    exit;
}


/* =========================================================
   DO NOT MODIFY ADMIN ACCOUNT
   ========================================================= */

if ($user['role'] !== 'applicant') {

    setFlashMessage(
        'danger',
        'Only applicant accounts can be updated here.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applicants.php'
    );

    exit;
}


/* =========================================================
   UPDATE STATUS
   ========================================================= */

$updateSql = "
    UPDATE users
    SET status = ?
    WHERE id = ?
    AND role = 'applicant'
";

$stmt = $conn->prepare($updateSql);

if (!$stmt) {

    setFlashMessage(
        'danger',
        'Unable to update account status.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/applicants.php'
    );

    exit;
}


$stmt->bind_param(
    'si',
    $status,
    $userId
);


$success = $stmt->execute();

$stmt->close();


/* =========================================================
   RESULT
   ========================================================= */

if (!$success) {

    setFlashMessage(
        'danger',
        'Failed to update applicant status.'
    );

} else {

    setFlashMessage(
        'success',
        'Applicant account status updated successfully.'
    );
}


/* =========================================================
   REDIRECT
   ========================================================= */

header(
    'Location: ' .
    APP_URL .
    '/admin/applicants.php'
);

exit;