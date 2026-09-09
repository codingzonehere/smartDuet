<?php

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
| Applicant Dashboard
|--------------------------------------------------------------------------
*/


// --------------------------------------------------
// Authentication + Common Functions
// --------------------------------------------------

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';


// --------------------------------------------------
// Page Title
// --------------------------------------------------

$pageTitle = 'Dashboard';


// --------------------------------------------------
// Get Logged-in User ID
// --------------------------------------------------

$userId = getUserId();


// ==================================================
// GET APPLICANT INFORMATION
// ==================================================

$applicant = null;

$sql = "
    SELECT
        a.id,
        a.full_name,
        a.father_name,
        a.mother_name,
        a.date_of_birth,
        a.gender,
        a.identity_number,
        u.email,
        u.mobile
    FROM applicants a
    INNER JOIN users u
        ON a.user_id = u.id
    WHERE a.user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $applicant = $result->fetch_assoc();

}

$stmt->close();


// ==================================================
// GET LATEST APPLICATION
// ==================================================

$application = null;

if ($applicant) {

    $sql = "
        SELECT
            ap.id,
            ap.application_no,
            ap.admission_year,
            ap.ssc_gpa,
            ap.diploma_cgpa,
            ap.diploma_passing_year,
            ap.quota,
            ap.status,
            ap.submitted_at,

            d.code AS department_code,
            d.name AS department_name,

            dt.name AS technology_name

        FROM applications ap

        INNER JOIN departments d
            ON ap.department_id = d.id

        INNER JOIN diploma_technologies dt
            ON ap.technology_id = dt.id

        WHERE ap.applicant_id = ?

        ORDER BY ap.id DESC

        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $applicant['id']
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $application = $result->fetch_assoc();

    }

    $stmt->close();

}


// ==================================================
// DEFAULT DASHBOARD VALUES
// ==================================================

$applicationStatus = 'Not Started';

$applicationBadge = 'No Application';

$applicationBadgeClass = 'bg-secondary';

$applicationId = '—';


$eligibilityStatus = 'Not Checked';

$eligibilityBadge = 'Check Now';

$eligibilityBadgeClass = 'bg-secondary';


$resultStatus = 'Not Published';


// ==================================================
// APPLICATION STATUS
// ==================================================

if ($application) {

    $applicationId =
        $application['application_no'];


    switch ($application['status']) {

        case 'draft':

            $applicationStatus = 'Draft';

            $applicationBadge = 'Incomplete';

            $applicationBadgeClass =
                'bg-warning text-dark';

            break;


        case 'payment_pending':

            $applicationStatus =
                'Payment Pending';

            $applicationBadge =
                'Payment Required';

            $applicationBadgeClass =
                'bg-warning text-dark';

            break;


        case 'submitted':

            $applicationStatus =
                'Submitted';

            $applicationBadge =
                'Submitted';

            $applicationBadgeClass =
                'bg-primary';

            break;


        case 'under_review':

            $applicationStatus =
                'Under Review';

            $applicationBadge =
                'Under Review';

            $applicationBadgeClass =
                'bg-info text-dark';

            break;


        case 'accepted':

            $applicationStatus =
                'Accepted';

            $applicationBadge =
                'Accepted';

            $applicationBadgeClass =
                'bg-success';

            break;


        case 'rejected':

            $applicationStatus =
                'Rejected';

            $applicationBadge =
                'Rejected';

            $applicationBadgeClass =
                'bg-danger';

            break;


        default:

            $applicationStatus =
                'Not Started';

            $applicationBadge =
                'No Application';

            $applicationBadgeClass =
                'bg-secondary';

            break;

    }


    // ==================================================
    // BASIC ELIGIBILITY DISPLAY
    // ==================================================

    $currentYear =
        (int) date('Y');

    $minimumPassingYear =
        $currentYear - 1;


    if (
        (float) $application['ssc_gpa'] >= 3.00 &&
        (float) $application['diploma_cgpa'] >= 3.00 &&
        (int) $application['diploma_passing_year']
            >= $minimumPassingYear
    ) {

        $eligibilityStatus =
            'Eligible';

        $eligibilityBadge =
            'Eligible';

        $eligibilityBadgeClass =
            'bg-success';

    } else {

        $eligibilityStatus =
            'Not Eligible';

        $eligibilityBadge =
            'Review';

        $eligibilityBadgeClass =
            'bg-danger';

    }

}


// ==================================================
// GET ADMISSION RESULT
// ==================================================

if ($application) {

    $sql = "
        SELECT
            result_status
        FROM results
        WHERE application_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $application['id']
    );

    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows === 1) {

        $resultData =
            $result->fetch_assoc();


        switch (
            $resultData['result_status']
        ) {

            case 'selected':

                $resultStatus =
                    'Selected';

                break;


            case 'waiting':

                $resultStatus =
                    'Waiting List';

                break;


            case 'not_selected':

                $resultStatus =
                    'Not Selected';

                break;


            default:

                $resultStatus =
                    'Pending';

                break;

        }

    }

    $stmt->close();

}


// ==================================================
// ADMISSION SESSION
// ==================================================

