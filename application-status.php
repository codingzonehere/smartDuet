<?php

/*
|--------------------------------------------------------------------------
| APPLICATION STATUS
|--------------------------------------------------------------------------
| Shows the logged-in applicant's latest admission application status.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';


// --------------------------------------------------
// Page Title
// --------------------------------------------------

$pageTitle = 'Application Status';


// --------------------------------------------------
// Current User
// --------------------------------------------------

$userId = getUserId();


// --------------------------------------------------
// Get Applicant ID
// --------------------------------------------------

$applicantId = 0;

$stmt = $conn->prepare("
    SELECT id
    FROM applicants
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param('i', $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $applicantId = (int) $row['id'];
}

$stmt->close();


// --------------------------------------------------
// Get Latest Application
// --------------------------------------------------

$application = null;

if ($applicantId > 0) {

    $stmt = $conn->prepare("
        SELECT
            a.id,
            a.application_no,
            a.admission_year,
            a.ssc_gpa,
            a.diploma_cgpa,
            a.diploma_passing_year,
            a.quota,
            a.status,
            a.submitted_at,
            a.created_at,

            d.id AS department_id,
            d.code AS department_code,
            d.name AS department_name,
            d.faculty,
            d.degree,
            d.duration,
            d.seats,

            t.id AS technology_id,
            t.name AS technology_name

        FROM applications a

        INNER JOIN departments d
            ON d.id = a.department_id

        INNER JOIN diploma_technologies t
            ON t.id = a.technology_id

        WHERE a.applicant_id = ?

        ORDER BY a.id DESC

        LIMIT 1
    ");

    $stmt->bind_param('i', $applicantId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $application = $row;
    }

    $stmt->close();
}


// --------------------------------------------------
// Payment Information
// --------------------------------------------------

$payment = null;

if ($application) {

    $stmt = $conn->prepare("
        SELECT
            payment_method,
            amount,
            transaction_id,
            payment_status,
            paid_at

        FROM payments

        WHERE application_id = ?

        LIMIT 1
    ");

    $stmt->bind_param('i', $application['id']);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $payment = $row;
    }

    $stmt->close();
}


// --------------------------------------------------
// Document Information
// --------------------------------------------------

$documents = null;

if ($application) {

    $stmt = $conn->prepare("
        SELECT
            applicant_photo,
            signature,
            identity_document,
            quota_document

        FROM documents

        WHERE application_id = ?

        LIMIT 1
    ");

    $stmt->bind_param('i', $application['id']);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $documents = $row;
    }

    $stmt->close();
}


// --------------------------------------------------
// Admit Card Information
// --------------------------------------------------

$admitCard = null;

if ($application) {

    $stmt = $conn->prepare("
        SELECT
            admit_card_no,
            exam_date,
            exam_shift,
            exam_center,
            seat_number,
            pdf_file,
            status

        FROM admit_cards

        WHERE application_id = ?

        LIMIT 1
    ");

    $stmt->bind_param('i', $application['id']);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $admitCard = $row;
    }

    $stmt->close();
}


// --------------------------------------------------
// Result Information
// --------------------------------------------------

$admissionResult = null;

if ($application) {

    $stmt = $conn->prepare("
        SELECT
            merit_position,
            result_status,
            published_date,
            remarks

        FROM results

        WHERE application_id = ?

        LIMIT 1
    ");

    $stmt->bind_param('i', $application['id']);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $admissionResult = $row;
    }

    $stmt->close();
}


// --------------------------------------------------
// Application Status Helper
// --------------------------------------------------

function getApplicationStatusText(string $status): string
{
    $statuses = [
        'draft' => 'Draft',
        'payment_pending' => 'Payment Pending',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected'
    ];

    return $statuses[$status] ?? 'Unknown';
}


// --------------------------------------------------
// Application Status Badge
// --------------------------------------------------

function getApplicationStatusBadge(string $status): string
{
    $classes = [
        'draft' => 'status-secondary',
        'payment_pending' => 'status-warning',
        'submitted' => 'status-primary',
        'under_review' => 'status-info',
        'accepted' => 'status-success',
        'rejected' => 'status-danger'
    ];

    return $classes[$status] ?? 'status-secondary';
}


// --------------------------------------------------
// Payment Status Text
// --------------------------------------------------

function getPaymentStatusText(?string $status): string
{
    if (!$status) {
        return 'Not Available';
    }

    $statuses = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed'
    ];

    return $statuses[$status] ?? ucfirst($status);
}


// --------------------------------------------------
// Payment Status Badge
// --------------------------------------------------

function getPaymentStatusBadge(?string $status): string
{
    $classes = [
        'pending' => 'status-warning',
        'paid' => 'status-success',
        'failed' => 'status-danger'
    ];

    return $classes[$status ?? ''] ?? 'status-secondary';
}


// --------------------------------------------------
// Flash Message
// --------------------------------------------------

$flashMessage = getFlashMessage();


// --------------------------------------------------
// Common Header
// --------------------------------------------------

require_once __DIR__ . '/includes/header.php';

?>


<style>

/* =========================================================
   APPLICATION STATUS PAGE
========================================================= */

