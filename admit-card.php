<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

global $conn;


/*
|--------------------------------------------------------------------------
| Logged-in User
|--------------------------------------------------------------------------
*/

$userId = getUserId();


/*
|--------------------------------------------------------------------------
| Get Application + Admit Card + Documents
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT

        a.id AS application_id,
        a.application_no,
        a.admission_year,
        a.status AS application_status,

        ap.full_name,
        ap.father_name,
        ap.mother_name,
        ap.identity_number,

        d.name AS department_name,
        d.code AS department_code,

        ac.id AS admit_card_id,
        ac.admit_card_no,
        ac.exam_date,
        ac.exam_shift,
        ac.exam_center,
        ac.seat_number,
        ac.status AS admit_card_status,

        doc.applicant_photo,
        doc.signature

    FROM applicants ap

    INNER JOIN users u
        ON u.id = ap.user_id

    LEFT JOIN applications a
        ON a.applicant_id = ap.id

    LEFT JOIN departments d
        ON d.id = a.department_id

    LEFT JOIN admit_cards ac
        ON ac.application_id = a.id

    LEFT JOIN documents doc
        ON doc.application_id = a.id

    WHERE u.id = ?

    ORDER BY a.id DESC

    LIMIT 1
");

$stmt->bind_param(
    'i',
    $userId
);

$stmt->execute();

$application =
    $stmt->get_result()->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Get Authorization Signature
|--------------------------------------------------------------------------
*/

$authorizationSignature = null;

