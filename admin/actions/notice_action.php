<?php
/**
 * Smart DUET Admission Management System
 * Admin Notice Action
 *
 * Purpose:
 * - Create notice
 * - Update notice
 * - Delete notice
 * - Upload / replace notice PDF
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
   POST ONLY
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: ' .
        APP_URL .
        '/admin/notices.php'
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
        '/admin/notices.php'
    );

    exit;
}


/* =========================================================
   ACTION
   ========================================================= */

$action = $_POST['action'] ?? '';


/* =========================================================
   DELETE
   ========================================================= */

if (
    $action === 'update' &&
    isset($_POST['delete_notice'])
) {

    $noticeId = (int) (
        $_POST['notice_id'] ?? 0
    );


    if ($noticeId <= 0) {

        setFlashMessage(
            'danger',
            'Invalid notice.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }


    /* Get PDF before deleting record */

    $sql = "
        SELECT pdf_file
        FROM notices
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
            '/admin/notices.php'
        );

        exit;
    }


    $stmt->bind_param(
        'i',
        $noticeId
    );

    $stmt->execute();

    $notice = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    if (!$notice) {

        setFlashMessage(
            'danger',
            'Notice not found.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }


    /* Delete database record */

    $deleteSql = "
        DELETE FROM notices
        WHERE id = ?
    ";

    $stmt = $conn->prepare($deleteSql);

    $stmt->bind_param(
        'i',
        $noticeId
    );

    $success = $stmt->execute();

    $stmt->close();


    if ($success) {

        /* Delete old PDF */

        if (!empty($notice['pdf_file'])) {

            $filePath =
                __DIR__ .
                '/../../assets/uploads/notices/' .
                basename($notice['pdf_file']);

            if (is_file($filePath)) {
                unlink($filePath);
            }
        }


        setFlashMessage(
            'success',
            'Notice deleted successfully.'
        );

    } else {

        setFlashMessage(
            'danger',
            'Failed to delete notice.'
        );
    }


    header(
        'Location: ' .
        APP_URL .
        '/admin/notices.php'
    );

    exit;
}


/* =========================================================
   GET FORM DATA
   ========================================================= */

$title = trim(
    $_POST['title'] ?? ''
);

$description = trim(
    $_POST['description'] ?? ''
);

$admissionYear = trim(
    $_POST['admission_year'] ?? ''
);

$publishedDate = trim(
    $_POST['published_date'] ?? ''
);

$status = trim(
    $_POST['status'] ?? ''
);


/* =========================================================
   VALIDATE TITLE
   ========================================================= */

if ($title === '') {

    setFlashMessage(
        'danger',
        'Notice title is required.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/notices.php'
    );

    exit;
}


if (strlen($title) > 255) {

    setFlashMessage(
        'danger',
        'Notice title is too long.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/notices.php'
    );

    exit;
}


/* =========================================================
   VALIDATE YEAR
   ========================================================= */

if ($admissionYear !== '') {

    if (
        !ctype_digit($admissionYear) ||
        (int) $admissionYear < 2000 ||
        (int) $admissionYear > 2100
    ) {

        setFlashMessage(
            'danger',
            'Invalid admission year.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }

    $admissionYear = (int) $admissionYear;

} else {

    $admissionYear = null;
}


/* =========================================================
   VALIDATE DATE
   ========================================================= */

$dateObject = DateTime::createFromFormat(
    'Y-m-d',
    $publishedDate
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $publishedDate
) {

    setFlashMessage(
        'danger',
        'Invalid published date.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/notices.php'
    );

    exit;
}


/* =========================================================
   VALIDATE STATUS
   ========================================================= */

if (!in_array(
    $status,
    ['published', 'draft'],
    true
)) {

    setFlashMessage(
        'danger',
        'Invalid notice status.'
    );

    header(
        'Location: ' .
        APP_URL .
        '/admin/notices.php'
    );

    exit;
}


/* =========================================================
   FILE UPLOAD
   ========================================================= */

$uploadedFileName = null;
$uploadedFilePath = null;


if (
    isset($_FILES['pdf_file']) &&
    $_FILES['pdf_file']['error'] !== UPLOAD_ERR_NO_FILE
) {

    $file = $_FILES['pdf_file'];


    /* Upload error */

    if ($file['error'] !== UPLOAD_ERR_OK) {

        setFlashMessage(
            'danger',
            'Failed to upload PDF.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }


    /* Maximum 5MB */

    if ($file['size'] > 5 * 1024 * 1024) {

        setFlashMessage(
            'danger',
            'PDF file must be 5MB or smaller.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }


    /* Check MIME type */

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mimeType = $finfo->file(
        $file['tmp_name']
    );


    if ($mimeType !== 'application/pdf') {

        setFlashMessage(
            'danger',
            'Only PDF files are allowed.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }


    /* Create directory */

    $uploadDirectory =
        __DIR__ .
        '/../../assets/uploads/notices/';


    if (!is_dir($uploadDirectory)) {

        if (!mkdir(
            $uploadDirectory,
            0755,
            true
        )) {

            setFlashMessage(
                'danger',
                'Unable to create upload directory.'
            );

            header(
                'Location: ' .
                APP_URL .
                '/admin/notices.php'
            );

            exit;
        }
    }


    /* Generate safe unique filename */

    $uploadedFileName =
        'notice_' .
        date('YmdHis') .
        '_' .
        bin2hex(random_bytes(5)) .
        '.pdf';


    $uploadedFilePath =
        $uploadDirectory .
        $uploadedFileName;


    /* Move file */

    if (!move_uploaded_file(
        $file['tmp_name'],
        $uploadedFilePath
    )) {

        setFlashMessage(
            'danger',
            'Unable to save PDF file.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }
}


/* =========================================================
   CREATE NOTICE
   ========================================================= */

if ($action === 'create') {


    $insertSql = "
        INSERT INTO notices (
            title,
            description,
            pdf_file,
            admission_year,
            published_date,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ";


    $stmt = $conn->prepare($insertSql);


    if (!$stmt) {

        if ($uploadedFilePath && is_file($uploadedFilePath)) {
            unlink($uploadedFilePath);
        }

        setFlashMessage(
            'danger',
            'Unable to create notice.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }


    $stmt->bind_param(
        'sssiss',
        $title,
        $description,
        $uploadedFileName,
        $admissionYear,
        $publishedDate,
        $status
    );


    $success = $stmt->execute();

    $stmt->close();


    if (!$success) {

        if ($uploadedFilePath && is_file($uploadedFilePath)) {
            unlink($uploadedFilePath);
        }

        setFlashMessage(
            'danger',
            'Failed to create notice.'
        );

    } else {

        setFlashMessage(
            'success',
            'Notice added successfully.'
        );
    }


    header(
        'Location: ' .
        APP_URL .
        '/admin/notices.php'
    );

    exit;
}


/* =========================================================
   UPDATE NOTICE
   ========================================================= */

if ($action === 'update') {


    $noticeId = (int) (
        $_POST['notice_id'] ?? 0
    );


    if ($noticeId <= 0) {

        if ($uploadedFilePath && is_file($uploadedFilePath)) {
            unlink($uploadedFilePath);
        }

        setFlashMessage(
            'danger',
            'Invalid notice.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }


    /* Get existing PDF */

    $checkSql = "
        SELECT pdf_file
        FROM notices
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($checkSql);

    $stmt->bind_param(
        'i',
        $noticeId
    );

    $stmt->execute();

    $existingNotice =
        $stmt
            ->get_result()
            ->fetch_assoc();

    $stmt->close();


    if (!$existingNotice) {

        if ($uploadedFilePath && is_file($uploadedFilePath)) {
            unlink($uploadedFilePath);
        }

        setFlashMessage(
            'danger',
            'Notice not found.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }


    /* Keep old PDF if no new PDF uploaded */

    $pdfFile = $existingNotice['pdf_file'];

    if ($uploadedFileName !== null) {
        $pdfFile = $uploadedFileName;
    }


    $updateSql = "
        UPDATE notices
        SET
            title = ?,
            description = ?,
            pdf_file = ?,
            admission_year = ?,
            published_date = ?,
            status = ?
        WHERE id = ?
    ";


    $stmt = $conn->prepare($updateSql);


    if (!$stmt) {

        if ($uploadedFilePath && is_file($uploadedFilePath)) {
            unlink($uploadedFilePath);
        }

        setFlashMessage(
            'danger',
            'Unable to update notice.'
        );

        header(
            'Location: ' .
            APP_URL .
            '/admin/notices.php'
        );

        exit;
    }


    $stmt->bind_param(
        'sssissi',
        $title,
        $description,
        $pdfFile,
        $admissionYear,
        $publishedDate,
        $status,
        $noticeId
    );


    $success = $stmt->execute();

    $stmt->close();


    if (!$success) {

        if ($uploadedFilePath && is_file($uploadedFilePath)) {
            unlink($uploadedFilePath);
        }

        setFlashMessage(
            'danger',
            'Failed to update notice.'
        );

    } else {


        /* Delete old PDF after successful replacement */

        if (
            $uploadedFileName !== null &&
            !empty($existingNotice['pdf_file'])
        ) {

            $oldFilePath =
                __DIR__ .
                '/../../assets/uploads/notices/' .
                basename(
                    $existingNotice['pdf_file']
                );

            if (is_file($oldFilePath)) {
                unlink($oldFilePath);
            }
        }


        setFlashMessage(
            'success',
            'Notice updated successfully.'
        );
    }


    header(
        'Location: ' .
        APP_URL .
        '/admin/notices.php'
    );

    exit;
}


/* =========================================================
   INVALID ACTION
   ========================================================= */

if ($uploadedFilePath && is_file($uploadedFilePath)) {
    unlink($uploadedFilePath);
}


setFlashMessage(
    'danger',
    'Invalid notice action.'
);


header(
    'Location: ' .
    APP_URL .
    '/admin/notices.php'
);

exit;