$admissionYear =
    (int) date('Y');

$admissionSession =
    ($admissionYear - 1)
    . '-'
    . $admissionYear;


// ==================================================
// EXAM INFORMATION
// ==================================================
//
// Based on the 2026 DUET admission test pattern
// shown in the supplied admission information.
//
// Total = 300
// MCQ   = 120
// Written = 180
// ==================================================

$examYear = $admissionYear;

$examSession =
    ($examYear - 1)
    . '-'
    . $examYear;


// --------------------------------------------------
// Paper 1
// --------------------------------------------------

$paperOneTotal = 150;

$paperOneMcq = 60;

$paperOneWritten = 90;


// --------------------------------------------------
// Paper 2
// --------------------------------------------------

$paperTwoTotal = 150;

$paperTwoMcq = 60;

$paperTwoWritten = 90;


// --------------------------------------------------
// Overall
// --------------------------------------------------

$totalExamMarks = 300;

$totalMcqMarks = 120;

$totalWrittenMarks = 180;


// --------------------------------------------------
// Passing Criteria
// --------------------------------------------------

$overallPassMarks = 120;

$paperPassMarks = 52.5;

$englishMcqPassMarks = 3;


// ==================================================
// COMMON HEADER
// ==================================================

require_once __DIR__ . '/includes/header.php';

?>


<style>
/* =========================================================
   SMART DUET APPLICANT DASHBOARD
   MATERIAL + COLORFUL UI
========================================================= */


/* =========================================================
   GLOBAL DASHBOARD FONT
========================================================= */

.content-area {

    font-family:
        "Inter",
        "Segoe UI",
        "Noto Sans",
        Arial,
        sans-serif;

    color: #212529;

}


/* =========================================================
   SECTION SPACING
========================================================= */

.dashboard-section {

    margin-bottom: 24px;

}


/* =========================================================
   MAIN MATERIAL CARD
========================================================= */

.material-card {

    position: relative;

    overflow: hidden;

    border: 1px solid rgba(0, 0, 0, 0.04);

    border-radius: 20px;

    padding: 24px;

    box-shadow:
        0 5px 18px rgba(0, 0, 0, 0.055);

    transition:
        transform .25s ease,
        box-shadow .25s ease;

}


.material-card:hover {

    transform: translateY(-3px);

    box-shadow:
        0 12px 30px rgba(0, 0, 0, 0.09);

}


/* =========================================================
   TOP COLOR STRIPE
========================================================= */

.material-card::before {

    content: "";

    position: absolute;

    top: 0;

    left: 0;

    width: 100%;

    height: 5px;

}


/* Exam Overview */

.exam-overview::before {

    background: #0d6efd;

}


/* Examination Time */

.material-card:has(.bi-clock)::before {

    background: #fd7e14;

}


/* Passing Criteria */

.material-card:has(.bi-check2-circle)::before {

    background: #198754;

}


/* Admission Information */

.material-card:has(.bi-credit-card)::before {

    background: #0dcaf0;

}


/* Your Application */

.material-card:has(.bi-file-earmark-text)::before {

    background: #6f42c1;

}


/* =========================================================
   SECTION TITLE
========================================================= */

.dashboard-section-title {

    font-family:
        "Inter",
        "Segoe UI",
        sans-serif;

    font-size: 19px;

    font-weight: 750;

    letter-spacing: -0.2px;

    color: #1f2937;

    margin-bottom: 4px;

}


.dashboard-section-subtitle {

    font-size: 13px;

    line-height: 1.55;

    color: #6b7280;

}


/* =========================================================
   MATERIAL ICON
========================================================= */

.material-icon {

    width: 48px;

    height: 48px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

    flex-shrink: 0;

}


/* =========================================================
   STATUS CARDS
========================================================= */

.stat-card {

    position: relative;

    overflow: hidden;

    min-height: 155px;

    border-radius: 18px;

    padding: 21px;

    border: 1px solid rgba(0, 0, 0, .04);

    box-shadow:
        0 5px 16px rgba(0, 0, 0, .055);

    transition:
        transform .25s ease,
        box-shadow .25s ease;

}


.stat-card:hover {

    transform: translateY(-5px);

    box-shadow:
        0 13px 28px rgba(0, 0, 0, .10);

}


/* Decorative circle */

.stat-card::after {

    content: "";

    position: absolute;

    width: 110px;

    height: 110px;

    right: -45px;

    bottom: -55px;

    border-radius: 50%;

    background: rgba(255,255,255,.30);

}


/* =========================================================
   APPLICATION STATUS
========================================================= */

.row.g-4.mb-4
> .col-sm-6:nth-child(1)
.stat-card {

    background:
        linear-gradient(
            145deg,
            #eaf3ff 0%,
            #dbeaff 100%
        );

    border-left: 5px solid #0d6efd;

}


.row.g-4.mb-4
> .col-sm-6:nth-child(1)
.stat-icon {

    background: #ffffff;

    color: #0d6efd;

    box-shadow:
        0 4px 10px rgba(13,110,253,.12);

}


