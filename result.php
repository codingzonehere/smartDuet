<?php

/**
 * Smart DUET Admission Management System
 * Applicant Result Page
 *
 * Purpose:
 * - Show logged-in applicant's result
 * - Show merit position
 * - Show result status
 * - Show published date
 * - Show admin remarks
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';


// ======================================================
// PAGE TITLE
// ======================================================

$pageTitle = 'Admission Result';


// ======================================================
// GET LOGGED-IN USER
// ======================================================

$userId = getUserId();


// ======================================================
// GET APPLICANT + APPLICATION + RESULT
// ======================================================

$application = null;

$stmt = $conn->prepare("
    SELECT

        a.id AS application_id,
        a.application_no,
        a.admission_year,

        ap.full_name,
        ap.father_name,

        d.code AS department_code,
        d.name AS department_name,

        t.name AS technology_name,

        r.merit_position,
        r.result_status,
        r.published_date,
        r.remarks

    FROM applicants ap

    INNER JOIN applications a
        ON a.applicant_id = ap.id

    INNER JOIN departments d
        ON a.department_id = d.id

    INNER JOIN diploma_technologies t
        ON a.technology_id = t.id

    LEFT JOIN results r
        ON a.id = r.application_id

    WHERE ap.user_id = ?

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


// ======================================================
// RESULT STATUS
// ======================================================

$resultStatus =
    $application['result_status']
    ?? null;


// ======================================================
// STATUS INFORMATION
// ======================================================

$statusTitle =
    'Result Pending';

$statusMessage =
    'Your admission result has not been published yet.';

$statusIcon =
    'bi-hourglass-split';

$statusClass =
    'result-pending';


if ($resultStatus === 'selected') {

    $statusTitle =
        'Congratulations!';

    $statusMessage =
        'You have been selected for admission.';

    $statusIcon =
        'bi-check-circle-fill';

    $statusClass =
        'result-selected';

} elseif (
    $resultStatus === 'waiting'
) {

    $statusTitle =
        'Waiting List';

    $statusMessage =
        'You are currently on the waiting list.';

    $statusIcon =
        'bi-clock-history';

    $statusClass =
        'result-waiting';

} elseif (
    $resultStatus === 'not_selected'
) {

    $statusTitle =
        'Not Selected';

    $statusMessage =
        'You have not been selected for admission.';

    $statusIcon =
        'bi-x-circle-fill';

    $statusClass =
        'result-not-selected';

}


// ======================================================
// FLASH MESSAGE
// ======================================================

$flash = getFlashMessage();


// ======================================================
// SHARED HEADER
// ======================================================

require_once __DIR__ . '/includes/header.php';

?>

<link
        rel="icon"
        type="image/x-icon"
        href="<?= APP_URL ?>/assets/images/duet-logo.png"
    >


<style>

/* =========================================================
   RESULT PAGE
========================================================= */

.result-page {

    max-width: 1250px;

    margin: 0 auto;

}


/* =========================================================
   HERO
========================================================= */

.result-hero {

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

    margin-bottom: 25px;

    box-shadow:
        0 12px 30px
        rgba(37, 99, 235, .20);

}

.result-hero::before {

    content: "";

    position: absolute;

    width: 190px;

    height: 190px;

    border-radius: 50%;

    background:
        rgba(255,255,255,.08);

    right: 50px;

    top: -100px;

}

.result-hero::after {

    content: "";

    position: absolute;

    width: 120px;

    height: 120px;

    border-radius: 50%;

    background:
        rgba(255,255,255,.06);

    right: -30px;

    bottom: -50px;

}

.result-hero-content {

    position: relative;

    z-index: 2;

}

.result-hero-icon {

    width: 52px;

    height: 52px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 15px;

    background:
        rgba(255,255,255,.16);

    font-size: 25px;

    margin-bottom: 14px;

}

.result-hero h2 {

    font-size: 26px;

    font-weight: 800;

    margin-bottom: 7px;

}