.status-page {
    max-width: 1250px;
    margin: 0 auto;
}


/* =========================================================
   HERO
========================================================= */

.status-hero {
    position: relative;
    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            #0d6efd 0%,
            #2563eb 48%,
            #4f46e5 100%
        );

    color: #ffffff;

    border-radius: 20px;

    padding: 30px 32px;

    margin-bottom: 26px;

    box-shadow:
        0 12px 30px rgba(37, 99, 235, .20);
}

.status-hero::before {
    content: "";

    position: absolute;

    width: 180px;
    height: 180px;

    border-radius: 50%;

    background: rgba(255,255,255,.08);

    right: 70px;
    top: -85px;
}

.status-hero::after {
    content: "";

    position: absolute;

    width: 120px;
    height: 120px;

    border-radius: 50%;

    background: rgba(255,255,255,.06);

    right: -25px;
    bottom: -50px;
}

.status-hero-content {
    position: relative;
    z-index: 2;
}

.status-hero-icon {
    width: 52px;
    height: 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 15px;

    background: rgba(255,255,255,.16);

    font-size: 25px;

    margin-bottom: 15px;
}

.status-hero h2 {
    font-size: 26px;
    font-weight: 800;

    margin-bottom: 8px;
}

.status-hero p {
    margin: 0;

    font-size: 14px;

    color: rgba(255,255,255,.88);
}


/* =========================================================
   FLASH MESSAGE
========================================================= */

.status-alert {
    border: 0;
    border-radius: 14px;

    box-shadow:
        0 5px 18px rgba(15,23,42,.06);
}


/* =========================================================
   APPLICATION NUMBER CARD
========================================================= */

.application-number-box {
    position: relative;

    background: #ffffff;

    border: 1px solid #e7edf5;

    border-radius: 18px;

    padding: 22px 24px;

    margin-bottom: 22px;

    box-shadow:
        0 6px 22px rgba(15,23,42,.05);

    overflow: hidden;
}

.application-number-box::before {
    content: "";

    position: absolute;

    left: 0;
    top: 0;
    bottom: 0;

    width: 5px;

    background:
        linear-gradient(
            180deg,
            #0d6efd,
            #4f46e5
        );
}

.application-number-label {
    color: #64748b;

    font-size: 12px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .6px;

    margin-bottom: 5px;
}

.application-number {
    font-size: 24px;

    font-weight: 800;

    color: #1d4ed8;

    letter-spacing: .7px;
}


/* =========================================================
   STATUS BADGES
========================================================= */

.status-badge {
    display: inline-flex;

    align-items: center;
    gap: 7px;

    padding: 8px 14px;

    border-radius: 50px;

    font-size: 12px;

    font-weight: 700;
}

.status-badge::before {
    content: "";

    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: currentColor;
}

.status-primary {
    background: #e8f1ff;
    color: #0d6efd;
}

.status-success {
    background: #e8f8ef;
    color: #198754;
}

.status-warning {
    background: #fff5d9;
    color: #9a6700;
}

.status-info {
    background: #e6f8fb;
    color: #087990;
}

.status-danger {
    background: #fdecec;
    color: #dc3545;
}

.status-secondary {
    background: #eef1f5;
    color: #64748b;
}


/* =========================================================
   TOP STAT CARDS
========================================================= */

.status-stat-card {
    position: relative;

    background: #ffffff;

    border: 1px solid #e8edf4;

    border-radius: 18px;

    padding: 22px;

    min-height: 125px;

    overflow: hidden;

    transition:
        transform .25s ease,
        box-shadow .25s ease;
}

.status-stat-card:hover {
    transform: translateY(-4px);

    box-shadow:
        0 12px 28px rgba(15,23,42,.09);
}

.status-stat-card::after {
    content: "";

    position: absolute;

    width: 85px;
    height: 85px;

    border-radius: 50%;

    right: -35px;
    bottom: -35px;

    opacity: .6;
}