/* =========================================================
   ELIGIBILITY
========================================================= */

.row.g-4.mb-4
> .col-sm-6:nth-child(2)
.stat-card {

    background:
        linear-gradient(
            145deg,
            #eaf8f0 0%,
            #d9f2e4 100%
        );

    border-left: 5px solid #198754;

}


.row.g-4.mb-4
> .col-sm-6:nth-child(2)
.stat-icon {

    background: #ffffff;

    color: #198754;

    box-shadow:
        0 4px 10px rgba(25,135,84,.12);

}


/* =========================================================
   APPLICATION ID
========================================================= */

.row.g-4.mb-4
> .col-sm-6:nth-child(3)
.stat-card {

    background:
        linear-gradient(
            145deg,
            #f4edff 0%,
            #e9ddff 100%
        );

    border-left: 5px solid #6f42c1;

}


.row.g-4.mb-4
> .col-sm-6:nth-child(3)
.stat-icon {

    background: #ffffff;

    color: #6f42c1;

    box-shadow:
        0 4px 10px rgba(111,66,193,.12);

}


/* =========================================================
   RESULT
========================================================= */

.row.g-4.mb-4
> .col-sm-6:nth-child(4)
.stat-card {

    background:
        linear-gradient(
            145deg,
            #fff4e8 0%,
            #ffe5cc 100%
        );

    border-left: 5px solid #fd7e14;

}


.row.g-4.mb-4
> .col-sm-6:nth-child(4)
.stat-icon {

    background: #ffffff;

    color: #fd7e14;

    box-shadow:
        0 4px 10px rgba(253,126,20,.12);

}


/* =========================================================
   STAT TEXT
========================================================= */

.stat-label {

    font-size: 12px;

    font-weight: 600;

    color: #64748b;

    text-transform: uppercase;

    letter-spacing: .35px;

}


.stat-number {

    font-size: 20px;

    font-weight: 800;

    color: #1e293b;

    margin-top: 5px;

    line-height: 1.25;

}


/* =========================================================
   EXAM OVERVIEW
========================================================= */

.exam-overview {

    background:
        linear-gradient(
            145deg,
            #eef6ff 0%,
            #e1efff 55%,
            #f5faff 100%
        );

}


/* =========================================================
   EXAM OVERVIEW MINI CARDS
========================================================= */

.exam-overview .paper-card {

    border: 0;

    border-radius: 16px;

    padding: 19px;

    background: rgba(255,255,255,.88);

    box-shadow:
        0 5px 15px rgba(13,110,253,.07);

}


.exam-overview .paper-card:hover {

    transform: translateY(-4px);

}


/* Total */

.exam-overview .paper-card:nth-child(1) {

    border-bottom: 4px solid #0d6efd;

}


/* MCQ */

.exam-overview .paper-card:nth-child(2) {

    border-bottom: 4px solid #198754;

}


/* Written */

.exam-overview .paper-card:nth-child(3) {

    border-bottom: 4px solid #6f42c1;

}


/* Papers */

.exam-overview .paper-card:nth-child(4) {

    border-bottom: 4px solid #fd7e14;

}


/* =========================================================
   EXAM ICON COLORS
========================================================= */

.exam-overview
.paper-card:nth-child(1)
.material-icon {

    background: #eaf3ff;

    color: #0d6efd;

}


.exam-overview
.paper-card:nth-child(2)
.material-icon {

    background: #eaf8f0;

    color: #198754;

}


.exam-overview
.paper-card:nth-child(3)
.material-icon {

    background: #f4edff;

    color: #6f42c1;

}


.exam-overview
.paper-card:nth-child(4)
.material-icon {

    background: #fff4e8;

    color: #fd7e14;

}


/* =========================================================
   EXAM NUMBER
========================================================= */

.exam-number {

    font-size: 29px;

    font-weight: 850;

    letter-spacing: -0.5px;

    color: #1e3a8a;

    line-height: 1;

}


.exam-label {

    font-size: 12px;

    font-weight: 500;

    color: #64748b;

    margin-top: 6px;

}


/* =========================================================
   PAPER CARDS
========================================================= */

.paper-card {

    position: relative;

    overflow: hidden;

    border-radius: 18px;

    padding: 21px;

    height: 100%;

    transition:
        transform .25s ease,
        box-shadow .25s ease;

}


.paper-card:hover {

    transform: translateY(-4px);

    box-shadow:
        0 12px 25px rgba(0,0,0,.08);

}


/* =========================================================
   FIRST PAPER
========================================================= */

.dashboard-section
.row.g-4
> .col-lg-6:first-child
.paper-card {

    background:
        linear-gradient(
            145deg,
            #f8f3ff,
            #eee3ff
        );

    border: 1px solid #e1d2ff;

    border-top: 5px solid #6f42c1;

}


/* =========================================================
   SECOND PAPER
========================================================= */

.dashboard-section
.row.g-4
> .col-lg-6:last-child
.paper-card {

    background:
        linear-gradient(
            145deg,
            #f0fbf5,
            #dcf5e6
        );

    border: 1px solid #cdebd9;

    border-top: 5px solid #198754;

}