.result-hero p {

    margin: 0;

    font-size: 13px;

    color:
        rgba(255,255,255,.88);

}


/* =========================================================
   FLASH
========================================================= */

.result-alert {

    border: none;

    border-radius: 14px;

    box-shadow:
        0 5px 18px
        rgba(15,23,42,.05);

}


/* =========================================================
   RESULT STATUS CARD
========================================================= */

.result-status-card {

    position: relative;

    overflow: hidden;

    border-radius: 20px;

    padding: 34px 25px;

    text-align: center;

    margin-bottom: 22px;

    border: 1px solid;

    box-shadow:
        0 7px 24px
        rgba(15,23,42,.06);

}

.result-status-card::before {

    content: "";

    position: absolute;

    width: 150px;

    height: 150px;

    border-radius: 50%;

    top: -80px;

    right: -45px;

    opacity: .45;

}

.result-status-icon {

    position: relative;

    z-index: 2;

    width: 76px;

    height: 76px;

    border-radius: 22px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    font-size: 34px;

    margin-bottom: 15px;

}

.result-status-card h3 {

    position: relative;

    z-index: 2;

    font-size: 23px;

    font-weight: 800;

    margin-bottom: 7px;

}

.result-status-card p {

    position: relative;

    z-index: 2;

    margin: 0;

    font-size: 13px;

}


/* =========================================================
   PENDING
========================================================= */

.result-pending {

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #f5f7fa
        );

    border-color: #e1e5ea;

}

.result-pending::before {

    background: #e2e8f0;

}

.result-pending
.result-status-icon {

    background: #e9eef4;

    color: #64748b;

}

.result-pending h3 {

    color: #475569;

}


/* =========================================================
   SELECTED
========================================================= */

.result-selected {

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #ecfbf2
        );

    border-color: #b7e4c7;

}

.result-selected::before {

    background: #bbf7d0;

}

.result-selected
.result-status-icon {

    background: #d1fae5;

    color: #198754;

}

.result-selected h3 {

    color: #198754;

}


/* =========================================================
   WAITING
========================================================= */

.result-waiting {

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #fff9e8
        );

    border-color: #ffe69c;

}

.result-waiting::before {

    background: #fde68a;

}

.result-waiting
.result-status-icon {

    background: #fff3cd;

    color: #997404;

}

.result-waiting h3 {

    color: #997404;

}


/* =========================================================
   NOT SELECTED
========================================================= */

.result-not-selected {

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #fff1f2
        );

    border-color: #f1b0b7;

}

.result-not-selected::before {

    background: #fecdd3;

}

.result-not-selected
.result-status-icon {

    background: #f8d7da;

    color: #b02a37;

}

.result-not-selected h3 {

    color: #b02a37;

}


/* =========================================================
   COMMON CARD
========================================================= */

.result-card {

    background: #ffffff;

    border: 1px solid #e7edf5;

    border-radius: 18px;

    padding: 23px;

    margin-bottom: 20px;

    box-shadow:
        0 6px 22px
        rgba(15,23,42,.045);

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}

.result-card:hover {

    box-shadow:
        0 10px 28px
        rgba(15,23,42,.07);

}

.result-card-title {

    display: flex;

    align-items: center;

    font-size: 16px;

    font-weight: 800;

    color: #172033;

    padding-bottom: 15px;

    margin-bottom: 5px;

    border-bottom:
        1px solid #edf1f6;

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
   RESULT INFORMATION FIELD
========================================================= */

.result-info {

    position: relative;

    background:
        linear-gradient(
            145deg,
            #ffffff,
            #f8fafc
        );

    border: 1px solid #e8edf4;

    border-radius: 13px;

    padding: 15px 16px;

    height: 100%;

    transition:
        transform .2s ease,
        box-shadow .2s ease,
        border-color .2s ease;

}

.result-info:hover {

    transform: translateY(-2px);

    border-color: #cfe0ff;

    box-shadow:
        0 7px 18px
        rgba(13,110,253,.07);

}

.result-info::before {

    content: "";

    position: absolute;

    left: 0;

    top: 12px;

    bottom: 12px;

    width: 3px;

    border-radius: 5px;

    background:
        linear-gradient(
            180deg,
            #0d6efd,
            #6366f1
        );

}

.result-label {

    font-size: 11px;

    font-weight: 700;

    color: #64748b;

    text-transform: uppercase;

    letter-spacing: .45px;

    margin-bottom: 6px;

}

.result-value {

    font-size: 13px;

    font-weight: 700;

    color: #1e293b;

    word-break: break-word;

}


/* =========================================================
   MERIT POSITION
========================================================= */

.merit-card {

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #f3f7ff
        );

}

