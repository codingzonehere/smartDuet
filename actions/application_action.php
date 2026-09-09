<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';


/*
|--------------------------------------------------------------------------
| Only POST Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(APP_URL . '/apply.php');

}


$userId = getUserId();


if (!$userId) {

    redirect(APP_URL . '/login.php');

}


/*
|--------------------------------------------------------------------------
| Get Applicant
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id
    FROM applicants
    WHERE user_id = ?
    LIMIT 1
");


if (!$stmt) {

    setFlashMessage(
        'danger',
        'Database error: ' . $conn->error
    );

    redirect(APP_URL . '/apply.php');

}


$stmt->bind_param(
    'i',
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$applicant = $result->fetch_assoc();

$stmt->close();


if (!$applicant) {

    setFlashMessage(
        'danger',
        'Applicant profile not found. Please complete your profile first.'
    );

    redirect(APP_URL . '/profile.php');

}


$applicantId =
    (int) $applicant['id'];


/*
|--------------------------------------------------------------------------
| Receive Form Data
|--------------------------------------------------------------------------
*/

$sscGpa =
    (float) ($_POST['ssc_gpa'] ?? 0);


$diplomaCgpa =
    (float) ($_POST['diploma_cgpa'] ?? 0);


$diplomaPassingYear =
    (int) ($_POST['diploma_passing_year'] ?? 0);


$technologyId =
    (int) ($_POST['technology_id'] ?? 0);


$departmentId =
    (int) ($_POST['department_id'] ?? 0);


$quota =
    trim($_POST['quota'] ?? '');


$paymentMethod =
    trim($_POST['payment_method'] ?? '');


$transactionId =
    trim($_POST['transaction_id'] ?? '');


$declaration =
    $_POST['declaration'] ?? '';


$currentYear =
    (int) date('Y');


/*
|--------------------------------------------------------------------------
| BASIC VALIDATION
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SSC GPA
|--------------------------------------------------------------------------
*/