/* =========================================================
   PAPER NUMBER
========================================================= */

.paper-number {

    width: 40px;

    height: 40px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #ffffff;

    color: #6f42c1;

    font-size: 16px;

    font-weight: 800;

    box-shadow:
        0 4px 10px rgba(0,0,0,.06);

}


/* Second paper number */

.dashboard-section
.row.g-4
> .col-lg-6:last-child
.paper-number {

    color: #198754;

}


/* =========================================================
   PAPER TITLE
========================================================= */

.paper-title {

    font-size: 16px;

    font-weight: 750;

    color: #1f2937;

}


.paper-description {

    font-size: 12px;

    color: #64748b;

    margin-top: 2px;

}


/* =========================================================
   SUBJECT ROW
========================================================= */

.subject-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    padding: 11px 0;

    border-bottom: 1px solid rgba(0,0,0,.07);

}


.subject-row:last-child {

    border-bottom: 0;

}


.subject-name {

    font-size: 13px;

    font-weight: 600;

    color: #374151;

}


/* =========================================================
   MARK CHIP
========================================================= */

.mark-chip {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    background: #ffffff;

    color: #5b21b6;

    border-radius: 9px;

    padding: 6px 10px;

    font-size: 11px;

    font-weight: 700;

    box-shadow:
        0 2px 7px rgba(0,0,0,.05);

}


/* =========================================================
   EXAMINATION TIME
========================================================= */

.material-card:has(.bi-clock) {

    background:
        linear-gradient(
            145deg,
            #fff7ed,
            #ffecd9
        );

    border-color: #ffe0bf;

}


/* =========================================================
   TIME CRITERIA CARDS
========================================================= */

.material-card:has(.bi-clock)
.criteria-card {

    background: rgba(255,255,255,.78);

    border: 1px solid #ffdcb8;

    box-shadow:
        0 4px 12px rgba(253,126,20,.06);

}


.material-card:has(.bi-clock)
.criteria-card:first-child {

    border-left: 4px solid #fd7e14;

}


.material-card:has(.bi-clock)
.criteria-card:last-child {

    border-left: 4px solid #e8590c;

}


/* =========================================================
   PASSING CRITERIA
========================================================= */

.material-card:has(.bi-check2-circle) {

    background:
        linear-gradient(
            145deg,
            #f0fbf5,
            #e0f6e8
        );

    border-color: #d1eddb;

}


/* =========================================================
   PASSING CRITERIA CARDS
========================================================= */

.material-card:has(.bi-check2-circle)
.criteria-card {

    background: rgba(255,255,255,.82);

}


.material-card:has(.bi-check2-circle)
.criteria-card:nth-child(1) {

    border-left: 4px solid #198754;

}


.material-card:has(.bi-check2-circle)
.criteria-card:nth-child(2) {

    border-left: 4px solid #0d6efd;

}


.material-card:has(.bi-check2-circle)
.criteria-card:nth-child(3) {

    border-left: 4px solid #6f42c1;

}


/* =========================================================
   CRITERIA CARD
========================================================= */

.criteria-card {

    border-radius: 16px;

    padding: 20px;

    height: 100%;

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}


.criteria-card:hover {

    transform: translateY(-3px);

    box-shadow:
        0 9px 20px rgba(0,0,0,.07);

}


.criteria-value {

    font-size: 26px;

    font-weight: 850;

    letter-spacing: -.5px;

    color: #198754;

}


.criteria-label {

    font-size: 12px;

    color: #64748b;

    line-height: 1.6;

}


.criteria-label strong {

    color: #334155;

}


/* =========================================================
   IMPORTANT INFO BOX
========================================================= */

.material-info {

    display: flex;

    gap: 14px;

    align-items: flex-start;

    background:
        linear-gradient(
            145deg,
            #edf7ff,
            #e4f1ff
        );

    border: 1px solid #cfe5ff;

    border-radius: 15px;

    padding: 17px;

}


.material-info-icon {

    width: 43px;

    height: 43px;

    min-width: 43px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #ffffff;

    color: #0d6efd;

    box-shadow:
        0 3px 8px rgba(13,110,253,.08);

}


/* =========================================================
   ADMISSION INFORMATION
========================================================= */

.material-card:has(.bi-credit-card) {

    background:
        linear-gradient(
            145deg,
            #effcff,
            #e0f8fc
        );

    border-color: #cbeef3;

}


/* =========================================================
   NOTICE ITEMS
========================================================= */

.dashboard-notice {

    padding: 17px 0;

    border-bottom: 1px solid rgba(0,0,0,.06);

}


.dashboard-notice:last-child {

    border-bottom: 0;

    padding-bottom: 0;

}


/* =========================================================
   NOTICE ICONS
========================================================= */

.notice-icon {

    width: 45px;

    height: 45px;

    min-width: 45px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

    background: #ffffff;

    color: #0d6efd;

    box-shadow:
        0 3px 10px rgba(0,0,0,.06);

}


/* Application fee */

.dashboard-notice:nth-child(2)
.notice-icon {

    color: #0d6efd;

}


/* Eligibility */