.merit-box {

    position: relative;

    overflow: hidden;

    text-align: center;

    background:
        linear-gradient(
            135deg,
            #eef5ff,
            #f4f2ff
        );

    border: 1px solid #dce8ff;

    border-radius: 16px;

    padding: 28px 20px;

}

.merit-box::before {

    content: "";

    position: absolute;

    width: 100px;

    height: 100px;

    border-radius: 50%;

    background:
        rgba(13,110,253,.07);

    left: -30px;

    bottom: -40px;

}

.merit-box::after {

    content: "";

    position: absolute;

    width: 90px;

    height: 90px;

    border-radius: 50%;

    background:
        rgba(99,102,241,.06);

    right: -25px;

    top: -35px;

}

.merit-label {

    position: relative;

    z-index: 2;

    font-size: 12px;

    font-weight: 700;

    color: #64748b;

    text-transform: uppercase;

    letter-spacing: .5px;

}

.merit-number {

    position: relative;

    z-index: 2;

    font-size: 46px;

    line-height: 1.15;

    font-weight: 900;

    color: #0d6efd;

    margin-top: 6px;

}

.merit-position-text {

    position: relative;

    z-index: 2;

    color: #64748b;

    font-size: 12px;

    margin-top: 4px;

}


/* =========================================================
   RESULT STATUS BADGE
========================================================= */

.result-status-badge {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 8px 14px;

    border-radius: 50px;

    font-size: 12px;

    font-weight: 800;

}

.result-status-badge i {

    font-size: 9px;

}


/* =========================================================
   REMARKS
========================================================= */

.result-remarks {

    position: relative;

    background:
        linear-gradient(
            135deg,
            #f5f9ff,
            #f8f7ff
        );

    border: 1px solid #dfe8f7;

    border-left:
        4px solid #0d6efd;

    border-radius: 12px;

    padding: 16px 18px;

    color: #475569;

    font-size: 13px;

    line-height: 1.6;

}


/* =========================================================
   NOT PUBLISHED
========================================================= */

.no-result-content {

    text-align: center;

    padding: 28px 15px;

}

.no-result-icon {

    width: 70px;

    height: 70px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: 0 auto 16px;

    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #eef2f7,
            #f8fafc
        );

    color: #64748b;

    font-size: 30px;

}

.no-result-content h5 {

    font-weight: 800;

    color: #1e293b;

}

.no-result-content p {

    font-size: 12px;

}


/* =========================================================
   NO APPLICATION
========================================================= */

.no-application {

    background: #ffffff;

    border: 1px solid #e7edf5;

    border-radius: 20px;

    padding: 60px 25px;

    text-align: center;

    box-shadow:
        0 8px 25px
        rgba(15,23,42,.05);

}

.no-application-icon {

    width: 80px;

    height: 80px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: 0 auto 18px;

    border-radius: 22px;

    background:
        linear-gradient(
            135deg,
            #eef2f7,
            #f8fafc
        );

    color: #64748b;

    font-size: 32px;

}

.no-application h4 {

    font-weight: 800;

    color: #172033;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 992px) {

    .result-hero {

        padding: 27px;

    }

    .result-hero h2 {

        font-size: 23px;

    }

}


