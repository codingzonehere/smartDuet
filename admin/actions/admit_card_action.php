<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';


/*
|--------------------------------------------------------------------------
| Only POST Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(
        APP_URL . '/admin/admit-cards.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

$csrfToken =
    $_POST['csrf_token'] ?? '';


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
        'Invalid security token.'
    );

    redirect(
        APP_URL . '/admin/admit-cards.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Action
|--------------------------------------------------------------------------
*/

$action =
    $_POST['action'] ?? '';



/*
|--------------------------------------------------------------------------
| ==============================================================
| SAVE AUTHORIZATION SIGNATURE
| ==============================================================
|--------------------------------------------------------------------------
*/

if (
    $action ===
    'save_authorization_signature'
) {


    /*
    |--------------------------------------------------------------------------
    | Check File
    |--------------------------------------------------------------------------
    */

    if (
        !isset(
            $_FILES[
                'authorization_signature'
            ]
        )
        ||
        $_FILES[
            'authorization_signature'
        ]['error']
        !== UPLOAD_ERR_OK
    ) {

        setFlashMessage(
            'danger',
            'Please select an authorization signature.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    $file =
        $_FILES[
            'authorization_signature'
        ];


    /*
    |--------------------------------------------------------------------------
    | Maximum File Size: 500 KB
    |--------------------------------------------------------------------------
    */

    if (
        $file['size']
        > 500 * 1024
    ) {

        setFlashMessage(
            'danger',
            'Signature file must be 500 KB or smaller.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate MIME Type
    |--------------------------------------------------------------------------
    */

    $finfo =
        new finfo(
            FILEINFO_MIME_TYPE
        );


    $mimeType =
        $finfo->file(
            $file['tmp_name']
        );


    $allowedTypes = [

        'image/jpeg' => 'jpg',

        'image/png' => 'png'

    ];


    if (
        !isset(
            $allowedTypes[
                $mimeType
            ]
        )
    ) {

        setFlashMessage(
            'danger',
            'Only JPG, JPEG or PNG signature files are allowed.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Actual Image
    |--------------------------------------------------------------------------
    */

    if (
        !getimagesize(
            $file['tmp_name']
        )
    ) {

        setFlashMessage(
            'danger',
            'Invalid signature image.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Upload Directory
    |--------------------------------------------------------------------------
    */

    $uploadDirectory =
        __DIR__ .
        '/../../assets/uploads/signatures/';


    if (
        !is_dir(
            $uploadDirectory
        )
    ) {

        if (
            !mkdir(
                $uploadDirectory,
                0755,
                true
            )
        ) {

            setFlashMessage(
                'danger',
                'Unable to create signature upload directory.'
            );

            redirect(
                APP_URL . '/admin/admit-cards.php'
            );

            exit;
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Generate Safe Filename
    |--------------------------------------------------------------------------
    */

    $extension =
        $allowedTypes[
            $mimeType
        ];


    $newFilename =
        'authorization_signature_' .
        date('YmdHis') .
        '_' .
        bin2hex(
            random_bytes(5)
        ) .
        '.' .
        $extension;


    $destination =
        $uploadDirectory .
        $newFilename;


    /*
    |--------------------------------------------------------------------------
    | Move Uploaded File
    |--------------------------------------------------------------------------
    */

    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {

        setFlashMessage(
            'danger',
            'Failed to upload authorization signature.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Old Signature
    |--------------------------------------------------------------------------
    */

    $oldSignature = null;


    $stmt = $conn->prepare("
        SELECT
            authorization_signature

        FROM admit_card_settings

        WHERE id = 1

        LIMIT 1
    ");


    $stmt->execute();


    $oldSetting =
        $stmt->get_result()->fetch_assoc();


    $stmt->close();


    if ($oldSetting) {

        $oldSignature =
            $oldSetting[
                'authorization_signature'
            ];

    }


    /*
    |--------------------------------------------------------------------------
    | Save New Signature
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        UPDATE admit_card_settings

        SET
            authorization_signature = ?

        WHERE id = 1
    ");


    $stmt->bind_param(
        's',
        $newFilename
    );


    if (!$stmt->execute()) {

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | Delete New File
        |--------------------------------------------------------------------------
        */

        if (
            file_exists(
                $destination
            )
        ) {

            unlink(
                $destination
            );

        }


        setFlashMessage(
            'danger',
            'Failed to save authorization signature.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Delete Old Signature
    |--------------------------------------------------------------------------
    */

    if (
        !empty(
            $oldSignature
        )
    ) {

        $oldFile =
            $uploadDirectory .
            basename(
                $oldSignature
            );


        if (
            file_exists(
                $oldFile
            )
            &&
            is_file(
                $oldFile
            )
        ) {

            unlink(
                $oldFile
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    setFlashMessage(
        'success',
        'Authorization signature saved successfully.'
    );


    redirect(
        APP_URL . '/admin/admit-cards.php'
    );

    exit;
}



/*
|--------------------------------------------------------------------------
| ==============================================================
| SAVE / UPDATE SCHEDULE
| ==============================================================
|--------------------------------------------------------------------------
*/

if (
    $action ===
    'save_schedule'
) {


    $scheduleId =
        (int)(
            $_POST['schedule_id']
            ?? 0
        );


    $admissionYear =
        (int)(
            $_POST['admission_year']
            ?? 0
        );


    $departmentId =
        (int)(
            $_POST['department_id']
            ?? 0
        );


    $examDate =
        trim(
            $_POST['exam_date']
            ?? ''
        );


    $examShift =
        trim(
            $_POST['exam_shift']
            ?? ''
        );


    $examCenter =
        trim(
            $_POST['exam_center']
            ?? ''
        );


    $status =
        trim(
            $_POST['status']
            ?? 'draft'
        );


    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if (
        $admissionYear < 2000 ||
        $admissionYear > 2100
    ) {

        setFlashMessage(
            'danger',
            'Invalid admission year.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    if (
        $departmentId <= 0
    ) {

        setFlashMessage(
            'danger',
            'Please select a department.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    if (
        $examDate === ''
    ) {

        setFlashMessage(
            'danger',
            'Exam date is required.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    if (
        $examShift === ''
    ) {

        setFlashMessage(
            'danger',
            'Exam shift/time is required.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    if (
        $examCenter === ''
    ) {

        setFlashMessage(
            'danger',
            'Exam center is required.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Status
    |--------------------------------------------------------------------------
    */

    if (
        !in_array(
            $status,
            [
                'draft',
                'published'
            ],
            true
        )
    ) {

        setFlashMessage(
            'danger',
            'Invalid schedule status.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Date
    |--------------------------------------------------------------------------
    */

    $dateObject =
        DateTime::createFromFormat(
            'Y-m-d',
            $examDate
        );


    if (
        !$dateObject ||
        $dateObject->format(
            'Y-m-d'
        )
        !==
        $examDate
    ) {

        setFlashMessage(
            'danger',
            'Invalid exam date.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Department
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            id,
            code

        FROM departments

        WHERE id = ?

          AND is_active = 1

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

        setFlashMessage(
            'danger',
            'Selected department was not found.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Schedule
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            id

        FROM admit_card_schedules

        WHERE admission_year = ?

          AND department_id = ?

          AND id != ?

        LIMIT 1
    ");


    $stmt->bind_param(
        'iii',
        $admissionYear,
        $departmentId,
        $scheduleId
    );


    $stmt->execute();


    $duplicate =
        $stmt->get_result()->fetch_assoc();


    $stmt->close();


    if ($duplicate) {

        setFlashMessage(
            'warning',
            'A schedule already exists for this department and admission year.'
        );

        redirect(
            APP_URL .
            '/admin/admit-cards.php?schedule_id=' .
            (int)$duplicate['id']
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Transaction
    |--------------------------------------------------------------------------
    */

    $conn->begin_transaction();


    try {


        /*
        |--------------------------------------------------------------------------
        | UPDATE Existing Schedule
        |--------------------------------------------------------------------------
        */

        if (
            $scheduleId > 0
        ) {


            $stmt = $conn->prepare("
                UPDATE admit_card_schedules

                SET

                    admission_year = ?,

                    department_id = ?,

                    exam_date = ?,

                    exam_shift = ?,

                    exam_center = ?,

                    status = ?

                WHERE id = ?
            ");


            $stmt->bind_param(
                'iissssi',
                $admissionYear,
                $departmentId,
                $examDate,
                $examShift,
                $examCenter,
                $status,
                $scheduleId
            );


            if (
                !$stmt->execute()
            ) {

                throw new Exception(
                    'Schedule update failed.'
                );
            }


            $stmt->close();

        }


        /*
        |--------------------------------------------------------------------------
        | INSERT New Schedule
        |--------------------------------------------------------------------------
        */

        else {


            $stmt = $conn->prepare("
                INSERT INTO admit_card_schedules
                (
                    admission_year,
                    department_id,
                    exam_date,
                    exam_shift,
                    exam_center,
                    status
                )

                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");


            $stmt->bind_param(
                'iissss',
                $admissionYear,
                $departmentId,
                $examDate,
                $examShift,
                $examCenter,
                $status
            );


            if (
                !$stmt->execute()
            ) {

                throw new Exception(
                    'Schedule creation failed.'
                );
            }


            $scheduleId =
                $conn->insert_id;


            $stmt->close();

        }


        /*
        |--------------------------------------------------------------------------
        | If Published
        |--------------------------------------------------------------------------
        |
        | Generate admit cards for paid applicants.
        |
        */

        if (
            $status ===
            'published'
        ) {

            generateAdmitCards(
                $conn,
                $scheduleId
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Commit
        |--------------------------------------------------------------------------
        */

        $conn->commit();


        setFlashMessage(
            'success',
            $status === 'published'

                ? 'Schedule published and paid applicants\' admit cards generated successfully.'

                : 'Admit card schedule saved successfully.'
        );


        redirect(
            APP_URL .
            '/admin/admit-cards.php?schedule_id=' .
            (int)$scheduleId
        );

        exit;


    } catch (
        Throwable $e
    ) {


        $conn->rollback();


        setFlashMessage(
            'danger',
            'Unable to save admit card schedule.'
        );


        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;

    }

}



/*
|--------------------------------------------------------------------------
| ==============================================================
| PUBLISH / SYNC SCHEDULE
| ==============================================================
|--------------------------------------------------------------------------
*/

if (
    $action ===
    'publish_schedule'
) {


    $scheduleId =
        (int)(
            $_POST['schedule_id']
            ?? 0
        );


    if (
        $scheduleId <= 0
    ) {

        setFlashMessage(
            'danger',
            'Invalid schedule.'
        );

        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Start Transaction
    |--------------------------------------------------------------------------
    */

    $conn->begin_transaction();


    try {


        /*
        |--------------------------------------------------------------------------
        | Check Schedule
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT

                id,
                admission_year,
                department_id,
                exam_date,
                exam_shift,
                exam_center,
                status

            FROM admit_card_schedules

            WHERE id = ?

            LIMIT 1
        ");


        $stmt->bind_param(
            'i',
            $scheduleId
        );


        $stmt->execute();


        $schedule =
            $stmt->get_result()->fetch_assoc();


        $stmt->close();


        if (!$schedule) {

            throw new Exception(
                'Schedule not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Publish Schedule
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE admit_card_schedules

            SET status = 'published'

            WHERE id = ?
        ");


        $stmt->bind_param(
            'i',
            $scheduleId
        );


        if (
            !$stmt->execute()
        ) {

            throw new Exception(
                'Unable to publish schedule.'
            );
        }


        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | Generate / Sync Cards
        |--------------------------------------------------------------------------
        */

        $generated =
            generateAdmitCards(
                $conn,
                $scheduleId
            );


        /*
        |--------------------------------------------------------------------------
        | Commit
        |--------------------------------------------------------------------------
        */

        $conn->commit();


        setFlashMessage(
            'success',
            $generated .
            ' paid applicant admit card(s) generated/synced successfully.'
        );


        redirect(
            APP_URL .
            '/admin/admit-cards.php?schedule_id=' .
            (int)$scheduleId
        );

        exit;


    } catch (
        Throwable $e
    ) {


        $conn->rollback();


        setFlashMessage(
            'danger',
            'Unable to publish admit cards.'
        );


        redirect(
            APP_URL . '/admin/admit-cards.php'
        );

        exit;

    }

}



/*
|--------------------------------------------------------------------------
| Unknown Action
|--------------------------------------------------------------------------
*/

setFlashMessage(
    'danger',
    'Invalid action.'
);


redirect(
    APP_URL . '/admin/admit-cards.php'
);

exit;



/*
|--------------------------------------------------------------------------
| FUNCTION: Generate Admit Cards
|--------------------------------------------------------------------------
|
| This function:
|
| 1. Gets published schedule.
| 2. Finds only PAID applicants.
| 3. Generates automatic seat numbers.
| 4. Generates automatic admit card numbers.
| 5. Creates missing cards.
| 6. Updates unpublished cards.
| 7. Never overwrites already published cards.
|
|--------------------------------------------------------------------------
*/

function generateAdmitCards(
    mysqli $conn,
    int $scheduleId
): int {


    /*
    |--------------------------------------------------------------------------
    | Get Schedule
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT

            id,
            admission_year,
            department_id,
            exam_date,
            exam_shift,
            exam_center,
            status

        FROM admit_card_schedules

        WHERE id = ?

        LIMIT 1
    ");


    $stmt->bind_param(
        'i',
        $scheduleId
    );


    $stmt->execute();


    $schedule =
        $stmt->get_result()->fetch_assoc();


    $stmt->close();


    if (!$schedule) {

        throw new Exception(
            'Schedule not found.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Schedule Must Be Published
    |--------------------------------------------------------------------------
    */

    if (
        $schedule['status']
        !==
        'published'
    ) {

        throw new Exception(
            'Schedule is not published.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Department Code
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            code

        FROM departments

        WHERE id = ?

        LIMIT 1
    ");


    $stmt->bind_param(
        'i',
        $schedule['department_id']
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


    /*
    |--------------------------------------------------------------------------
    | Clean Department Code
    |--------------------------------------------------------------------------
    */

    $departmentCode =
        strtoupper(
            preg_replace(
                '/[^A-Za-z0-9]/',
                '',
                $department['code']
            )
        );


    if (
        $departmentCode === ''
    ) {

        $departmentCode =
            'DEPT';

    }


    /*
    |--------------------------------------------------------------------------
    | Get PAID Applicants
    |--------------------------------------------------------------------------
    |
    | Only:
    |
    | payment_status = paid
    |
    | AND valid application status.
    |
    */

    $stmt = $conn->prepare("
        SELECT

            a.id AS application_id,

            a.application_no,

            a.admission_year,

            a.department_id

        FROM applications a

        INNER JOIN payments p
            ON p.application_id =
               a.id

        WHERE

            a.department_id = ?

            AND a.admission_year = ?

            AND p.payment_status =
                'paid'

            AND a.status IN (
                'submitted',
                'under_review',
                'accepted'
            )

        ORDER BY
            a.id ASC
    ");


    $stmt->bind_param(
        'ii',
        $schedule['department_id'],
        $schedule['admission_year']
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $applicants = [];


    while (
        $row =
        $result->fetch_assoc()
    ) {

        $applicants[] =
            $row;

    }


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Generate Cards
    |--------------------------------------------------------------------------
    */

    $generatedCount = 0;


    foreach (
        $applicants
        as $index => $applicant
    ) {


        /*
        |--------------------------------------------------------------------------
        | Automatic Seat Number
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | CSE-001
        | CSE-002
        | CSE-003
        |
        */

        $seatNumber =
            $departmentCode .
            '-' .
            str_pad(
                $index + 1,
                3,
                '0',
                STR_PAD_LEFT
            );


        /*
        |--------------------------------------------------------------------------
        | Automatic Admit Card Number
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | DUET-AC-2026-CSE-0001
        |
        */

        $admitCardNo =
            'DUET-AC-' .
            $schedule['admission_year'] .
            '-' .
            $departmentCode .
            '-' .
            str_pad(
                $index + 1,
                4,
                '0',
                STR_PAD_LEFT
            );


        /*
        |--------------------------------------------------------------------------
        | Check Existing Admit Card
        |--------------------------------------------------------------------------
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
            $applicant[
                'application_id'
            ]
        );


        $stmt->execute();


        $existing =
            $stmt->get_result()->fetch_assoc();


        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | CREATE NEW
        |--------------------------------------------------------------------------
        */

        if (!$existing) {


            /*
            |--------------------------------------------------------------------------
            | PDF file remains NULL.
            |--------------------------------------------------------------------------
            |
            | PDF is not stored on server.
            | Applicant prints the admit card from browser.
            |
            */

            $pdfFile = null;


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

                $applicant[
                    'application_id'
                ],

                $admitCardNo,

                $schedule[
                    'exam_date'
                ],

                $schedule[
                    'exam_shift'
                ],

                $schedule[
                    'exam_center'
                ],

                $seatNumber,

                $pdfFile
            );


            if (
                !$stmt->execute()
            ) {

                throw new Exception(
                    'Failed to create admit card.'
                );
            }


            $stmt->close();


            $generatedCount++;


        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE Existing Unpublished Card
        |--------------------------------------------------------------------------
        */

        elseif (
            $existing['status']
            ===
            'not_published'
        ) {


            $stmt = $conn->prepare("
                UPDATE admit_cards

                SET

                    admit_card_no = ?,

                    exam_date = ?,

                    exam_shift = ?,

                    exam_center = ?,

                    seat_number = ?,

                    status = 'published'

                WHERE id = ?
            ");


            $stmt->bind_param(
                'sssssi',

                $admitCardNo,

                $schedule[
                    'exam_date'
                ],

                $schedule[
                    'exam_shift'
                ],

                $schedule[
                    'exam_center'
                ],

                $seatNumber,

                $existing['id']
            );


            if (
                !$stmt->execute()
            ) {

                throw new Exception(
                    'Failed to update admit card.'
                );
            }


            $stmt->close();


            $generatedCount++;

        }


        /*
        |--------------------------------------------------------------------------
        | Already Published
        |--------------------------------------------------------------------------
        |
        | Do nothing.
        |
        */

    }


    return $generatedCount;
}