.dashboard-notice:nth-child(3)
.notice-icon {

    color: #198754;

}


/* Documents */

.dashboard-notice:nth-child(4)
.notice-icon {

    color: #6f42c1;

}


/* =========================================================
   YOUR APPLICATION
========================================================= */

.material-card:has(.bi-file-earmark-text) {

    background:
        linear-gradient(
            145deg,
            #f7f2ff,
            #eee5ff
        );

    border-color: #e1d5f7;

}


/* =========================================================
   APPLICATION INFORMATION BOXES
========================================================= */

.material-card .bg-light {

    background: rgba(255,255,255,.72) !important;

    border: 1px solid rgba(111,66,193,.10);

    border-radius: 13px !important;

    transition:
        background .2s ease,
        transform .2s ease;

}


.material-card .bg-light:hover {

    background: #ffffff !important;

    transform: translateY(-2px);

}


/* =========================================================
   BUTTONS
========================================================= */

.btn {

    border-radius: 10px;

    font-weight: 600;

    font-size: 13px;

    padding: 8px 14px;

}


.btn-primary {

    box-shadow:
        0 4px 10px rgba(13,110,253,.16);

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}


.btn-primary:hover {

    transform: translateY(-1px);

    box-shadow:
        0 7px 15px rgba(13,110,253,.22);

}


.btn-outline-primary {

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}


.btn-outline-primary:hover {

    transform: translateY(-1px);

}


/* =========================================================
   BADGES
========================================================= */

.badge {

    border-radius: 8px;

    font-size: 11px;

    font-weight: 650;

    padding: 6px 9px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 992px) {

    .material-card {

        padding: 21px;

    }


    .stat-card {

        min-height: 145px;

    }

}


@media (max-width: 768px) {

    .material-card {

        padding: 18px;

        border-radius: 16px;

    }


    .stat-card {

        padding: 17px;

        min-height: 140px;

        border-radius: 15px;

    }


    .dashboard-section-title {

        font-size: 17px;

    }


    .dashboard-section-subtitle {

        font-size: 12px;

    }


    .exam-number {

        font-size: 24px;

    }


    .criteria-value {

        font-size: 23px;

    }


    .paper-card {

        padding: 17px;

    }


    .subject-row {

        align-items: flex-start;

    }


    .mark-chip {

        font-size: 10px;

        padding: 5px 7px;

    }

}


@media (max-width: 480px) {

    .material-card {

        padding: 16px;

    }


    .stat-card {

        padding: 15px;

    }


    .stat-number {

        font-size: 17px;

    }


    .material-icon {

        width: 43px;

        height: 43px;

        font-size: 18px;

    }


    .notice-icon {

        width: 40px;

        height: 40px;

        min-width: 40px;

    }

}
</style>


<!-- =====================================================
     DASHBOARD CONTENT
====================================================== -->