$stmt = $conn->prepare("
    SELECT authorization_signature

    FROM admit_card_settings

    WHERE id = 1

    LIMIT 1
");

$stmt->execute();

$signatureSetting =
    $stmt->get_result()->fetch_assoc();

$stmt->close();


if ($signatureSetting) {

    $authorizationSignature =
        $signatureSetting[
            'authorization_signature'
        ];

}


/*
|--------------------------------------------------------------------------
| File URLs
|--------------------------------------------------------------------------
*/

$photoUrl = '';

$applicantSignatureUrl = '';

$authorizationSignatureUrl = '';


if ($application) {

    if (!empty($application['applicant_photo'])) {

        $photoUrl =
            APP_URL .
            '/assets/uploads/photos/' .
            rawurlencode(
                basename(
                    $application['applicant_photo']
                )
            );

    }


    if (!empty($application['signature'])) {

        $applicantSignatureUrl =
            APP_URL .
            '/assets/uploads/signatures/' .
            rawurlencode(
                basename(
                    $application['signature']
                )
            );

    }

}


if (!empty($authorizationSignature)) {

    $authorizationSignatureUrl =
        APP_URL .
        '/assets/uploads/signatures/' .
        rawurlencode(
            basename(
                $authorizationSignature
            )
        );

}


/*
|--------------------------------------------------------------------------
| Published?
|--------------------------------------------------------------------------
*/

$isPublished =
    $application &&
    !empty($application['admit_card_id']) &&
    $application['admit_card_status'] === 'published';


$flash = getFlashMessage();

?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admit Card | Smart DUET
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="<?= APP_URL ?>/css/style.css"
    >

    <link
        rel="icon"
        type="image/x-icon"
        href="<?= APP_URL ?>/assets/images/duet-logo.png"
    >


    <style>

        /*
        ============================================================
        PAGE
        ============================================================
        */

        .admit-page {

            padding: 25px 15px 40px;

        }


        /*
        ============================================================
        HERO
        ============================================================
        */

        .admit-hero {

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #084298
                );

            color: #fff;

            border-radius: 16px;

            padding: 25px 30px;

            margin-bottom: 25px;

        }


        .admit-hero h1 {

            font-size: 27px;

            font-weight: 700;

            margin: 0 0 4px;

        }


        .admit-hero p {

            margin: 0;

            opacity: .9;

        }


        /*
        ============================================================
        A4 PAPER
        ============================================================
        */

        .a4-container {

            display: flex;

            justify-content: center;

        }


        .a4-paper {

            width: 210mm;

            min-height: 297mm;

            background: #fff;

            padding: 12mm;

            box-sizing: border-box;

            border: 1px solid #d4d4d4;

            box-shadow:
                0 8px 30px rgba(
                    0,
                    0,
                    0,
                    .12
                );

            position: relative;

        }


        /*
        ============================================================
        INNER BORDER
        ============================================================
        */

        .admit-border {

            min-height: 272mm;

            border: 2px solid #173f78;

            padding: 8mm;

            box-sizing: border-box;

            position: relative;

        }


        /*
        ============================================================
        HEADER
        ============================================================
        */

        .duet-header {

            text-align: center;

            border-bottom: 2px solid #173f78;

            padding-bottom: 5mm;

            margin-bottom: 5mm;

        }


        .duet-logo {

            width: 22mm;

            height: 22mm;

            object-fit: contain;

            margin-bottom: 2mm;

        }


        .university-name {

            font-size: 19px;

            font-weight: 800;

            color: #123c73;

            margin: 0;

        }


        .university-location {

            font-size: 11px;

            color: #555;

            margin-top: 1mm;

        }


        .admission-heading {

            font-size: 15px;

            font-weight: 800;

            margin-top: 3mm;

            color: #123c73;

            letter-spacing: .4px;

        }


        /*
        ============================================================
        APPLICANT INFORMATION
        ============================================================
        */

        .applicant-section {

            display: flex;

            gap: 8mm;

            margin-bottom: 5mm;

        }


        .applicant-details {

            flex: 1;

        }


        .photo-container {

            width: 32mm;

            min-width: 32mm;

            height: 40mm;

            border: 1px solid #555;

            display: flex;

            justify-content: center;

            align-items: center;

            background: #fafafa;

            overflow: hidden;

        }


        .photo-container img {

            width: 100%;

            height: 100%;

            object-fit: cover;

        }


        .photo-placeholder {

            font-size: 10px;

            color: #777;

            text-align: center;

        }


        /*
        ============================================================
        INFORMATION
        ============================================================
        */

        .detail-row {

            display: flex;

            margin-bottom: 2.5mm;

            font-size: 11px;

        }


        .detail-label {

            width: 39mm;

            font-weight: 700;

            color: #333;

        }


        .detail-value {

            flex: 1;

            color: #111;

        }


        /*
        ============================================================
        EXAM INFORMATION
        ============================================================
        */

        .section-heading {

            background: #edf4ff;

            border-left: 4px solid #173f78;

            padding: 2.5mm 3mm;

            font-size: 12px;

            font-weight: 800;

            color: #123c73;

            margin: 4mm 0 3mm;

        }


        .exam-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            border: 1px solid #cfd6df;

        }


        .exam-item {

            padding: 3mm;

            border-right: 1px solid #cfd6df;

            border-bottom: 1px solid #cfd6df;

        }


        .exam-item:nth-child(even) {

            border-right: none;

        }


        .exam-item:nth-last-child(-n+2) {

            border-bottom: none;

        }


        .exam-label {

            display: block;

            font-size: 9px;

            color: #666;

            margin-bottom: 1mm;

        }


        .exam-value {

            display: block;

            font-size: 11px;

            font-weight: 700;

            color: #111;

        }


        /*
        ============================================================
        SEAT NUMBER
        ============================================================
        */

        .seat-container {

            margin: 5mm 0;

            text-align: center;

            border: 2px solid #198754;

            background: #f1fbf5;

            padding: 4mm;

        }


        .seat-label {

            font-size: 9px;

            font-weight: 700;

            color: #555;

        }


        .seat-number {

            font-size: 22px;

            font-weight: 900;

            color: #146c43;

            margin-top: 1mm;

        }


        /*
        ============================================================
        INSTRUCTIONS
        ============================================================
        */

        .instructions {

            margin-top: 4mm;

        }


        .instructions ol {

            margin: 0;

            padding-left: 5mm;

        }


        .instructions li {

            font-size: 9px;

            margin-bottom: 1.5mm;

            line-height: 1.35;

        }


        /*
        ============================================================
        SIGNATURE AREA
        ============================================================
        */

        .signature-area {

            margin-top: 12mm;

            display: flex;

            justify-content: space-between;

            align-items: flex-end;

        }


        .signature-box {

            width: 45mm;

            text-align: center;

        }


        .signature-image {

            height: 16mm;

            display: flex;

            align-items: flex-end;

            justify-content: center;

            margin-bottom: 2mm;

        }


        .signature-image img {

            max-width: 40mm;

            max-height: 14mm;

            object-fit: contain;

        }


        .signature-line {

            border-top: 1px solid #222;

            padding-top: 1.5mm;

            font-size: 8px;

            font-weight: 700;

        }


        /*
        ============================================================
        AUTHORIZED SIGNATURE
        ============================================================
        */

        .authorized-signature {

            width: 48mm;

            text-align: center;

        }


        .authorized-image {

            height: 16mm;

            display: flex;

            justify-content: center;

            align-items: flex-end;

            margin-bottom: 2mm;

        }


        .authorized-image img {

            max-width: 43mm;

            max-height: 14mm;

            object-fit: contain;

        }


        .authorized-line {

            border-top: 1px solid #222;

            padding-top: 1.5mm;

            font-size: 8px;

            font-weight: 700;

        }


        /*
        ============================================================
        FOOTER
        ============================================================
        */

        .paper-footer {

            position: absolute;

            left: 8mm;

            right: 8mm;

            bottom: 6mm;

            border-top: 1px solid #ccc;

            padding-top: 2mm;

            text-align: center;

            font-size: 7.5px;

            color: #666;

        }


        /*
        ============================================================
        PRINT BUTTON
        ============================================================
        */

        .print-area {

            text-align: center;

            margin: 25px 0;

        }


        /*
        ============================================================
        NOT PUBLISHED
        ============================================================
        */

        .not-published {

            max-width: 650px;

            margin: 40px auto;

            background: #fff;

            border-radius: 15px;

            padding: 45px 25px;

            text-align: center;

            box-shadow:
                0 8px 25px rgba(
                    0,
                    0,
                    0,
                    .08
                );

        }


        .not-published-icon {

            width: 75px;

            height: 75px;

            border-radius: 50%;

            background: #fff3cd;

            color: #856404;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 18px;

            font-size: 30px;

        }


        /*
        ============================================================
        PRINT CSS
        ============================================================
        */

        @page {

            size: A4 portrait;

            margin: 0;

        }


        @media print {

            html,
            body {

                width: 210mm;

                height: 297mm;

                margin: 0 !important;

                padding: 0 !important;

                background: #fff !important;

            }


            body * {

                visibility: hidden !important;

            }


            #printAdmitCard,
            #printAdmitCard * {

                visibility: visible !important;

            }


            #printAdmitCard {

                position: absolute;

                left: 0;

                top: 0;

                width: 210mm;

                min-height: 297mm;

                margin: 0;

                padding: 0;

                box-shadow: none;

                border: none;

            }


            .a4-paper {

                width: 210mm;

                height: 297mm;

                min-height: 297mm;

                padding: 10mm;

                border: none;

                box-shadow: none;

            }


            .admit-border {

                min-height: 277mm;

            }


            .print-area {

                display: none !important;

            }


            .admit-hero {

                display: none !important;

            }


            .applicant-top,
            header,
            footer,
            nav {

                visibility: hidden !important;

            }

        }


        /*
        ============================================================
        MOBILE
        ============================================================
        */

        @media (max-width: 768px) {

            .a4-container {

                overflow-x: auto;

                justify-content: flex-start;

            }


            .a4-paper {

                flex-shrink: 0;

                transform-origin: top left;

            }

        }

    </style>