.stat-blue {
    background: linear-gradient(135deg,#ffffff,#f3f7ff);
}

.stat-blue::after {
    background: #dbeafe;
}

.stat-green {
    background: linear-gradient(135deg,#ffffff,#f2fbf6);
}

.stat-green::after {
    background: #d1fae5;
}

.stat-purple {
    background: linear-gradient(135deg,#ffffff,#f7f4ff);
}

.stat-purple::after {
    background: #e9d5ff;
}

.stat-top {
    position: relative;
    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: space-between;
}

.stat-label {
    color: #64748b;

    font-size: 12px;

    font-weight: 700;

    margin-bottom: 6px;
}

.stat-value {
    color: #172033;

    font-size: 18px;

    font-weight: 800;
}

.stat-icon {
    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;

    font-size: 21px;
}

.stat-icon-blue {
    background: #e8f1ff;
    color: #0d6efd;
}

.stat-icon-green {
    background: #e8f8ef;
    color: #198754;
}

.stat-icon-purple {
    background: #f0e9ff;
    color: #6f42c1;
}


/* =========================================================
   COMMON CARD
========================================================= */

.status-card {
    background: #ffffff;

    border: 1px solid #e7edf5;

    border-radius: 18px;

    padding: 24px;

    height: 100%;

    box-shadow:
        0 6px 22px rgba(15,23,42,.045);

    transition:
        box-shadow .25s ease,
        transform .25s ease;
}

.status-card:hover {
    box-shadow:
        0 10px 28px rgba(15,23,42,.07);
}

.status-card-title {
    display: flex;
    align-items: center;

    font-size: 16px;

    font-weight: 800;

    color: #172033;

    padding-bottom: 15px;

    margin-bottom: 5px;

    border-bottom: 1px solid #edf1f6;
}

.card-title-icon {
    width: 36px;
    height: 36px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 10px;

    background: #eaf2ff;

    color: #0d6efd;

    margin-right: 10px;

    font-size: 16px;
}


/* =========================================================
   INFORMATION ROW
========================================================= */

.info-row {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    padding: 13px 2px;

    border-bottom: 1px solid #edf1f6;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: #64748b;

    font-size: 12px;

    font-weight: 600;
}

.info-value {
    color: #1e293b;

    font-size: 13px;

    font-weight: 700;

    text-align: right;

    word-break: break-word;
}


/* =========================================================
   PAYMENT CARD SPECIAL
========================================================= */

.payment-card {
    background:
        linear-gradient(
            145deg,
            #ffffff 0%,
            #f7faff 100%
        );
}

.payment-status-box {
    margin-top: 18px;

    padding: 15px;

    border-radius: 13px;

    background: #f8fafc;

    border: 1px solid #e9eef5;
}


/* =========================================================
   TIMELINE
========================================================= */

.status-timeline {
    position: relative;

    padding: 12px 5px 5px 42px;
}

.status-timeline::before {
    content: "";

    position: absolute;

    left: 14px;

    top: 25px;

    bottom: 28px;

    width: 2px;

    background: #e5eaf1;
}

.timeline-item {
    position: relative;

    padding-bottom: 25px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-dot {
    position: absolute;

    left: -34px;

    top: 1px;

    width: 22px;
    height: 22px;

    border-radius: 50%;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #ffffff;

    border: 2px solid #cbd5e1;

    color: #94a3b8;

    font-size: 10px;

    z-index: 2;
}

.timeline-item.completed .timeline-dot {
    background: #e8f8ef;

    border-color: #198754;

    color: #198754;
}

.timeline-title {
    font-size: 13px;

    font-weight: 800;

    color: #64748b;

    margin-bottom: 4px;
}

.timeline-item.completed .timeline-title {
    color: #198754;
}

.timeline-text {
    font-size: 11px;

    line-height: 1.55;

    color: #94a3b8;
}


/* =========================================================
   UPDATE BOX
========================================================= */

.update-box {
    border: 1px solid #e7edf5;

    border-radius: 14px;

    padding: 17px;

    margin-bottom: 13px;

    background: #ffffff;

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}

.update-box:hover {
    transform: translateY(-2px);

    box-shadow:
        0 7px 18px rgba(15,23,42,.06);
}

.update-box:last-child {
    margin-bottom: 0;
}

.update-icon {
    width: 40px;
    height: 40px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 11px;

    background: #eaf2ff;

    color: #0d6efd;

    font-size: 18px;
}

.update-title {
    font-size: 13px;

    font-weight: 800;

    color: #1e293b;
}

.update-description {
    color: #94a3b8;

    font-size: 11px;

    line-height: 1.5;

    margin-top: 3px;
}


/* =========================================================
   BUTTON
========================================================= */

.status-btn {
    border-radius: 10px;

    font-size: 12px;

    font-weight: 700;

    padding: 8px 13px;

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}

.status-btn:hover {
    transform: translateY(-1px);

    box-shadow:
        0 5px 12px rgba(13,110,253,.18);
}


/* =========================================================
   NO APPLICATION
========================================================= */

.no-application {
    background: #ffffff;

    border: 1px solid #e7edf5;

    border-radius: 20px;

    padding: 65px 25px;

    text-align: center;

    box-shadow:
        0 8px 25px rgba(15,23,42,.05);
}

.no-application-icon {
    width: 82px;
    height: 82px;

    display: flex;

    align-items: center;
    justify-content: center;

    margin: 0 auto 20px;

    border-radius: 22px;

    background:
        linear-gradient(
            135deg,
            #e8f1ff,
            #eef2ff
        );

    color: #0d6efd;

    font-size: 34px;
}

.no-application h4 {
    color: #172033;

    font-weight: 800;
}

.no-application p {
    font-size: 13px;

    max-width: 480px;

    margin: 8px auto 0;
}


/* =========================================================
   INFO ALERT
========================================================= */

.status-note {
    border: 0;

    border-radius: 15px;

    padding: 15px 18px;

    font-size: 12px;

    box-shadow:
        0 5px 18px rgba(15,23,42,.04);
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 992px) {

    .status-hero {
        padding: 26px;
    }

    .status-hero h2 {
        font-size: 23px;
    }

}


@media (max-width: 768px) {

    .status-page {
        width: 100%;
    }

    .status-hero {
        border-radius: 16px;

        padding: 23px 20px;
    }

    .status-hero-icon {
        width: 45px;
        height: 45px;

        font-size: 21px;
    }

    .status-hero h2 {
        font-size: 20px;
    }

    .status-hero p {
        font-size: 12px;
    }

    .application-number-box {
        padding: 19px;
    }

    .application-number {
        font-size: 20px;
    }

    .status-card {
        padding: 19px;
    }

    .info-row {
        align-items: flex-start;

        flex-direction: column;

        gap: 4px;
    }

    .info-value {
        text-align: left;
    }

    .status-timeline {
        padding-left: 35px;
    }

}


@media (max-width: 576px) {

    .status-hero {
        margin-bottom: 18px;
    }

    .status-stat-card {
        min-height: 110px;

        padding: 18px;
    }

    .stat-icon {
        width: 42px;
        height: 42px;

        font-size: 18px;
    }

    .stat-value {
        font-size: 16px;
    }

    .status-card-title {
        font-size: 14px;
    }

    .application-number {
        font-size: 18px;
    }

    .no-application {
        padding: 45px 18px;
    }

}


/* =========================================================
   SMOOTH ANIMATION
========================================================= */

.status-page > * {
    animation: statusFade .45s ease both;
}

@keyframes statusFade {

    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }

}

</style>


<div class="content-area">

    <div class="status-page">


        <!-- =================================================
             HERO
        ================================================== -->

        <div class="status-hero">

            <div class="status-hero-content">

                <div class="status-hero-icon">

                    <i class="bi bi-clock-history"></i>

                </div>

                <h2>
                    Application Status
                </h2>

                <p>
                    Track your DUET undergraduate admission
                    application status, payment, admit card
                    and result from one place.
                </p>

            </div>

        </div>


        <!-- =================================================
             FLASH MESSAGE
        ================================================== -->

        <?php if ($flashMessage): ?>

            <div
                class="alert alert-<?= e($flashMessage['type']); ?>
                       alert-dismissible fade show
                       status-alert mb-4"
                role="alert"
            >

                <i class="bi bi-info-circle-fill me-2"></i>

                <?= e($flashMessage['message']); ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <?php if (!$application): ?>


            <!-- =================================================
                 NO APPLICATION
            ================================================== -->

            <div class="no-application">

                <div class="no-application-icon">

                    <i class="bi bi-file-earmark-text"></i>

                </div>

                <h4>
                    No Application Found
                </h4>

                <p class="text-muted">

                    You have not submitted an admission
                    application yet.

                </p>

                <div class="mt-4">

                    <a
                        href="<?= APP_URL ?>/eligibility.php"
                        class="btn btn-primary status-btn me-1"
                    >

                        <i class="bi bi-person-check me-2"></i>

                        Check Eligibility

                    </a>

                    <a
                        href="<?= APP_URL ?>/apply.php"
                        class="btn btn-outline-primary status-btn"
                    >

                        <i class="bi bi-file-earmark-plus me-2"></i>

                        Apply for Admission

                    </a>

                </div>

            </div>


        <?php else: ?>


            <!-- =================================================
                 APPLICATION NUMBER
            ================================================== -->

            <div class="application-number-box">

                <div class="row align-items-center">

                    <div class="col-md-7">

                        <div class="application-number-label">

                            Application Number

                        </div>

                        <div class="application-number">

                            <?= e(
                                $application['application_no']
                            ); ?>

                        </div>

                    </div>

                    <div class="col-md-5 text-md-end mt-3 mt-md-0">

                        <span
                            class="status-badge
                                   <?= e(
                                       getApplicationStatusBadge(
                                           $application['status']
                                       )
                                   ); ?>"
                        >

                            <?= e(
                                getApplicationStatusText(
                                    $application['status']
                                )
                            ); ?>

                        </span>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 TOP STAT CARDS
            ================================================== -->

            <div class="row g-3 mb-4">


                <!-- APPLICATION STATUS -->

                <div class="col-md-4">

                    <div class="status-stat-card stat-blue">

                        <div class="stat-top">

                            <div>

                                <div class="stat-label">
                                    Application Status
                                </div>

                                <div class="stat-value">

                                    <?= e(
                                        getApplicationStatusText(
                                            $application['status']
                                        )
                                    ); ?>

                                </div>

                            </div>

                            <div class="stat-icon stat-icon-blue">

                                <i class="bi bi-file-earmark-check"></i>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PAYMENT STATUS -->

                <div class="col-md-4">

                    <div class="status-stat-card stat-green">

                        <div class="stat-top">

                            <div>

                                <div class="stat-label">
                                    Payment Status
                                </div>

                                <div class="stat-value">

                                    <?= e(
                                        getPaymentStatusText(
                                            $payment['payment_status']
                                                ?? null
                                        )
                                    ); ?>

                                </div>

                            </div>

                            <div class="stat-icon stat-icon-green">

                                <i class="bi bi-credit-card-2-front"></i>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- RESULT STATUS -->

                <div class="col-md-4">

                    <div class="status-stat-card stat-purple">

                        <div class="stat-top">

                            <div>

                                <div class="stat-label">
                                    Admission Result
                                </div>

                                <div class="stat-value">

                                    <?php

                                    if (!$admissionResult) {

                                        echo 'Not Published';

                                    } else {

                                        $resultLabels = [

                                            'pending' =>
                                                'Pending',

                                            'selected' =>
                                                'Selected',

                                            'waiting' =>
                                                'Waiting List',

                                            'not_selected' =>
                                                'Not Selected'

                                        ];

                                        $resultStatusKey =
                                            $admissionResult[
                                                'result_status'
                                            ] ?? '';

                                        echo e(
                                            $resultLabels[
                                                $resultStatusKey
                                            ] ?? 'Published'
                                        );

                                    }

                                    ?>

                                </div>

                            </div>

                            <div class="stat-icon stat-icon-purple">

                                <i class="bi bi-trophy"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 APPLICATION DETAILS + PAYMENT
            ================================================== -->

            <div class="row g-4 mb-4">


                <!-- APPLICATION DETAILS -->

                <div class="col-lg-7">

                    <div class="status-card">

                        <h5 class="status-card-title">

                            <span class="card-title-icon">

                                <i class="bi bi-file-earmark-text"></i>

                            </span>

                            Application Details

                        </h5>


                        <div class="info-row">

                            <span class="info-label">
                                Admission Year
                            </span>

                            <span class="info-value">

                                <?= e(
                                    $application['admission_year']
                                ); ?>

                            </span>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Diploma Technology
                            </span>

                            <span class="info-value">

                                <?= e(
                                    $application['technology_name']
                                ); ?>

                            </span>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Selected Department
                            </span>

                            <span class="info-value">

                                <?= e(
                                    $application['department_name']
                                ); ?>

                                (<?= e(
                                    $application['department_code']
                                ); ?>)

                            </span>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Faculty
                            </span>

                            <span class="info-value">

                                <?= e(
                                    $application['faculty']
                                ); ?>

                            </span>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                SSC GPA
                            </span>

                            <span class="info-value">

                                <?= number_format(
                                    (float)
                                    $application['ssc_gpa'],
                                    2
                                ); ?>

                            </span>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Diploma CGPA
                            </span>

                            <span class="info-value">

                                <?= number_format(
                                    (float)
                                    $application['diploma_cgpa'],
                                    2
                                ); ?>

                            </span>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Passing Year
                            </span>

                            <span class="info-value">

                                <?= e(
                                    $application[
                                        'diploma_passing_year'
                                    ]
                                ); ?>

                            </span>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Quota
                            </span>

                            <span class="info-value">

                                <?= e(
                                    $application['quota']
                                ); ?>

                            </span>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Submitted At
                            </span>

                            <span class="info-value">

                                <?php

                                if (
                                    !empty(
                                        $application['submitted_at']
                                    )
                                ) {

                                    echo e(
                                        date(
                                            'd M Y, h:i A',
                                            strtotime(
                                                $application[
                                                    'submitted_at'
                                                ]
                                            )
                                        )
                                    );

                                } else {

                                    echo 'Not Submitted';

                                }

                                ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- PAYMENT -->

                <div class="col-lg-5">

                    <div class="status-card payment-card">

                        <h5 class="status-card-title">

                            <span class="card-title-icon">

                                <i class="bi bi-credit-card"></i>

                            </span>

                            Payment Information

                        </h5>


                        <?php if ($payment): ?>

                            <div class="info-row">

                                <span class="info-label">
                                    Payment Method
                                </span>

                                <span class="info-value">

                                    <?= e(
                                        $payment[
                                            'payment_method'
                                        ]
                                    ); ?>

                                </span>

                            </div>


                            <div class="info-row">

                                <span class="info-label">
                                    Amount
                                </span>

                                <span class="info-value">

                                    Tk.
                                    <?= number_format(
                                        (float)
                                        $payment['amount'],
                                        2
                                    ); ?>

                                </span>

                            </div>


                            <div class="info-row">

                                <span class="info-label">
                                    Transaction ID
                                </span>

                                <span class="info-value">

                                    <?= e(
                                        $payment[
                                            'transaction_id'
                                        ]
                                    ); ?>

                                </span>

                            </div>


                            <div class="payment-status-box">

                                <div class="d-flex
                                            justify-content-between
                                            align-items-center">

                                    <span class="info-label">
                                        Payment Status
                                    </span>

                                    <span
                                        class="status-badge
                                            <?= e(
                                                getPaymentStatusBadge(
                                                    $payment[
                                                        'payment_status'
                                                    ]
                                                )
                                            ); ?>"
                                    >

                                        <?= e(
                                            getPaymentStatusText(
                                                $payment[
                                                    'payment_status'
                                                ]
                                            )
                                        ); ?>

                                    </span>

                                </div>

                            </div>


                            <?php if (
                                !empty(
                                    $payment['paid_at']
                                )
                            ): ?>

                                <div class="info-row mt-2">

                                    <span class="info-label">
                                        Paid At
                                    </span>

                                    <span class="info-value">

                                        <?= e(
                                            date(
                                                'd M Y, h:i A',
                                                strtotime(
                                                    $payment[
                                                        'paid_at'
                                                    ]
                                                )
                                            )
                                        ); ?>

                                    </span>

                                </div>

                            <?php endif; ?>


                        <?php else: ?>

                            <div class="text-center py-5">

                                <div class="stat-icon
                                            stat-icon-blue
                                            mx-auto mb-3">

                                    <i class="bi bi-credit-card"></i>

                                </div>

                                <div class="fw-bold text-dark">

                                    No Payment Information

                                </div>

                                <div class="small text-muted mt-1">

                                    Payment information is
                                    not available.

                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 APPLICATION PROGRESS + UPDATES
            ================================================== -->

            <div class="row g-4 mb-4">


                <!-- APPLICATION PROGRESS -->

                <div class="col-lg-7">

                    <div class="status-card">

                        <h5 class="status-card-title">

                            <span class="card-title-icon">

                                <i class="bi bi-list-check"></i>

                            </span>

                            Application Progress

                        </h5>


                        <?php

                        /*
                        |--------------------------------------------------------------------------
                        | Build Progress State
                        |--------------------------------------------------------------------------
                        | Logic kept exactly based on actual DB information.
                        |--------------------------------------------------------------------------
                        */

                        $applicationSubmitted =
                            !empty($application['submitted_at'])
                            ||
                            in_array(
                                $application['status'],
                                [
                                    'submitted',
                                    'under_review',
                                    'accepted',
                                    'rejected'
                                ],
                                true
                            );

                        $paymentCompleted =
                            $payment
                            &&
                            strtolower(
                                (string)
                                $payment['payment_status']
                            ) === 'paid';

                        $applicationReviewed =
                            in_array(
                                $application['status'],
                                [
                                    'under_review',
                                    'accepted',
                                    'rejected'
                                ],
                                true
                            );

                        $finalDecision =
                            in_array(
                                $application['status'],
                                [
                                    'accepted',
                                    'rejected'
                                ],
                                true
                            );

                        $resultPublished =
                            $admissionResult !== null;

                        ?>


                        <div class="status-timeline">


                            <!-- APPLICATION CREATED -->

                            <div class="timeline-item completed">

                                <div class="timeline-dot">

                                    <i class="bi bi-check-lg"></i>

                                </div>

                                <div class="timeline-title">
                                    Application Created
                                </div>

                                <div class="timeline-text">
                                    Your application record has been created.
                                </div>

                            </div>


                            <!-- APPLICATION SUBMITTED -->

                            <div
                                class="timeline-item
                                <?= $applicationSubmitted
                                    ? 'completed'
                                    : ''; ?>"
                            >

                                <div class="timeline-dot">

                                    <?php if ($applicationSubmitted): ?>

                                        <i class="bi bi-check-lg"></i>

                                    <?php else: ?>

                                        <i class="bi bi-hourglass-split"></i>

                                    <?php endif; ?>

                                </div>

                                <div class="timeline-title">
                                    Application Submitted
                                </div>

                                <div class="timeline-text">

                                    <?php if ($applicationSubmitted): ?>

                                        Your application has been
                                        submitted successfully.

                                    <?php else: ?>

                                        Your application has not
                                        been submitted yet.

                                    <?php endif; ?>

                                </div>

                            </div>


                            <!-- PAYMENT -->

                            <div
                                class="timeline-item
                                <?= $paymentCompleted
                                    ? 'completed'
                                    : ''; ?>"
                            >

                                <div class="timeline-dot">

                                    <?php if ($paymentCompleted): ?>

                                        <i class="bi bi-check-lg"></i>

                                    <?php else: ?>

                                        <i class="bi bi-credit-card"></i>

                                    <?php endif; ?>

                                </div>

                                <div class="timeline-title">
                                    Payment Completed
                                </div>

                                <div class="timeline-text">

                                    <?php if ($paymentCompleted): ?>

                                        Payment has been recorded
                                        successfully.

                                    <?php elseif ($payment): ?>

                                        Payment is currently
                                        <?= e(
                                            getPaymentStatusText(
                                                $payment[
                                                    'payment_status'
                                                ]
                                            )
                                        ); ?>.

                                    <?php else: ?>

                                        Payment information is
                                        not available.

                                    <?php endif; ?>

                                </div>

                            </div>


                            <!-- REVIEW -->

                            <div
                                class="timeline-item
                                <?= $applicationReviewed
                                    ? 'completed'
                                    : ''; ?>"
                            >

                                <div class="timeline-dot">

                                    <?php if ($applicationReviewed): ?>

                                        <i class="bi bi-check-lg"></i>

                                    <?php else: ?>

                                        <i class="bi bi-search"></i>

                                    <?php endif; ?>

                                </div>

                                <div class="timeline-title">
                                    Application Review
                                </div>

                                <div class="timeline-text">

                                    <?php if (
                                        $application['status']
                                        === 'under_review'
                                    ): ?>

                                        Your application is currently
                                        under review.

                                    <?php elseif (
                                        $application['status']
                                        === 'accepted'
                                    ): ?>

                                        Your application has passed
                                        the review stage.

                                    <?php elseif (
                                        $application['status']
                                        === 'rejected'
                                    ): ?>

                                        Your application was rejected
                                        during review.

                                    <?php else: ?>

                                        Your application is waiting
                                        for review.

                                    <?php endif; ?>

                                </div>

                            </div>


                            <!-- FINAL DECISION -->

                            <div
                                class="timeline-item
                                <?= $finalDecision
                                    ? 'completed'
                                    : ''; ?>"
                            >

                                <div class="timeline-dot">

                                    <?php if ($finalDecision): ?>

                                        <i class="bi bi-check-lg"></i>

                                    <?php else: ?>

                                        <i class="bi bi-flag"></i>

                                    <?php endif; ?>

                                </div>

                                <div class="timeline-title">
                                    Final Decision
                                </div>

                                <div class="timeline-text">

                                    <?php if (
                                        $application['status']
                                        === 'accepted'
                                    ): ?>

                                        Your application has been accepted.

                                    <?php elseif (
                                        $application['status']
                                        === 'rejected'
                                    ): ?>

                                        Your application has been rejected.

                                    <?php else: ?>

                                        Final admission decision is pending.

                                    <?php endif; ?>

                                </div>

                            </div>


                            <!-- ADMISSION RESULT -->

                            <div
                                class="timeline-item
                                <?= $resultPublished
                                    ? 'completed'
                                    : ''; ?>"
                            >

                                <div class="timeline-dot">

                                    <?php if ($resultPublished): ?>

                                        <i class="bi bi-check-lg"></i>

                                    <?php else: ?>

                                        <i class="bi bi-trophy"></i>

                                    <?php endif; ?>

                                </div>

                                <div class="timeline-title">
                                    Admission Result
                                </div>

                                <div class="timeline-text">

                                    <?php if ($resultPublished): ?>

                                        Admission result is available.

                                    <?php else: ?>

                                        Admission result has not
                                        been published yet.

                                    <?php endif; ?>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- ADMISSION UPDATES -->

                <div class="col-lg-5">

                    <div class="status-card">

                        <h5 class="status-card-title">

                            <span class="card-title-icon">

                                <i class="bi bi-card-checklist"></i>

                            </span>

                            Admission Updates

                        </h5>


                        <!-- ADMIT CARD -->

                        <?php

                        $admitCardAvailable =
                            $admitCard
                            &&
                            !empty($admitCard['pdf_file'])
                            &&
                            strtolower(
                                (string)
                                ($admitCard['status']
                                    ?? 'published')
                            ) !== 'disabled';

                        ?>


                        <?php if ($admitCardAvailable): ?>

                            <div class="update-box">

                                <div class="d-flex gap-3">

                                    <div class="update-icon">

                                        <i class="bi bi-person-vcard"></i>

                                    </div>

                                    <div class="flex-grow-1">

                                        <div class="update-title">

                                            Admit Card Available

                                        </div>

                                        <div class="update-description">

                                            Admit Card No:
                                            <strong>
                                                <?= e(
                                                    $admitCard[
                                                        'admit_card_no'
                                                    ]
                                                ); ?>
                                            </strong>

                                        </div>

                                        <a
                                            href="<?= APP_URL ?>/admit-card.php"
                                            class="btn btn-primary
                                                   status-btn mt-3"
                                        >

                                            <i class="bi bi-eye me-1"></i>

                                            View Admit Card

                                        </a>

                                    </div>

                                </div>

                            </div>


                        <?php else: ?>

                            <div class="update-box">

                                <div class="d-flex gap-3">

                                    <div class="update-icon"
                                         style="
                                         background:#f1f3f5;
                                         color:#64748b;
                                         ">

                                        <i class="bi bi-person-vcard"></i>

                                    </div>

                                    <div>

                                        <div class="update-title">

                                            Admit Card

                                        </div>

                                        <div class="update-description">

                                            Admit card has not been
                                            published yet.

                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>


                        <!-- RESULT -->

                        <?php if ($admissionResult): ?>

                            <div class="update-box">

                                <div class="d-flex gap-3">

                                    <div class="update-icon"
                                         style="
                                         background:#f0e9ff;
                                         color:#6f42c1;
                                         ">

                                        <i class="bi bi-trophy"></i>

                                    </div>

                                    <div class="flex-grow-1">

                                        <div class="update-title">

                                            Result Available

                                        </div>


                                        <?php if (
                                            $admissionResult[
                                                'merit_position'
                                            ] !== null
                                        ): ?>

                                            <div class="update-description">

                                                Merit Position:
                                                <strong>

                                                    <?= (int)
                                                        $admissionResult[
                                                            'merit_position'
                                                        ]; ?>

                                                </strong>

                                            </div>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty(
                                                $admissionResult[
                                                    'remarks'
                                                ]
                                            )
                                        ): ?>

                                            <div class="update-description">

                                                <?= e(
                                                    $admissionResult[
                                                        'remarks'
                                                    ]
                                                ); ?>

                                            </div>

                                        <?php endif; ?>


                                        <a
                                            href="<?= APP_URL ?>/result.php"
                                            class="btn btn-outline-primary
                                                   status-btn mt-3"
                                        >

                                            <i class="bi bi-eye me-1"></i>

                                            View Result

                                        </a>

                                    </div>

                                </div>

                            </div>


                        <?php else: ?>

                            <div class="update-box">

                                <div class="d-flex gap-3">

                                    <div class="update-icon"
                                         style="
                                         background:#f1f3f5;
                                         color:#64748b;
                                         ">

                                        <i class="bi bi-trophy"></i>

                                    </div>

                                    <div>

                                        <div class="update-title">

                                            Admission Result

                                        </div>

                                        <div class="update-description">

                                            Result has not been
                                            published yet.

                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>


                    </div>

                </div>

            </div>


            <!-- =================================================
                 IMPORTANT NOTE
            ================================================== -->

            <div class="alert alert-info status-note mb-0">

                <i class="bi bi-info-circle-fill me-2"></i>

                Please keep your application number and
                transaction ID safe for future reference.

            </div>


        <?php endif; ?>


    </div>

</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>