@media (max-width: 768px) {

    .result-page {

        width: 100%;

    }

    .result-hero {

        border-radius: 16px;

        padding: 23px 20px;

    }

    .result-hero-icon {

        width: 45px;

        height: 45px;

        font-size: 21px;

    }

    .result-hero h2 {

        font-size: 20px;

    }

    .result-hero p {

        font-size: 12px;

    }

    .result-status-card {

        padding: 28px 18px;

    }

    .result-status-card h3 {

        font-size: 20px;

    }

    .result-card {

        padding: 18px;

    }

    .merit-number {

        font-size: 40px;

    }

}


@media (max-width: 576px) {

    .result-card-title {

        font-size: 14px;

    }

    .result-info {

        padding: 14px;

    }

    .result-label {

        font-size: 10px;

    }

    .result-value {

        font-size: 12px;

    }

    .result-status-icon {

        width: 68px;

        height: 68px;

        font-size: 29px;

    }

}


/* =========================================================
   PAGE ANIMATION
========================================================= */

.result-page > * {

    animation:
        resultFade .45s ease both;

}

@keyframes resultFade {

    from {

        opacity: 0;

        transform:
            translateY(8px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}

</style>


<div class="content-area">

    <div class="result-page">


        <!-- =================================================
             HERO
        ================================================== -->

        <section class="result-hero">

            <div class="result-hero-content">

                <div class="result-hero-icon">

                    <i class="bi bi-trophy"></i>

                </div>

                <h2>

                    Admission Result

                </h2>

                <p>

                    Check your Smart DUET admission result,
                    merit position and admission status.

                </p>

            </div>

        </section>


        <!-- =================================================
             FLASH MESSAGE
        ================================================== -->

        <?php if ($flash): ?>

            <div
                class="alert alert-<?= e($flash['type']) ?>
                       alert-dismissible fade show
                       result-alert mb-4"
                role="alert"
            >

                <i class="bi bi-info-circle-fill me-2"></i>

                <?= e($flash['message']) ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <!-- =================================================
             NO APPLICATION
        ================================================== -->

        <?php if (!$application): ?>


            <div class="no-application">

                <div class="no-application-icon">

                    <i class="bi bi-file-earmark-x"></i>

                </div>

                <h4>

                    No Application Found

                </h4>

                <p class="text-muted mb-0">

                    You have not submitted an admission
                    application yet.

                </p>

            </div>


        <?php else: ?>


            <!-- =================================================
                 RESULT STATUS
            ================================================== -->

            <div
                class="result-status-card
                       <?= e($statusClass) ?>"
            >

                <div class="result-status-icon">

                    <i
                        class="bi <?= e($statusIcon) ?>"
                    ></i>

                </div>

                <h3>

                    <?= e($statusTitle) ?>

                </h3>

                <p>

                    <?= e($statusMessage) ?>

                </p>

            </div>


            <!-- =================================================
                 APPLICATION INFORMATION
            ================================================== -->

            <div class="result-card">

                <div class="result-card-title">

                    <span class="card-title-icon">

                        <i class="bi bi-file-earmark-text"></i>

                    </span>

                    Application Information

                </div>


                <div class="row g-3">


                    <!-- APPLICATION NUMBER -->

                    <div class="col-md-4">

                        <div class="result-info">

                            <div class="result-label">

                                Application Number

                            </div>

                            <div class="result-value">

                                <?= e(
                                    $application['application_no']
                                ) ?>

                            </div>

                        </div>

                    </div>


                    <!-- APPLICANT NAME -->

                    <div class="col-md-4">

                        <div class="result-info">

                            <div class="result-label">

                                Applicant Name

                            </div>

                            <div class="result-value">

                                <?= e(
                                    $application['full_name']
                                ) ?>

                            </div>

                        </div>

                    </div>


                    <!-- FATHER NAME -->

                    <div class="col-md-4">

                        <div class="result-info">

                            <div class="result-label">

                                Father's Name

                            </div>

                            <div class="result-value">

                                <?= e(
                                    $application['father_name']
                                ) ?>

                            </div>

                        </div>

                    </div>


                    <!-- DEPARTMENT -->

                    <div class="col-md-6">

                        <div class="result-info">

                            <div class="result-label">

                                Department

                            </div>

                            <div class="result-value">

                                <?= e(
                                    $application['department_code']
                                ) ?>

                                -

                                <?= e(
                                    $application['department_name']
                                ) ?>

                            </div>

                        </div>

                    </div>


                    <!-- TECHNOLOGY -->

                    <div class="col-md-6">

                        <div class="result-info">

                            <div class="result-label">

                                Diploma Technology

                            </div>

                            <div class="result-value">

                                <?= e(
                                    $application['technology_name']
                                ) ?>

                            </div>

                        </div>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 MERIT POSITION
            ================================================== -->

            <?php if (
                $resultStatus &&
                !empty(
                    $application['merit_position']
                )
            ): ?>


                <div class="result-card merit-card">

                    <div class="result-card-title">

                        <span class="card-title-icon">

                            <i class="bi bi-bar-chart-line"></i>

                        </span>

                        Merit Position

                    </div>


                    <div class="merit-box">

                        <div class="merit-label">

                            Your Merit Position

                        </div>

                        <div class="merit-number">

                            <?= e(
                                $application['merit_position']
                            ) ?>

                        </div>

                        <div class="merit-position-text">

                            DUET Admission Merit Ranking

                        </div>

                    </div>

                </div>


            <?php endif; ?>


            <!-- =================================================
                 RESULT DETAILS
            ================================================== -->

            <?php if ($resultStatus): ?>


                <div class="result-card">

                    <div class="result-card-title">

                        <span class="card-title-icon">

                            <i class="bi bi-info-circle"></i>

                        </span>

                        Result Details

                    </div>


                    <div class="row g-3">


                        <!-- RESULT STATUS -->

                        <div class="col-md-6">

                            <div class="result-info">

                                <div class="result-label">

                                    Result Status

                                </div>

                                <div class="result-value">


                                    <?php

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


                                    echo e(
                                        $resultLabels[
                                            $resultStatus
                                        ]
                                        ??
                                        'Pending'
                                    );

                                    ?>


                                </div>

                            </div>

                        </div>


                        <!-- PUBLISHED DATE -->

                        <?php if (
                            !empty(
                                $application['published_date']
                            )
                        ): ?>

                            <div class="col-md-6">

                                <div class="result-info">

                                    <div class="result-label">

                                        Result Published

                                    </div>

                                    <div class="result-value">

                                        <?= e(
                                            date(
                                                'd M Y',
                                                strtotime(
                                                    $application[
                                                        'published_date'
                                                    ]
                                                )
                                            )
                                        ) ?>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>


                    </div>


                    <!-- =================================================
                         REMARKS
                    ================================================== -->

                    <?php if (
                        !empty(
                            $application['remarks']
                        )
                    ): ?>

                        <div class="mt-4">

                            <div class="result-label mb-2">

                                Remarks

                            </div>

                            <div class="result-remarks">

                                <i
                                    class="bi bi-chat-left-text
                                           me-2 text-primary"
                                ></i>

                                <?= nl2br(
                                    e(
                                        $application['remarks']
                                    )
                                ) ?>

                            </div>

                        </div>

                    <?php endif; ?>


                </div>


            <?php else: ?>


                <!-- =================================================
                     RESULT NOT PUBLISHED
                ================================================== -->

                <div class="result-card">

                    <div class="no-result-content">

                        <div class="no-result-icon">

                            <i class="bi bi-hourglass-split"></i>

                        </div>

                        <h5>

                            Result Not Published

                        </h5>

                        <p class="text-muted mb-0">

                            Please check again after the admission
                            authority publishes the result.

                        </p>

                    </div>

                </div>


            <?php endif; ?>


        <?php endif; ?>


    </div>

</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>