if (
    $sscGpa <= 0 ||
    $sscGpa > 5.00
) {

    setFlashMessage(
        'danger',
        'SSC / Equivalent GPA must be between 0.01 and 5.00.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Diploma CGPA
|--------------------------------------------------------------------------
*/

if (
    $diplomaCgpa <= 0 ||
    $diplomaCgpa > 4.00
) {

    setFlashMessage(
        'danger',
        'Diploma CGPA must be between 0.01 and 4.00.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Technology
|--------------------------------------------------------------------------
*/

if ($technologyId <= 0) {

    setFlashMessage(
        'danger',
        'Please select your Diploma Technology.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Department
|--------------------------------------------------------------------------
*/

if ($departmentId <= 0) {

    setFlashMessage(
        'danger',
        'Please select a department.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Passing Year
|--------------------------------------------------------------------------
*/

$allowedPassingYears = [

    $currentYear,
    $currentYear - 1,
    $currentYear - 2

];


if (
    !in_array(
        $diplomaPassingYear,
        $allowedPassingYears,
        true
    )
) {

    setFlashMessage(
        'danger',
        'Invalid Diploma Passing Year.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Quota
|--------------------------------------------------------------------------
*/

$allowedQuotas = [

    'None',
    "Freedom Fighter's Son/Daughter",
    'Tribal'

];


if (
    !in_array(
        $quota,
        $allowedQuotas,
        true
    )
) {

    setFlashMessage(
        'danger',
        'Please select a valid quota.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Payment Method
|--------------------------------------------------------------------------
*/

$allowedPaymentMethods = [

    'Rocket',
    'bKash',
    'Agrani Education Fee Pay'

];


if (
    !in_array(
        $paymentMethod,
        $allowedPaymentMethods,
        true
    )
) {

    setFlashMessage(
        'danger',
        'Please select a valid payment method.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Transaction ID
|--------------------------------------------------------------------------
*/

if ($transactionId === '') {

    setFlashMessage(
        'danger',
        'Please enter your Payment / Transaction ID.'
    );

    redirect(APP_URL . '/apply.php');

}


if (strlen($transactionId) > 100) {

    setFlashMessage(
        'danger',
        'Transaction ID is too long.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Declaration
|--------------------------------------------------------------------------
*/

if ($declaration !== '1') {

    setFlashMessage(
        'danger',
        'Please accept the declaration before submitting.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Check Technology
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        name
    FROM diploma_technologies
    WHERE id = ?
      AND is_active = 1
    LIMIT 1
");


if (!$stmt) {

    setFlashMessage(
        'danger',
        'Database error while checking technology.'
    );

    redirect(APP_URL . '/apply.php');

}


$stmt->bind_param(
    'i',
    $technologyId
);

$stmt->execute();

$result =
    $stmt->get_result();

$technology =
    $result->fetch_assoc();

$stmt->close();


if (!$technology) {

    setFlashMessage(
        'danger',
        'Selected Diploma Technology was not found or is inactive.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Check Department
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        code,
        name,
        faculty,
        degree,
        duration,
        seats
    FROM departments
    WHERE id = ?
      AND is_active = 1
    LIMIT 1
");


if (!$stmt) {

    setFlashMessage(
        'danger',
        'Database error while checking department.'
    );

    redirect(APP_URL . '/apply.php');

}


$stmt->bind_param(
    'i',
    $departmentId
);

$stmt->execute();

$result =
    $stmt->get_result();

$department =
    $result->fetch_assoc();

$stmt->close();


if (!$department) {

    setFlashMessage(
        'danger',
        'Selected department was not found or is inactive.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Check Technology + Department Mapping
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id
    FROM technology_department_eligibility
    WHERE technology_id = ?
      AND department_id = ?
    LIMIT 1
");


if (!$stmt) {

    setFlashMessage(
        'danger',
        'Database error while checking eligibility mapping.'
    );

    redirect(APP_URL . '/apply.php');

}


$stmt->bind_param(
    'ii',
    $technologyId,
    $departmentId
);

$stmt->execute();

$result =
    $stmt->get_result();

$mapping =
    $result->fetch_assoc();

$stmt->close();


if (!$mapping) {

    setFlashMessage(
        'danger',
        'The selected Diploma Technology is not eligible for this department.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Duplicate Application Check
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        application_no,
        status
    FROM applications
    WHERE applicant_id = ?
      AND admission_year = ?
    LIMIT 1
");


if (!$stmt) {

    setFlashMessage(
        'danger',
        'Database error while checking existing application.'
    );

    redirect(APP_URL . '/apply.php');

}


$stmt->bind_param(
    'ii',
    $applicantId,
    $currentYear
);

$stmt->execute();

$result =
    $stmt->get_result();

$existingApplication =
    $result->fetch_assoc();

$stmt->close();


if ($existingApplication) {

    setFlashMessage(
        'warning',
        'You already have an application for the current admission year.'
    );

    redirect(
        APP_URL .
        '/application-status.php'
    );

}


/*
|--------------------------------------------------------------------------
| FILE VALIDATION FUNCTION
|--------------------------------------------------------------------------
*/

function validateUpload(
    string $fieldName,
    string $label,
    array $extensions,
    array $mimeTypes,
    int $maxSize
): array {


    /*
    --------------------------------------------------------------
    | Check File
    --------------------------------------------------------------
    */

    if (
        !isset($_FILES[$fieldName]) ||
        $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE
    ) {

        return [
            'success' => false,
            'message' => $label . ' is required.'
        ];

    }


    /*
    --------------------------------------------------------------
    | PHP Upload Error
    --------------------------------------------------------------
    */

    if (
        $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK
    ) {

        return [
            'success' => false,
            'message' =>
                $label .
                ' could not be uploaded.'
        ];

    }


    /*
    --------------------------------------------------------------
    | Size
    --------------------------------------------------------------
    */

    $size =
        (int) $_FILES[$fieldName]['size'];


    if ($size > $maxSize) {

        $maxKB =
            round(
                $maxSize / 1024
            );


        return [
            'success' => false,
            'message' =>
                $label .
                ' must be ' .
                $maxKB .
                ' KB or smaller.'
        ];

    }


    /*
    --------------------------------------------------------------
    | Extension
    --------------------------------------------------------------
    */

    $originalName =
        $_FILES[$fieldName]['name'];


    $extension =
        strtolower(
            pathinfo(
                $originalName,
                PATHINFO_EXTENSION
            )
        );


    if (
        !in_array(
            $extension,
            $extensions,
            true
        )
    ) {

        return [
            'success' => false,
            'message' =>
                $label .
                ' has an invalid file format.'
        ];

    }


    /*
    --------------------------------------------------------------
    | MIME
    --------------------------------------------------------------
    */

    $mimeType = '';


    if (function_exists('finfo_open')) {

        $finfo =
            finfo_open(
                FILEINFO_MIME_TYPE
            );


        if ($finfo) {

            $mimeType =
                finfo_file(
                    $finfo,
                    $_FILES[$fieldName]['tmp_name']
                );


            finfo_close($finfo);

        }

    }


    if (
        $mimeType !== '' &&
        !in_array(
            $mimeType,
            $mimeTypes,
            true
        )
    ) {

        return [
            'success' => false,
            'message' =>
                $label .
                ' contains an invalid file type.'
        ];

    }


    return [

        'success' => true,

        'tmp_name' =>
            $_FILES[$fieldName]['tmp_name'],

        'extension' =>
            $extension

    ];

}


/*
|--------------------------------------------------------------------------
| Validate Photo
|--------------------------------------------------------------------------
*/

$photo =
    validateUpload(

        'photo',

        'Applicant photo',

        [
            'jpg',
            'jpeg',
            'png'
        ],

        [
            'image/jpeg',
            'image/png'
        ],

        100 * 1024

    );


if (!$photo['success']) {

    setFlashMessage(
        'danger',
        $photo['message']
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Validate Signature
|--------------------------------------------------------------------------
*/

$signatureFile =
    validateUpload(

        'signature',

        'Signature',

        [
            'jpg',
            'jpeg',
            'png'
        ],

        [
            'image/jpeg',
            'image/png'
        ],

        100 * 1024

    );


if (!$signatureFile['success']) {

    setFlashMessage(
        'danger',
        $signatureFile['message']
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Validate Identity Document
|--------------------------------------------------------------------------
*/

$identity =
    validateUpload(

        'identity_document',

        'NID / Birth Registration document',

        [
            'jpg',
            'jpeg',
            'png',
            'pdf'
        ],

        [
            'image/jpeg',
            'image/png',
            'application/pdf'
        ],

        5 * 1024 * 1024

    );


if (!$identity['success']) {

    setFlashMessage(
        'danger',
        $identity['message']
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Validate Quota Document
|--------------------------------------------------------------------------
*/

$quotaFile = null;


if ($quota !== 'None') {

    $quotaFile =
        validateUpload(

            'quota_document',

            'Quota supporting document',

            [
                'pdf'
            ],

            [
                'application/pdf'
            ],

            5 * 1024 * 1024

        );


    if (!$quotaFile['success']) {

        setFlashMessage(
            'danger',
            $quotaFile['message']
        );

        redirect(APP_URL . '/apply.php');

    }

}


/*
|--------------------------------------------------------------------------
| Create Upload Directories
|--------------------------------------------------------------------------
*/

$photoDir =
    __DIR__ .
    '/../assets/uploads/photos/';


$signatureDir =
    __DIR__ .
    '/../assets/uploads/signatures/';


$documentDir =
    __DIR__ .
    '/../assets/uploads/documents/';


$quotaDir =
    __DIR__ .
    '/../assets/uploads/quota/';


$directories = [

    $photoDir,
    $signatureDir,
    $documentDir,
    $quotaDir

];


foreach ($directories as $directory) {

    if (!is_dir($directory)) {

        if (!mkdir(
            $directory,
            0777,
            true
        )) {

            setFlashMessage(
                'danger',
                'Upload directory could not be created.'
            );

            redirect(APP_URL . '/apply.php');

        }

    }

}


/*
|--------------------------------------------------------------------------
| Generate File Names
|--------------------------------------------------------------------------
*/

$applicantPhoto =
    'photo_' .
    uniqid('', true) .
    '.' .
    $photo['extension'];


$signature =
    'signature_' .
    uniqid('', true) .
    '.' .
    $signatureFile['extension'];


$identityDocument =
    'identity_' .
    uniqid('', true) .
    '.' .
    $identity['extension'];


$quotaDocument = null;


if (
    $quota !== 'None' &&
    $quotaFile !== null
) {

    $quotaDocument =
        'quota_' .
        uniqid('', true) .
        '.' .
        $quotaFile['extension'];

}


/*
|--------------------------------------------------------------------------
| Target Paths
|--------------------------------------------------------------------------
*/

$photoPath =
    $photoDir .
    $applicantPhoto;


$signaturePath =
    $signatureDir .
    $signature;


$identityPath =
    $documentDir .
    $identityDocument;


$quotaPath = null;


if ($quotaDocument) {

    $quotaPath =
        $quotaDir .
        $quotaDocument;

}


/*
|--------------------------------------------------------------------------
| Move Photo
|--------------------------------------------------------------------------
*/

if (
    !move_uploaded_file(
        $photo['tmp_name'],
        $photoPath
    )
) {

    setFlashMessage(
        'danger',
        'Applicant photo could not be saved.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Move Signature
|--------------------------------------------------------------------------
*/

if (
    !move_uploaded_file(
        $signatureFile['tmp_name'],
        $signaturePath
    )
) {

    if (file_exists($photoPath)) {
        unlink($photoPath);
    }

    setFlashMessage(
        'danger',
        'Signature could not be saved.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Move Identity Document
|--------------------------------------------------------------------------
*/

if (
    !move_uploaded_file(
        $identity['tmp_name'],
        $identityPath
    )
) {

    if (file_exists($photoPath)) {
        unlink($photoPath);
    }

    if (file_exists($signaturePath)) {
        unlink($signaturePath);
    }

    setFlashMessage(
        'danger',
        'NID / Birth Registration document could not be saved.'
    );

    redirect(APP_URL . '/apply.php');

}


/*
|--------------------------------------------------------------------------
| Move Quota Document
|--------------------------------------------------------------------------
*/

if ($quotaDocument) {

    if (
        !move_uploaded_file(
            $quotaFile['tmp_name'],
            $quotaPath
        )
    ) {

        if (file_exists($photoPath)) {
            unlink($photoPath);
        }

        if (file_exists($signaturePath)) {
            unlink($signaturePath);
        }

        if (file_exists($identityPath)) {
            unlink($identityPath);
        }

        setFlashMessage(
            'danger',
            'Quota supporting document could not be saved.'
        );

        redirect(APP_URL . '/apply.php');

    }

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
    | Temporary Application Number
    |--------------------------------------------------------------------------
    */

    $tempApplicationNo =
        'TEMP-' .
        $currentYear .
        '-' .
        strtoupper(
            substr(
                uniqid(),
                -8
            )
        );


    /*
    |--------------------------------------------------------------------------
    | INSERT APPLICATION
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | There are 9 values.
    |
    | s i i d d i i i s
    |
    | This was one of the main problems in the previous code.
    |
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        INSERT INTO applications
        (
            application_no,
            applicant_id,
            admission_year,
            ssc_gpa,
            diploma_cgpa,
            diploma_passing_year,
            technology_id,
            department_id,
            quota,
            status,
            submitted_at
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
            ?,
            ?,
            'payment_pending',
            NOW()
        )
    ");


    if (!$stmt) {

        throw new Exception(
            'Application prepare failed: ' .
            $conn->error
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CORRECT BIND PARAM
    |--------------------------------------------------------------------------
    */

    $stmt->bind_param(

        'siiddiiis',

        $tempApplicationNo,

        $applicantId,

        $currentYear,

        $sscGpa,

        $diplomaCgpa,

        $diplomaPassingYear,

        $technologyId,

        $departmentId,

        $quota

    );


    if (!$stmt->execute()) {

        throw new Exception(
            'Application insert failed: ' .
            $stmt->error
        );

    }


    $applicationId =
        $conn->insert_id;


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Final Application Number
    |--------------------------------------------------------------------------
    */

    $applicationNo =
        'SDUET-' .
        $currentYear .
        '-' .
        str_pad(
            (string) $applicationId,
            6,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Update Application Number
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        UPDATE applications
        SET application_no = ?
        WHERE id = ?
    ");


    if (!$stmt) {

        throw new Exception(
            'Application number update failed: ' .
            $conn->error
        );

    }


    $stmt->bind_param(
        'si',
        $applicationNo,
        $applicationId
    );


    if (!$stmt->execute()) {

        throw new Exception(
            'Application number update failed: ' .
            $stmt->error
        );

    }


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | INSERT DOCUMENTS
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        INSERT INTO documents
        (
            application_id,
            applicant_photo,
            signature,
            identity_document,
            quota_document
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");


    if (!$stmt) {

        throw new Exception(
            'Document prepare failed: ' .
            $conn->error
        );

    }


    $stmt->bind_param(

        'issss',

        $applicationId,

        $applicantPhoto,

        $signature,

        $identityDocument,

        $quotaDocument

    );


    if (!$stmt->execute()) {

        throw new Exception(
            'Document insert failed: ' .
            $stmt->error
        );

    }


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | INSERT PAYMENT
    |--------------------------------------------------------------------------
    */

    $amount = 1500;

    $paymentStatus = 'pending';


    $stmt = $conn->prepare("
        INSERT INTO payments
        (
            application_id,
            payment_method,
            amount,
            transaction_id,
            payment_status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");


    if (!$stmt) {

        throw new Exception(
            'Payment prepare failed: ' .
            $conn->error
        );

    }


    /*
    |
    | i = application_id
    | s = payment_method
    | i = amount
    | s = transaction_id
    | s = payment_status
    |
    */

    $stmt->bind_param(

        'isiss',

        $applicationId,

        $paymentMethod,

        $amount,

        $transactionId,

        $paymentStatus

    );


    if (!$stmt->execute()) {

        throw new Exception(
            'Payment insert failed: ' .
            $stmt->error
        );

    }


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    */

    setFlashMessage(

        'success',

        'Application submitted successfully! ' .
        'Your Application Number is ' .
        $applicationNo

    );


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    redirect(
        APP_URL .
        '/application-status.php'
    );


} catch (Exception $e) {


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    $conn->rollback();


    /*
    |--------------------------------------------------------------------------
    | Delete uploaded files
    |--------------------------------------------------------------------------
    */

    if (file_exists($photoPath)) {

        unlink($photoPath);

    }


    if (file_exists($signaturePath)) {

        unlink($signaturePath);

    }


    if (file_exists($identityPath)) {

        unlink($identityPath);

    }


    if (
        $quotaPath !== null &&
        file_exists($quotaPath)
    ) {

        unlink($quotaPath);

    }


    /*
    |--------------------------------------------------------------------------
    | SHOW ACTUAL ERROR DURING DEVELOPMENT
    |--------------------------------------------------------------------------
    |
    | This will help us immediately identify any remaining
    | database/schema problem.
    |--------------------------------------------------------------------------
    */

    setFlashMessage(

        'danger',

        'Application submission failed: ' .
        $e->getMessage()

    );


    redirect(
        APP_URL .
        '/apply.php'
    );

}