</head>


<body>


<?php include __DIR__ . '/includes/header.php'; ?>


<div class="admit-page">


    <!-- ==========================================================
         HERO
    =========================================================== -->

    <section class="admit-hero">

        <h1>

            <i class="bi bi-card-heading"></i>

            Admit Card

        </h1>


        <p>

            DUET Admission Test Admit Card

        </p>

    </section>



    <?php if ($flash): ?>

        <div class="alert alert-<?= e($flash['type']) ?>">

            <?= e($flash['message']) ?>

        </div>

    <?php endif; ?>



    <?php if (!$application): ?>


        <div class="not-published">

            <div class="not-published-icon">

                <i class="bi bi-file-earmark-x"></i>

            </div>


            <h3>

                No Application Found

            </h3>


            <p class="text-muted">

                You have not submitted an application yet.

            </p>


        </div>


    <?php elseif (!$isPublished): ?>


        <div class="not-published">

            <div class="not-published-icon">

                <i class="bi bi-hourglass-split"></i>

            </div>


            <h3>

                Admit Card Not Published Yet

            </h3>


            <p class="text-muted mb-0">

                Your admit card has not been published yet.
                Please check again later.

            </p>

        </div>


    <?php else: ?>


        <!-- ======================================================
             A4 ADMIT CARD
        ======================================================= -->

        <div class="a4-container">


            <div
                id="printAdmitCard"
                class="a4-paper"
            >


                <div class="admit-border">


                    <!-- ==========================================
                         HEADER
                    =========================================== -->

                    <div class="duet-header">


                        <?php

                        $logoPath =
                            __DIR__ .
                            '/assets/images/duet-logo.png';

                        ?>


                        <?php if (file_exists($logoPath)): ?>

                            <img
                                src="<?= APP_URL ?>/assets/images/duet-logo.png"
                                class="duet-logo"
                                alt="DUET Logo"
                            >

                        <?php endif; ?>


                        <h1 class="university-name">

                            Dhaka University of Engineering & Technology

                        </h1>


                        <div class="university-location">

                            Gazipur, Bangladesh

                        </div>


                        <div class="admission-heading">

                            ADMISSION TEST
                            <?= e(
                                $application['admission_year']
                            ) ?>

                            — ADMIT CARD

                        </div>


                    </div>



                    <!-- ==========================================
                         APPLICANT
                    =========================================== -->

                    <div class="applicant-section">


                        <div class="applicant-details">


                            <div class="detail-row">

                                <div class="detail-label">
                                    Applicant Name
                                </div>

                                <div class="detail-value">

                                    <?= e(
                                        $application['full_name']
                                    ) ?>

                                </div>

                            </div>


                            <div class="detail-row">

                                <div class="detail-label">
                                    Father's Name
                                </div>

                                <div class="detail-value">

                                    <?= e(
                                        $application['father_name']
                                        ?? '-'
                                    ) ?>

                                </div>

                            </div>


                            <div class="detail-row">

                                <div class="detail-label">
                                    Application No
                                </div>

                                <div class="detail-value">

                                    <strong>

                                        <?= e(
                                            $application['application_no']
                                        ) ?>

                                    </strong>

                                </div>

                            </div>


                            <div class="detail-row">

                                <div class="detail-label">
                                    Admit Card No
                                </div>

                                <div class="detail-value">

                                    <strong>

                                        <?= e(
                                            $application['admit_card_no']
                                        ) ?>

                                    </strong>

                                </div>

                            </div>


                            <div class="detail-row">

                                <div class="detail-label">
                                    Department
                                </div>

                                <div class="detail-value">

                                    <?= e(
                                        $application['department_code']
                                    ) ?>

                                    -

                                    <?= e(
                                        $application['department_name']
                                    ) ?>

                                </div>

                            </div>


                            <div class="detail-row">

                                <div class="detail-label">
                                    Identity Number
                                </div>

                                <div class="detail-value">

                                    <?= e(
                                        $application['identity_number']
                                    ) ?>

                                </div>

                            </div>


                        </div>



                        <!-- Applicant Photo -->

                        <div class="photo-container">


                            <?php if ($photoUrl): ?>

                                <img
                                    src="<?= e($photoUrl) ?>"
                                    alt="Applicant Photo"
                                >

                            <?php else: ?>

                                <div class="photo-placeholder">

                                    Applicant<br>
                                    Photo

                                </div>

                            <?php endif; ?>


                        </div>


                    </div>



                    <!-- ==========================================
                         EXAM INFORMATION
                    =========================================== -->

                    <div class="section-heading">

                        <i class="bi bi-calendar-event"></i>

                        Examination Information

                    </div>


                    <div class="exam-grid">


                        <div class="exam-item">

                            <span class="exam-label">
                                Exam Date
                            </span>


                            <span class="exam-value">

                                <?php

                                $examDate =
                                    strtotime(
                                        $application['exam_date']
                                    );

                                echo $examDate
                                    ? e(
                                        date(
                                            'd F Y',
                                            $examDate
                                        )
                                    )
                                    : '-';

                                ?>

                            </span>

                        </div>



                        <div class="exam-item">

                            <span class="exam-label">

                                Shift / Time

                            </span>


                            <span class="exam-value">

                                <?= e(
                                    $application['exam_shift']
                                ) ?>

                            </span>

                        </div>



                        <div class="exam-item">

                            <span class="exam-label">

                                Examination Center

                            </span>


                            <span class="exam-value">

                                <?= e(
                                    $application['exam_center']
                                ) ?>

                            </span>

                        </div>



                        <div class="exam-item">

                            <span class="exam-label">

                                Department

                            </span>


                            <span class="exam-value">

                                <?= e(
                                    $application['department_name']
                                ) ?>

                            </span>

                        </div>


                    </div>



                    <!-- ==========================================
                         SEAT
                    =========================================== -->

                    <div class="seat-container">

                        <div class="seat-label">

                            YOUR SEAT NUMBER

                        </div>


                        <div class="seat-number">

                            <?= e(
                                $application['seat_number']
                            ) ?>

                        </div>

                    </div>



                    <!-- ==========================================
                         INSTRUCTIONS
                    =========================================== -->

                    <div class="section-heading">

                        Important Instructions

                    </div>


                    <div class="instructions">


                        <ol>

                            <li>

                                The applicant must bring this admit card
                                to the examination center.

                            </li>


                            <li>

                                The applicant must arrive at the examination
                                center before the scheduled examination time.

                            </li>


                            <li>

                                Bring the required original identity document
                                as instructed by the admission authority.

                            </li>


                            <li>

                                Electronic devices and unauthorized materials
                                are not allowed in the examination hall.

                            </li>


                            <li>

                                The applicant must follow all instructions
                                given by the examination authority.

                            </li>


                        </ol>

                    </div>



                    <!-- ==========================================
                         SIGNATURES
                    =========================================== -->

                    <div class="signature-area">


                        <!-- Applicant Signature -->

                        <div class="signature-box">


                            <div class="signature-image">


                                <?php if (
                                    $applicantSignatureUrl
                                ): ?>

                                    <img
                                        src="<?= e(
                                            $applicantSignatureUrl
                                        ) ?>"
                                        alt="Applicant Signature"
                                    >

                                <?php endif; ?>


                            </div>


                            <div class="signature-line">

                                Applicant's Signature

                            </div>

                        </div>



                        <!-- Authorized Signature -->

                        <div class="authorized-signature">


                            <div class="authorized-image">


                                <?php if (
                                    $authorizationSignatureUrl
                                ): ?>

                                    <img
                                        src="<?= e(
                                            $authorizationSignatureUrl
                                        ) ?>"
                                        alt="Authorized Signature"
                                    >

                                <?php endif; ?>


                            </div>


                            <div class="authorized-line">

                                Authorized Signature

                            </div>

                        </div>


                    </div>



                    <!-- ==========================================
                         FOOTER
                    =========================================== -->

                    <div class="paper-footer">

                        Smart DUET Admission Management System
                        |
                        DUET Admission Test

                    </div>


                </div>


            </div>


        </div>



        <!-- ======================================================
             PRINT
        ======================================================= -->

        <div class="print-area">


            <button
                type="button"
                onclick="window.print()"
                class="btn btn-primary btn-lg"
            >

                <i class="bi bi-printer"></i>

                Print Admit Card

            </button>


        </div>


    <?php endif; ?>


</div>


<?php include __DIR__ . '/includes/footer.php'; ?>


</body>

</html>