<div class="content-area">


    <!-- =================================================
         WELCOME HERO
         Existing Hero Style Preserved
    ================================================== -->

    <div class="welcome-box mb-4">

        <div>

            <h3 class="fw-bold mb-2">

                Welcome to Smart DUET!

            </h3>

            <p class="mb-0">

                Manage your DUET undergraduate admission
                application from one place.

            </p>

        </div>

    </div>



    <!-- =================================================
         STATUS CARDS
    ================================================== -->

    <div class="row g-4 mb-4">


        <!-- APPLICATION STATUS -->

        <div class="col-sm-6 col-lg-3">

            <div class="stat-card h-100">

                <div
                    class="d-flex
                           justify-content-between"
                >

                    <div>

                        <div class="stat-label">

                            Application Status

                        </div>

                        <div class="stat-number">

                            <?= e($applicationStatus); ?>

                        </div>

                    </div>


                    <div class="stat-icon">

                        <i class="bi bi-file-earmark-text"></i>

                    </div>

                </div>


                <span
                    class="badge
                           <?= e($applicationBadgeClass); ?>
                           mt-3"
                >

                    <?= e($applicationBadge); ?>

                </span>

            </div>

        </div>



        <!-- ELIGIBILITY -->

        <div class="col-sm-6 col-lg-3">

            <div class="stat-card h-100">

                <div
                    class="d-flex
                           justify-content-between"
                >

                    <div>

                        <div class="stat-label">

                            Eligibility

                        </div>

                        <div class="stat-number">

                            <?= e($eligibilityStatus); ?>

                        </div>

                    </div>


                    <div class="stat-icon">

                        <i class="bi bi-shield-check"></i>

                    </div>

                </div>


                <a
                    href="<?= APP_URL; ?>/eligibility.php"
                    class="badge
                           <?= e($eligibilityBadgeClass); ?>
                           mt-3
                           text-decoration-none"
                >

                    <?= e($eligibilityBadge); ?>

                </a>

            </div>

        </div>



        <!-- APPLICATION ID -->

        <div class="col-sm-6 col-lg-3">

            <div class="stat-card h-100">

                <div
                    class="d-flex
                           justify-content-between"
                >

                    <div>

                        <div class="stat-label">

                            Application ID

                        </div>

                        <div class="stat-number">

                            <?= e($applicationId); ?>

                        </div>

                    </div>


                    <div class="stat-icon">

                        <i class="bi bi-person"></i>

                    </div>

                </div>


                <span class="badge bg-primary mt-3">

                    Session <?= e($admissionSession); ?>

                </span>

            </div>

        </div>



        <!-- RESULT -->

        <div class="col-sm-6 col-lg-3">

            <div class="stat-card h-100">

                <div
                    class="d-flex
                           justify-content-between"
                >

                    <div>

                        <div class="stat-label">

                            Admission Result

                        </div>

                        <div class="stat-number">

                            <?= e($resultStatus); ?>

                        </div>

                    </div>


                    <div class="stat-icon">

                        <i class="bi bi-trophy"></i>

                    </div>

                </div>


                <a
                    href="<?= APP_URL; ?>/result.php"
                    class="btn
                           btn-sm
                           btn-outline-primary
                           mt-3"
                >

                    View Result

                </a>

            </div>

        </div>


    </div>



    <!-- =================================================
         ADMISSION EXAM OVERVIEW
    ================================================== -->

    <div class="dashboard-section">


        <div class="material-card exam-overview">


            <!-- Header -->

            <div
                class="d-flex
                       justify-content-between
                       align-items-start
                       flex-wrap
                       gap-3
                       mb-4"
            >

                <div>

                    <h5 class="dashboard-section-title">

                        DUET Admission Test <?= e($examYear); ?>

                    </h5>

                    <p class="dashboard-section-subtitle">

                        Academic Session <?= e($examSession); ?>

                    </p>

                </div>


                <span class="badge bg-primary px-3 py-2">

                    300 Marks

                </span>

            </div>



            <!-- Overview -->

            <div class="row g-3">


                <!-- Total -->

                <div class="col-6 col-md-3">

                    <div class="paper-card">

                        <div class="material-icon mb-3">

                            <i class="bi bi-award"></i>

                        </div>

                        <div class="exam-number">

                            <?= $totalExamMarks; ?>

                        </div>

                        <div class="exam-label">

                            Total Marks

                        </div>

                    </div>

                </div>



                <!-- MCQ -->

                <div class="col-6 col-md-3">

                    <div class="paper-card">

                        <div class="material-icon mb-3">

                            <i class="bi bi-list-check"></i>

                        </div>

                        <div class="exam-number">

                            <?= $totalMcqMarks; ?>

                        </div>

                        <div class="exam-label">

                            MCQ Marks

                        </div>

                    </div>

                </div>



                <!-- Written -->

                <div class="col-6 col-md-3">

                    <div class="paper-card">

                        <div class="material-icon mb-3">

                            <i class="bi bi-pencil-square"></i>

                        </div>

                        <div class="exam-number">

                            <?= $totalWrittenMarks; ?>

                        </div>

                        <div class="exam-label">

                            Written Marks

                        </div>

                    </div>

                </div>



                <!-- Papers -->

                <div class="col-6 col-md-3">

                    <div class="paper-card">

                        <div class="material-icon mb-3">

                            <i class="bi bi-layers"></i>

                        </div>

                        <div class="exam-number">

                            2

                        </div>

                        <div class="exam-label">

                            Examination Papers

                        </div>

                    </div>

                </div>


            </div>


        </div>


    </div>



    <!-- =================================================
         PAPER DETAILS
    ================================================== -->

    <div class="dashboard-section">


        <div class="row g-4">


            <!-- =================================================
                 FIRST PAPER
            ================================================== -->

            <div class="col-lg-6">

                <div class="paper-card">


                    <div
                        class="d-flex
                               align-items-center
                               gap-3
                               mb-3"
                    >

                        <div class="paper-number">

                            1

                        </div>


                        <div>

                            <div class="paper-title">

                                First Paper

                            </div>

                            <div class="paper-description">

                                General Subjects

                            </div>

                        </div>


                        <span
                            class="badge
                                   bg-light
                                   text-primary
                                   ms-auto"
                        >

                            150 Marks

                        </span>

                    </div>



                    <!-- Physics -->

                    <div class="subject-row">

                        <span class="subject-name">

                            Physics

                        </span>

                        <span class="mark-chip">

                            MCQ 15

                            <span>+</span>

                            Written 25

                        </span>

                    </div>



                    <!-- Chemistry -->

                    <div class="subject-row">

                        <span class="subject-name">

                            Chemistry

                        </span>

                        <span class="mark-chip">

                            MCQ 15

                            <span>+</span>

                            Written 25

                        </span>

                    </div>



                    <!-- Mathematics -->

                    <div class="subject-row">

                        <span class="subject-name">

                            Mathematics

                        </span>

                        <span class="mark-chip">

                            MCQ 15

                            <span>+</span>

                            Written 25

                        </span>

                    </div>



                    <!-- English -->

                    <div class="subject-row">

                        <span class="subject-name">

                            English

                        </span>

                        <span class="mark-chip">

                            MCQ 15

                            <span>+</span>

                            Written 15

                        </span>

                    </div>



                    <div
                        class="d-flex
                               justify-content-between
                               mt-3
                               pt-3
                               border-top"
                    >

                        <span class="small text-muted">

                            MCQ: 60

                        </span>

                        <strong class="small">

                            Written: 90

                        </strong>

                    </div>


                </div>

            </div>



            <!-- =================================================
                 SECOND PAPER
            ================================================== -->

            <div class="col-lg-6">

                <div class="paper-card">


                    <div
                        class="d-flex
                               align-items-center
                               gap-3
                               mb-3"
                    >

                        <div class="paper-number">

                            2

                        </div>


                        <div>

                            <div class="paper-title">

                                Second Paper

                            </div>

                            <div class="paper-description">

                                Technical / Architecture

                            </div>

                        </div>


                        <span
                            class="badge
                                   bg-light
                                   text-primary
                                   ms-auto"
                        >

                            150 Marks

                        </span>

                    </div>



                    <!-- Technical -->

                    <div class="subject-row">

                        <span class="subject-name">

                            Technical Subject /
                            Architecture

                        </span>

                        <span class="mark-chip">

                            MCQ 60

                        </span>

                    </div>



                    <div
                        class="mt-3
                               p-3
                               rounded-3"
                        style="background:#f8f9fa;"
                    >

                        <div
                            class="d-flex
                                   justify-content-between
                                   mb-2"
                        >

                            <span class="small text-muted">

                                MCQ

                            </span>

                            <strong class="small">

                                60 Marks

                            </strong>

                        </div>


                        <div
                            class="d-flex
                                   justify-content-between
                                   mb-2"
                        >

                            <span class="small text-muted">

                                Written

                            </span>

                            <strong class="small">

                                90 Marks

                            </strong>

                        </div>


                        <div
                            class="d-flex
                                   justify-content-between"
                        >

                            <span class="small text-muted">

                                Total

                            </span>

                            <strong class="small text-primary">

                                150 Marks

                            </strong>

                        </div>

                    </div>


                </div>

            </div>


        </div>


    </div>



    <!-- =================================================
         EXAM TIME
    ================================================== -->

    <div class="dashboard-section">


        <div class="material-card">


            <div
                class="d-flex
                       align-items-center
                       gap-3
                       mb-4"
            >

                <div class="material-icon">

                    <i class="bi bi-clock"></i>

                </div>


                <div>

                    <h5 class="dashboard-section-title">

                        Examination Time

                    </h5>

                    <p class="dashboard-section-subtitle">

                        Time allocation according to
                        the admission test pattern.

                    </p>

                </div>

            </div>


            <div class="row g-3">


                <!-- MCQ Time -->

                <div class="col-md-6">

                    <div class="criteria-card">

                        <div class="criteria-value">

                            1 Hour

                        </div>

                        <div class="criteria-label">

                            MCQ section

                            <br>

                            Total MCQ: 120 Marks

                        </div>

                    </div>

                </div>



                <!-- Written Time -->

                <div class="col-md-6">

                    <div class="criteria-card">

                        <div class="criteria-value">

                            1 Hour 30 Minutes

                        </div>

                        <div class="criteria-label">

                            Written section

                            <br>

                            Total Written: 180 Marks

                        </div>

                    </div>

                </div>


            </div>


        </div>


    </div>



    <!-- =================================================
         PASSING CRITERIA
    ================================================== -->

    <div class="dashboard-section">


        <div class="material-card">


            <div
                class="d-flex
                       align-items-center
                       gap-3
                       mb-4"
            >

                <div class="material-icon">

                    <i class="bi bi-check2-circle"></i>

                </div>


                <div>

                    <h5 class="dashboard-section-title">

                        DUET Admission Passing Criteria

                    </h5>

                    <p class="dashboard-section-subtitle">

                        All conditions must be satisfied
                        to qualify for merit consideration.

                    </p>

                </div>

            </div>



            <div class="row g-3">


                <!-- Overall -->

                <div class="col-md-4">

                    <div class="criteria-card">

                        <div class="criteria-value">

                            40%

                        </div>

                        <div class="criteria-label">

                            Minimum overall marks

                            <br>

                            <strong>
                                <?= $overallPassMarks; ?>/300
                            </strong>

                        </div>

                    </div>

                </div>



                <!-- English MCQ -->

                <div class="col-md-4">

                    <div class="criteria-card">

                        <div class="criteria-value">

                            20%

                        </div>

                        <div class="criteria-label">

                            Minimum English MCQ

                            <br>

                            <strong>
                                <?= $englishMcqPassMarks; ?>/15
                            </strong>

                        </div>

                    </div>

                </div>



                <!-- Each Paper -->

                <div class="col-md-4">

                    <div class="criteria-card">

                        <div class="criteria-value">

                            35%

                        </div>

                        <div class="criteria-label">

                            Minimum in each paper

                            <br>

                            <strong>
                                52.5/150 per paper
                            </strong>

                        </div>

                    </div>

                </div>


            </div>



            <!-- Important Note -->

            <div class="material-info mt-3">

                <div class="material-info-icon">

                    <i class="bi bi-info-circle"></i>

                </div>


                <div>

                    <div class="fw-semibold mb-1">

                        Important

                    </div>

                    <div class="small text-muted">

                        The overall 40%, English MCQ 20%,
                        and separate 35% requirement for
                        each paper must all be satisfied.

                    </div>

                </div>

            </div>


        </div>


    </div>



    <!-- =================================================
         ADMISSION INFORMATION
    ================================================== -->

    <div class="dashboard-section">


        <div class="material-card">


            <div
                class="d-flex
                       justify-content-between
                       align-items-center
                       mb-3"
            >

                <div>

                    <h5 class="dashboard-section-title">

                        Admission Information

                    </h5>

                    <p class="dashboard-section-subtitle">

                        Important information for applicants.

                    </p>

                </div>


                <a
                    href="<?= APP_URL; ?>/notices.php"
                    class="text-decoration-none small"
                >

                    View All

                </a>

            </div>



            <!-- Application Fee -->

            <div class="dashboard-notice">

                <div class="d-flex gap-3">


                    <div class="notice-icon">

                        <i class="bi bi-credit-card"></i>

                    </div>


                    <div>

                        <h6 class="mb-1">

                            Application Fee

                        </h6>

                        <p class="small text-muted mb-0">

                            Application fee is Tk. 1,500.
                            Payment can be made through
                            the available payment methods.

                        </p>

                    </div>


                </div>

            </div>



            <!-- Eligibility -->

            <div class="dashboard-notice">

                <div class="d-flex gap-3">


                    <div class="notice-icon">

                        <i class="bi bi-person-check"></i>

                    </div>


                    <div>

                        <h6 class="mb-1">

                            Admission Eligibility

                        </h6>

                        <p class="small text-muted mb-0">

                            Applicants must satisfy the
                            required academic and diploma
                            technology conditions.

                        </p>

                    </div>


                </div>

            </div>



            <!-- Documents -->

            <div class="dashboard-notice">

                <div class="d-flex gap-3">


                    <div class="notice-icon">

                        <i class="bi bi-file-earmark-check"></i>

                    </div>


                    <div>

                        <h6 class="mb-1">

                            Required Documents

                        </h6>

                        <p class="small text-muted mb-0">

                            Keep your applicant photo,
                            signature, identity document
                            and applicable quota document ready.

                        </p>

                    </div>


                </div>

            </div>


        </div>


    </div>



    <!-- =================================================
         CURRENT APPLICATION
    ================================================== -->

    <?php if ($application): ?>

        <div class="dashboard-section">


            <div class="material-card">


                <div
                    class="d-flex
                           align-items-center
                           gap-3
                           mb-4"
                >

                    <div class="material-icon">

                       <i class="bi bi-file-earmark-check"></i>

                    </div>


                    <div>

                        <h5 class="dashboard-section-title">

                            Your Application

                        </h5>

                        <p class="dashboard-section-subtitle">

                            Latest submitted application
                            information.

                        </p>

                    </div>

                </div>



                <div class="row g-3">


                    <!-- Application No -->

                    <div class="col-md-6 col-lg-3">

                        <div class="p-3 bg-light rounded-3">

                            <div class="small text-muted">

                                Application No.

                            </div>

                            <div class="fw-bold mt-1">

                                <?= e(
                                    $application['application_no']
                                ); ?>

                            </div>

                        </div>

                    </div>



                    <!-- Department -->

                    <div class="col-md-6 col-lg-3">

                        <div class="p-3 bg-light rounded-3">

                            <div class="small text-muted">

                                Department

                            </div>

                            <div class="fw-bold mt-1">

                                <?= e(
                                    $application['department_name']
                                ); ?>

                            </div>

                        </div>

                    </div>



                    <!-- Technology -->

                    <div class="col-md-6 col-lg-3">

                        <div class="p-3 bg-light rounded-3">

                            <div class="small text-muted">

                                Diploma Technology

                            </div>

                            <div class="fw-bold mt-1">

                                <?= e(
                                    $application['technology_name']
                                ); ?>

                            </div>

                        </div>

                    </div>



                    <!-- Quota -->

                    <div class="col-md-6 col-lg-3">

                        <div class="p-3 bg-light rounded-3">

                            <div class="small text-muted">

                                Quota

                            </div>

                            <div class="fw-bold mt-1">

                                <?= e(
                                    $application['quota']
                                ); ?>

                            </div>

                        </div>

                    </div>


                </div>



                <div class="mt-4">


                    <a
                        href="<?= APP_URL; ?>/application-status.php"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-arrow-right-circle me-1"></i>

                        View Application Status

                    </a>


                    <?php if (
                        $resultStatus !== 'Not Published'
                    ): ?>

                        <a
                            href="<?= APP_URL; ?>/result.php"
                            class="btn btn-outline-primary ms-2"
                        >

                            <i class="bi bi-award me-1"></i>

                            View Result

                        </a>

                    <?php endif; ?>


                </div>


            </div>


        </div>

    <?php endif; ?>


</div>


<?php

/*
|--------------------------------------------------------------------------
| COMMON FOOTER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/footer.php';

?>