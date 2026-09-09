<?php

/*
|--------------------------------------------------------------------------
| SMART DUET ADMISSION MANAGEMENT SYSTEM
|--------------------------------------------------------------------------
| File: admin/actions/payment_action.php
|
| Purpose:
| Admin payment verification.
|
| Main Logic:
| 1. Verify payment
| 2. Update payment status
| 3. Update application status
| 4. If department schedule is already published,
|    automatically generate admit card
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../auth.php';

require_once __DIR__ . '/../../config/config.php';

require_once __DIR__ . '/../../includes/functions.php';


/*
|--------------------------------------------------------------------------
| ONLY POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(
        APP_URL . '/admin/applications.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CSRF TOKEN CHECK
|--------------------------------------------------------------------------
*/

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
        APP_URL . '/admin/applications.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$paymentId =
    (int) ($_POST['payment_id'] ?? 0);

$newPaymentStatus =
    trim(
        $_POST['payment_status'] ?? ''
    );


/*
|--------------------------------------------------------------------------
| VALIDATE PAYMENT ID
|--------------------------------------------------------------------------
*/

if ($paymentId <= 0) {

    setFlashMessage(
        'danger',
        'Invalid payment.'
    );

    redirect(
        APP_URL . '/admin/applications.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| VALID PAYMENT STATUS
|--------------------------------------------------------------------------
*/

$allowedStatuses = [

    'pending',
    'paid',
    'failed'

];


if (
    !in_array(
        $newPaymentStatus,
        $allowedStatuses,
        true
    )
) {

    setFlashMessage(
        'danger',
        'Invalid payment status.'
    );

    redirect(
        APP_URL . '/admin/applications.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| START DATABASE TRANSACTION
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();


try {


    /*
    |--------------------------------------------------------------------------
    | GET PAYMENT + APPLICATION INFORMATION
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT

            p.id AS payment_id,
            p.application_id,
            p.payment_status AS old_payment_status,

            a.application_no,
            a.status AS application_status,
            a.admission_year,
            a.department_id

        FROM payments p

        INNER JOIN applications a
            ON a.id = p.application_id

        WHERE p.id = ?

        LIMIT 1
    ");


    $stmt->bind_param(
        'i',
        $paymentId
    );


    if (!$stmt->execute()) {

        throw new Exception(
            'Unable to find payment.'
        );
    }


    $payment =
        $stmt->get_result()->fetch_assoc();


    $stmt->close();


    if (!$payment) {

        throw new Exception(
            'Payment record not found.'
        );
    }


    $applicationId =
        (int) $payment['application_id'];

    $admissionYear =
        (int) $payment['admission_year'];

    $departmentId =
        (int) $payment['department_id'];


    /*
    |--------------------------------------------------------------------------
    | UPDATE PAYMENT
    |--------------------------------------------------------------------------
    */

    if ($newPaymentStatus === 'paid') {

        $stmt = $conn->prepare("
            UPDATE payments

            SET
                payment_status = 'paid',
                paid_at = NOW()

            WHERE id = ?
        ");

    } else {

        $stmt = $conn->prepare("
            UPDATE payments

            SET
                payment_status = ?,
                paid_at = NULL

            WHERE id = ?
        ");

    }


    if ($newPaymentStatus === 'paid') {

        $stmt->bind_param(
            'i',
            $paymentId
        );

    } else {

        $stmt->bind_param(
            'si',
            $newPaymentStatus,
            $paymentId
        );

    }


    if (!$stmt->execute()) {

        throw new Exception(
            'Unable to update payment status.'
        );
    }


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | APPLICATION STATUS
    |--------------------------------------------------------------------------
    |
    | Payment Paid:
    | payment_pending -> submitted
    |
    | Payment Failed:
    | submitted -> payment_pending
    |
    | Pending:
    | Keep existing application status.
    |
    |--------------------------------------------------------------------------
    */


    if ($newPaymentStatus === 'paid') {


        /*
        |----------------------------------------------------------------------
        | Only move payment_pending to submitted
        |----------------------------------------------------------------------
        */

        if (
            $payment['application_status']
            === 'payment_pending'
        ) {

            $stmt = $conn->prepare("
                UPDATE applications

                SET
                    status = 'submitted'

                WHERE id = ?
            ");


            $stmt->bind_param(
                'i',
                $applicationId
            );


            if (!$stmt->execute()) {

                throw new Exception(
                    'Unable to update application status.'
                );
            }


            $stmt->close();

        }


    } elseif (
        $newPaymentStatus === 'failed'
    ) {


        /*
        |----------------------------------------------------------------------
        | Move submitted application back to payment_pending
        |----------------------------------------------------------------------
        */

        if (
            $payment['application_status']
            === 'submitted'
        ) {

            $stmt = $conn->prepare("
                UPDATE applications

                SET
                    status = 'payment_pending'

                WHERE id = ?
            ");


            $stmt->bind_param(
                'i',
                $applicationId
            );


            if (!$stmt->execute()) {

                throw new Exception(
                    'Unable to update application status.'
                );
            }


            $stmt->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | AUTOMATIC ADMIT CARD GENERATION
    |--------------------------------------------------------------------------
    |
    | This happens ONLY when:
    |
    | 1. Payment becomes paid
    | 2. Department schedule exists
    | 3. Schedule is published
    |
    |--------------------------------------------------------------------------
    */

    $admitCardGenerated = false;


    if ($newPaymentStatus === 'paid') {


        /*
        |----------------------------------------------------------------------
        | Find Published Schedule
        |----------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT

                id,
                admission_year,
                department_id,
                exam_date,
                exam_shift,
                exam_center

            FROM admit_card_schedules

            WHERE admission_year = ?
              AND department_id = ?
              AND status = 'published'

            LIMIT 1
        ");


        $stmt->bind_param(
            'ii',
            $admissionYear,
            $departmentId
        );


        if (!$stmt->execute()) {

            throw new Exception(
                'Unable to check admit card schedule.'
            );
        }


        $schedule =
            $stmt->get_result()->fetch_assoc();


        $stmt->close();


        /*
        |----------------------------------------------------------------------
        | If Published Schedule Exists
        |----------------------------------------------------------------------
        */

        if ($schedule) {


            /*
            |------------------------------------------------------------------
            | Check Existing Admit Card
            |------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                SELECT

                    id,
                    status

                FROM admit_cards

                WHERE application_id = ?

                LIMIT 1
            ");


            $stmt->bind_param(
                'i',
                $applicationId
            );


            if (!$stmt->execute()) {

                throw new Exception(
                    'Unable to check existing admit card.'
                );
            }


            $existingAdmitCard =
                $stmt->get_result()->fetch_assoc();


            $stmt->close();


            /*
            |------------------------------------------------------------------
            | Generate Only If Missing
            |------------------------------------------------------------------
            */

            if (!$existingAdmitCard) {


                /*
                |--------------------------------------------------------------
                | GET DEPARTMENT CODE
                |--------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    SELECT code

                    FROM departments

                    WHERE id = ?

                    LIMIT 1
                ");


                $stmt->bind_param(
                    'i',
                    $departmentId
                );


                if (!$stmt->execute()) {

                    throw new Exception(
                        'Unable to find department.'
                    );
                }


                $department =
                    $stmt->get_result()->fetch_assoc();


                $stmt->close();


                if (!$department) {

                    throw new Exception(
                        'Department not found.'
                    );
                }


                /*
                |--------------------------------------------------------------
                | CLEAN DEPARTMENT CODE
                |--------------------------------------------------------------
                */

                $departmentCode =
                    strtoupper(
                        preg_replace(
                            '/[^A-Za-z0-9]/',
                            '',
                            $department['code']
                        )
                    );


                if ($departmentCode === '') {

                    $departmentCode = 'DEPT';

                }


                /*
                |--------------------------------------------------------------
                | FIND NEXT SEAT NUMBER
                |--------------------------------------------------------------
                |
                | Example:
                |
                | Existing:
                | CSE-001
                | CSE-002
                |
                | New:
                | CSE-003
                |
                |--------------------------------------------------------------
                */

                $seatPrefix =
                    $departmentCode . '-';


                $stmt = $conn->prepare("
                    SELECT
                        seat_number

                    FROM admit_cards ac

                    INNER JOIN applications a
                        ON a.id = ac.application_id

                    WHERE a.department_id = ?
                      AND a.admission_year = ?
                      AND ac.status = 'published'
                      AND ac.seat_number LIKE ?

                    ORDER BY ac.id DESC

                    LIMIT 1
                ");


                $seatLike =
                    $seatPrefix . '%';


                $stmt->bind_param(
                    'iis',
                    $departmentId,
                    $admissionYear,
                    $seatLike
                );


                if (!$stmt->execute()) {

                    throw new Exception(
                        'Unable to calculate seat number.'
                    );
                }


                $lastSeat =
                    $stmt->get_result()->fetch_assoc();


                $stmt->close();


                /*
                |--------------------------------------------------------------
                | NEXT SEAT
                |--------------------------------------------------------------
                */

                $nextSeatNumber = 1;


                if ($lastSeat) {

                    $lastSeatValue =
                        (string)
                        $lastSeat['seat_number'];


                    $lastNumber =
                        (int)
                        substr(
                            $lastSeatValue,
                            strlen($seatPrefix)
                        );


                    if ($lastNumber > 0) {

                        $nextSeatNumber =
                            $lastNumber + 1;

                    }

                }


                $seatNumber =
                    $departmentCode .
                    '-' .
                    str_pad(
                        $nextSeatNumber,
                        3,
                        '0',
                        STR_PAD_LEFT
                    );


                /*
                |--------------------------------------------------------------
                | ADMIT CARD NUMBER
                |--------------------------------------------------------------
                |
                | Example:
                |
                | DUET-AC-2026-CSE-0003
                |
                |--------------------------------------------------------------
                */

                $cardNumber =
                    'DUET-AC-' .
                    $admissionYear .
                    '-' .
                    $departmentCode .
                    '-' .
                    str_pad(
                        $nextSeatNumber,
                        4,
                        '0',
                        STR_PAD_LEFT
                    );


                /*
                |--------------------------------------------------------------
                | EXTRA SAFETY:
                | Check card number uniqueness
                |--------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    SELECT id

                    FROM admit_cards

                    WHERE admit_card_no = ?

                    LIMIT 1
                ");


                $stmt->bind_param(
                    's',
                    $cardNumber
                );


                $stmt->execute();


                $cardExists =
                    $stmt->get_result()->fetch_assoc();


                $stmt->close();


                if ($cardExists) {

                    /*
                    |----------------------------------------------------------
                    | If somehow the number already exists,
                    | generate using application ID.
                    |----------------------------------------------------------
                    */

                    $cardNumber =
                        'DUET-AC-' .
                        $admissionYear .
                        '-' .
                        $departmentCode .
                        '-' .
                        str_pad(
                            $applicationId,
                            4,
                            '0',
                            STR_PAD_LEFT
                        );

                }


                /*
                |--------------------------------------------------------------
                | PDF FILE
                |--------------------------------------------------------------
                |
                | We DO NOT store PDF.
                | Applicant side creates print-ready PDF through browser.
                |
                |--------------------------------------------------------------
                */

                $pdfFile = null;


                /*
                |--------------------------------------------------------------
                | INSERT ADMIT CARD
                |--------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    INSERT INTO admit_cards
                    (
                        application_id,
                        admit_card_no,
                        exam_date,
                        exam_shift,
                        exam_center,
                        seat_number,
                        pdf_file,
                        status
                    )

                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        'published'
                    )
                ");


                $stmt->bind_param(
                    'issssss',
                    $applicationId,
                    $cardNumber,
                    $schedule['exam_date'],
                    $schedule['exam_shift'],
                    $schedule['exam_center'],
                    $seatNumber,
                    $pdfFile
                );


                if (!$stmt->execute()) {

                    throw new Exception(
                        'Unable to generate admit card.'
                    );
                }


                $stmt->close();


                $admitCardGenerated = true;

            }


            /*
            |------------------------------------------------------------------
            | Existing Unpublished Admit Card
            |------------------------------------------------------------------
            */

            elseif (
                $existingAdmitCard['status']
                === 'not_published'
            ) {


                /*
                |--------------------------------------------------------------
                | Get Department Code
                |--------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    SELECT code

                    FROM departments

                    WHERE id = ?

                    LIMIT 1
                ");


                $stmt->bind_param(
                    'i',
                    $departmentId
                );


                $stmt->execute();


                $department =
                    $stmt->get_result()->fetch_assoc();


                $stmt->close();


                if (!$department) {

                    throw new Exception(
                        'Department not found.'
                    );
                }


                $departmentCode =
                    strtoupper(
                        preg_replace(
                            '/[^A-Za-z0-9]/',
                            '',
                            $department['code']
                        )
                    );


                if ($departmentCode === '') {

                    $departmentCode = 'DEPT';

                }


                /*
                |--------------------------------------------------------------
                | Keep existing seat/card if available
                |--------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    UPDATE admit_cards

                    SET
                        exam_date = ?,
                        exam_shift = ?,
                        exam_center = ?,
                        status = 'published'

                    WHERE id = ?
                ");


                $stmt->bind_param(
                    'sssi',
                    $schedule['exam_date'],
                    $schedule['exam_shift'],
                    $schedule['exam_center'],
                    $existingAdmitCard['id']
                );


                if (!$stmt->execute()) {

                    throw new Exception(
                        'Unable to publish admit card.'
                    );
                }


                $stmt->close();


                $admitCardGenerated = true;

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | COMMIT TRANSACTION
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    */

    if ($newPaymentStatus === 'paid') {


        if ($admitCardGenerated) {

            $message =
                'Payment verified successfully. ' .
                'Application submitted and admit card generated automatically.';

        } else {

            $message =
                'Payment verified successfully. ' .
                'Application submitted. Admit card will be generated when the department schedule is published.';

        }

    } elseif (
        $newPaymentStatus === 'failed'
    ) {

        $message =
            'Payment marked as failed. Application moved to payment pending.';

    } else {

        $message =
            'Payment status changed to pending.';

    }


    setFlashMessage(
        'success',
        $message
    );


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    redirect(
        APP_URL .
        '/admin/applications.php'
    );

    exit;


} catch (Throwable $e) {


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    $conn->rollback();


    /*
    |--------------------------------------------------------------------------
    | ERROR MESSAGE
    |--------------------------------------------------------------------------
    */

    setFlashMessage(
        'danger',
        'Unable to update payment. Please try again.'
    );


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    redirect(
        APP_URL .
        '/admin/applications.php'
    );

    exit;

}