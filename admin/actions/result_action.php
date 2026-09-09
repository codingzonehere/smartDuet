<?php

/**
 * Smart DUET Admission Management System
 * Admin Result Action
 *
 * Purpose:
 * - Create result
 * - Update result
 * - Automatically use current system date
 */

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';


// ======================================================
// ONLY POST REQUEST
// ======================================================

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    redirect(
        APP_URL . '/admin/results.php'
    );

    exit;
}


// ======================================================
// CSRF CHECK
// ======================================================

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

    redirect(
        APP_URL . '/admin/results.php'
    );

    exit;
}


// ======================================================
// FORM DATA
// ======================================================

$applicationId =
    (int) (
        $_POST['application_id'] ?? 0
    );


$meritPositionInput =
    trim(
        $_POST['merit_position'] ?? ''
    );


$resultStatus =
    trim(
        $_POST['result_status'] ?? ''
    );


$remarks =
    trim(
        $_POST['remarks'] ?? ''
    );


// ======================================================
// VALID APPLICATION ID
// ======================================================

if (
    $applicationId <= 0
) {

    setFlashMessage(
        'danger',
        'Invalid application.'
    );

    redirect(
        APP_URL . '/admin/results.php'
    );

    exit;
}


// ======================================================
// VALID RESULT STATUS
// ======================================================

$allowedStatuses = [

    'pending',
    'selected',
    'waiting',
    'not_selected'

];


if (
    !in_array(
        $resultStatus,
        $allowedStatuses,
        true
    )
) {

    setFlashMessage(
        'danger',
        'Invalid result status.'
    );

    redirect(
        APP_URL . '/admin/results.php'
    );

    exit;
}


// ======================================================
// MERIT POSITION
// ======================================================
//
// Merit position is optional for Pending.
//
// But for Selected / Waiting / Not Selected,
// if entered, it must be a positive integer.
//
// ======================================================

$meritPosition = null;


if ($meritPositionInput !== '') {

    if (
        !ctype_digit(
            $meritPositionInput
        )
    ) {

        setFlashMessage(
            'danger',
            'Merit position must be a valid positive number.'
        );

        redirect(
            APP_URL . '/admin/results.php'
        );

        exit;
    }


    $meritPosition =
        (int) $meritPositionInput;


    if (
        $meritPosition <= 0
    ) {

        setFlashMessage(
            'danger',
            'Merit position must be greater than zero.'
        );

        redirect(
            APP_URL . '/admin/results.php'
        );

        exit;
    }

}


// ======================================================
// CHECK APPLICATION
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id,
        application_no,
        status

    FROM applications

    WHERE id = ?

    LIMIT 1
");


$stmt->bind_param(
    'i',
    $applicationId
);


if (!$stmt->execute()) {

    $stmt->close();

    setFlashMessage(
        'danger',
        'Unable to verify application.'
    );

    redirect(
        APP_URL . '/admin/results.php'
    );

    exit;
}


$application =
    $stmt->get_result()->fetch_assoc();


$stmt->close();


if (!$application) {

    setFlashMessage(
        'danger',
        'Application not found.'
    );

    redirect(
        APP_URL . '/admin/results.php'
    );

    exit;
}


// ======================================================
// CHECK EXISTING RESULT
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id

    FROM results

    WHERE application_id = ?

    LIMIT 1
");


$stmt->bind_param(
    'i',
    $applicationId
);


if (!$stmt->execute()) {

    $stmt->close();

    setFlashMessage(
        'danger',
        'Unable to check existing result.'
    );

    redirect(
        APP_URL . '/admin/results.php'
    );

    exit;
}


$existingResult =
    $stmt->get_result()->fetch_assoc();


$stmt->close();


// ======================================================
// START TRANSACTION
// ======================================================

$conn->begin_transaction();


try {


    // ==================================================
    // UPDATE EXISTING RESULT
    // ==================================================

    if ($existingResult) {


        $resultId =
            (int) $existingResult['id'];


        /*
        |--------------------------------------------------
        | published_date is automatically updated
        | with current system date.
        |
        | No date input is required from admin.
        |--------------------------------------------------
        */


        $stmt = $conn->prepare("
            UPDATE results

            SET
                merit_position = ?,
                result_status = ?,
                published_date = CURDATE(),
                remarks = ?

            WHERE id = ?
        ");


        $stmt->bind_param(
            'issi',
            $meritPosition,
            $resultStatus,
            $remarks,
            $resultId
        );


        if (!$stmt->execute()) {

            throw new Exception(
                'Unable to update result.'
            );
        }


        $stmt->close();


    } else {


        // ==================================================
        // CREATE NEW RESULT
        // ==================================================

        /*
        |--------------------------------------------------
        | published_date automatically comes from
        | current system date.
        |--------------------------------------------------
        */


        $stmt = $conn->prepare("
            INSERT INTO results
            (
                application_id,
                merit_position,
                result_status,
                published_date,
                remarks
            )

            VALUES
            (
                ?,
                ?,
                ?,
                CURDATE(),
                ?
            )
        ");


        $stmt->bind_param(
            'iiss',
            $applicationId,
            $meritPosition,
            $resultStatus,
            $remarks
        );


        if (!$stmt->execute()) {

            throw new Exception(
                'Unable to save result.'
            );
        }


        $stmt->close();

    }


    // ==================================================
    // COMMIT
    // ==================================================

    $conn->commit();


    // ==================================================
    // SUCCESS
    // ==================================================

    setFlashMessage(
        'success',
        'Result saved successfully.'
    );


    // ==================================================
    // REDIRECT
    // ==================================================

    redirect(
        APP_URL . '/admin/results.php'
    );

    exit;


} catch (Throwable $e) {


    // ==================================================
    // ROLLBACK
    // ==================================================

    $conn->rollback();


    setFlashMessage(
        'danger',
        'Unable to save result. Please try again.'
    );


    redirect(
        APP_URL . '/admin/results.php'
    );

    exit;
}