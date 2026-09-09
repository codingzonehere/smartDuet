<?php

/*
|--------------------------------------------------------------------------
| ELIGIBILITY CHECK
|--------------------------------------------------------------------------
| Check applicant eligibility for DUET undergraduate admission.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';


// --------------------------------------------------
// Page Title
// --------------------------------------------------

$pageTitle = 'Eligibility Check';


// --------------------------------------------------
// Current Year
// --------------------------------------------------

$currentYear = (int) date('Y');


// --------------------------------------------------
// Project Rule
// --------------------------------------------------
//
// Keep existing project year logic.
//
// Dropdown:
// Current Year - 1
// Current Year - 2
//
// --------------------------------------------------

$diplomaYears = [
    $currentYear - 1,
    $currentYear - 2
];


// --------------------------------------------------
// Get Diploma Technologies
// --------------------------------------------------

$technologies = [];

$sql = "
    SELECT
        id,
        name
    FROM diploma_technologies
    WHERE is_active = 1
    ORDER BY name ASC
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $technologies[] = $row;

    }

}


// --------------------------------------------------
// Flash Message
// --------------------------------------------------

$flash = getFlashMessage();


// --------------------------------------------------
// Eligibility Result from Session
// --------------------------------------------------

$eligibilityResult =
    $_SESSION['eligibility_result'] ?? null;


// --------------------------------------------------
// Keep Submitted Values in Form
// --------------------------------------------------

$sscGpaValue =
    $eligibilityResult['ssc_gpa'] ?? '';

$diplomaCgpaValue =
    $eligibilityResult['diploma_cgpa'] ?? '';

$passingYearValue =
    $eligibilityResult['diploma_passing_year'] ?? '';

$technologyValue =
    $eligibilityResult['technology_id'] ?? '';


// --------------------------------------------------
// Remove Result After Reading
// --------------------------------------------------

unset($_SESSION['eligibility_result']);


// --------------------------------------------------
// Common Header
// --------------------------------------------------

require_once __DIR__ . '/includes/header.php';

?>


<style>

/* =========================================================
   ELIGIBILITY PAGE
========================================================= */

.eligibility-page {

    max-width: 1250px;

    margin: 0 auto;

}


/* =========================================================
   HERO
========================================================= */

.eligibility-hero {

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

.eligibility-hero::before {

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

.eligibility-hero::after {

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

.eligibility-hero-content {

    position: relative;

    z-index: 2;

}

.eligibility-hero-icon {

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

.eligibility-hero h2 {

    font-size: 26px;

    font-weight: 800;

    margin-bottom: 7px;

}

.eligibility-hero p {

    margin: 0;

    font-size: 13px;

    color:
        rgba(255,255,255,.88);

}


/* =========================================================
   FLASH MESSAGE
========================================================= */

.eligibility-alert {

    border: none;

    border-radius: 14px;

    box-shadow:
        0 5px 18px
        rgba(15,23,42,.05);

}


/* =========================================================
   COMMON CARD
========================================================= */

.eligibility-card {

    background: #ffffff;

    border: 1px solid #e7edf5;

    border-radius: 18px;

    padding: 23px;

    box-shadow:
        0 6px 22px
        rgba(15,23,42,.045);

    transition:
        box-shadow .25s ease,
        transform .25s ease;

}

.eligibility-card:hover {

    box-shadow:
        0 10px 28px
        rgba(15,23,42,.07);

}


/* =========================================================
   CARD TITLE
========================================================= */

.card-heading {

    display: flex;

    align-items: center;

    gap: 10px;

    padding-bottom: 15px;

    margin-bottom: 20px;

    border-bottom:
        1px solid #edf1f6;

}

.card-heading-icon {

    width: 38px;
    height: 38px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 11px;

    background:
        #eaf2ff;

    color:
        #0d6efd;

    font-size: 17px;

}

.card-heading h5 {

    margin: 0;

    color: #172033;

    font-size: 16px;

    font-weight: 800;

}

.card-heading small {

    display: block;

    margin-top: 2px;

    color: #94a3b8;

    font-size: 11px;

}


/* =========================================================
   FORM FIELD
========================================================= */

.form-field {

    position: relative;

}

.form-field .form-label {

    display: flex;

    align-items: center;

    gap: 5px;

    color: #334155;

    font-size: 12px;

    font-weight: 700;

    margin-bottom: 8px;

}

.form-field .form-control,
.form-field .form-select {

    height: 46px;

    border: 1px solid #dfe6ef;

    border-radius: 11px;

    background: #ffffff;

    color: #1e293b;

    font-size: 13px;

    font-weight: 600;

    padding-left: 14px;

    padding-right: 14px;

    box-shadow: none;

    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background .2s ease;

}

.form-field .form-control::placeholder {

    color: #a3adba;

    font-weight: 400;

}

.form-field .form-control:hover,
.form-field .form-select:hover {

    border-color: #b9c9df;

    background: #fbfdff;

}

.form-field .form-control:focus,
.form-field .form-select:focus {

    border-color: #86b7fe;

    box-shadow:
        0 0 0 3px
        rgba(13,110,253,.09);

    background: #ffffff;

}

.field-help {

    display: block;

    margin-top: 6px;

    color: #94a3b8;

    font-size: 10px;

    font-weight: 500;

}

.required-star {

    color: #dc3545;

}


/* =========================================================
   INPUT ICON STYLE
========================================================= */

.input-wrapper {

    position: relative;

}

.input-wrapper-icon {

    position: absolute;

    left: 13px;

    top: 50%;

    transform: translateY(-50%);

    color: #64748b;

    font-size: 15px;

    z-index: 2;

    pointer-events: none;

}

.input-wrapper .form-control {

    padding-left: 39px;

}


/* =========================================================
   CHECK BUTTON
========================================================= */

.check-btn {

    border: none;

    border-radius: 11px;

    padding: 10px 18px;

    font-size: 12px;

    font-weight: 700;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #4f46e5
        );

    box-shadow:
        0 6px 15px
        rgba(13,110,253,.18);

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}

.check-btn:hover {

    transform: translateY(-2px);

    box-shadow:
        0 9px 20px
        rgba(13,110,253,.25);

}


/* =========================================================
   REQUIREMENT CARD
========================================================= */

.requirement-item {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px;

    border: 1px solid #e9eef5;

    border-radius: 13px;

    background:
        linear-gradient(
            145deg,
            #ffffff,
            #f8fafc
        );

    margin-bottom: 11px;

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}

.requirement-item:last-child {

    margin-bottom: 0;

}

.requirement-item:hover {

    transform: translateX(3px);

    box-shadow:
        0 5px 15px
        rgba(15,23,42,.05);

}

.requirement-icon {

    flex-shrink: 0;

    width: 40px;
    height: 40px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 11px;

    background: #eaf2ff;

    color: #0d6efd;

    font-size: 17px;

}

.requirement-title {

    color: #1e293b;

    font-size: 12px;

    font-weight: 800;

    margin-bottom: 3px;

}

.requirement-text {

    color: #94a3b8;

    font-size: 10px;

    margin: 0;

}


/* =========================================================
   IMPORTANT BOX
========================================================= */

.important-box {

    margin-top: 15px;

    padding: 14px;

    border-radius: 13px;

    border: 1px solid #dbeafe;

    background:
        linear-gradient(
            135deg,
            #eff6ff,
            #f5f3ff
        );

}

.important-box-title {

    color: #1d4ed8;

    font-size: 12px;

    font-weight: 800;

    margin-bottom: 5px;

}

.important-box p {

    color: #64748b;

    font-size: 10px;

    line-height: 1.55;

    margin: 0;

}


/* =========================================================
   ELIGIBILITY RESULT CARD
========================================================= */

.eligibility-result {

    border-radius: 18px;

    padding: 23px;

    margin-bottom: 23px;

    border: 1px solid;

    box-shadow:
        0 7px 22px
        rgba(15,23,42,.05);

}


/* =========================================================
   ELIGIBLE RESULT
========================================================= */

.eligible-result {

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #effcf4
        );

    border-color: #b7e4c7;

}

.eligible-result .result-icon {

    background: #d1fae5;

    color: #198754;

}


/* =========================================================
   NOT ELIGIBLE RESULT
========================================================= */

.not-eligible-result {

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #fff2f2
        );

    border-color: #f1b0b7;

}

.not-eligible-result .result-icon {

    background: #f8d7da;

    color: #dc3545;

}


/* =========================================================
   RESULT ICON
========================================================= */

.result-icon {

    flex-shrink: 0;

    width: 48px;
    height: 48px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 14px;

    font-size: 22px;

}


/* =========================================================
   RESULT CONTENT
========================================================= */

.result-heading {

    font-size: 16px;

    font-weight: 800;

    margin-bottom: 5px;

}

.eligible-result .result-heading {

    color: #198754;

}

.not-eligible-result .result-heading {

    color: #dc3545;

}

.result-description {

    color: #64748b;

    font-size: 12px;

    line-height: 1.6;

    margin-bottom: 17px;

}


/* =========================================================
   DEPARTMENT RESULT CARD
========================================================= */

.department-result-card {

    position: relative;

    height: 100%;

    padding: 16px;

    border-radius: 14px;

    background: #ffffff;

    border: 1px solid #e2e8f0;

    transition:
        transform .2s ease,
        box-shadow .2s ease,
        border-color .2s ease;

}

.department-result-card:hover {

    transform: translateY(-3px);

    border-color: #c9dcfa;

    box-shadow:
        0 8px 20px
        rgba(13,110,253,.08);

}

.department-code {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 42px;

    padding: 5px 8px;

    border-radius: 7px;

    background: #eaf2ff;

    color: #0d6efd;

    font-size: 10px;

    font-weight: 800;

    margin-bottom: 10px;

}

.department-name {

    color: #172033;

    font-size: 13px;

    font-weight: 800;

    line-height: 1.4;

    margin-bottom: 5px;

}

.department-degree {

    color: #64748b;

    font-size: 10px;

    margin-bottom: 10px;

}

.department-seats {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    color: #198754;

    background: #ecfdf3;

    border-radius: 7px;

    padding: 5px 8px;

    font-size: 10px;

    font-weight: 700;

}


/* =========================================================
   APPLY BUTTON
========================================================= */

.apply-btn {

    border: none;

    border-radius: 10px;

    padding: 9px 16px;

    font-size: 11px;

    font-weight: 700;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #4f46e5
        );

    box-shadow:
        0 5px 14px
        rgba(13,110,253,.17);

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}

.apply-btn:hover {

    transform: translateY(-2px);

    box-shadow:
        0 8px 18px
        rgba(13,110,253,.23);

}


/* =========================================================
   REASON LIST
========================================================= */

.reason-list {

    margin: 0;

    padding: 0;

    list-style: none;

}

.reason-list li {

    display: flex;

    align-items: flex-start;

    gap: 9px;

    padding: 9px 11px;

    margin-bottom: 7px;

    border-radius: 9px;

    background: #fffafa;

    border: 1px solid #fde2e2;

    color: #b42318;

    font-size: 11px;

    line-height: 1.5;

}

.reason-list li:last-child {

    margin-bottom: 0;

}

.reason-icon {

    flex-shrink: 0;

    margin-top: 1px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 992px) {

    .eligibility-hero {

        padding: 27px;

    }

    .eligibility-hero h2 {

        font-size: 23px;

    }

}


@media (max-width: 768px) {

    .eligibility-page {

        width: 100%;

    }

    .eligibility-hero {

        border-radius: 16px;

        padding: 23px 20px;

    }

    .eligibility-hero-icon {

        width: 45px;
        height: 45px;

        font-size: 21px;

    }

    .eligibility-hero h2 {

        font-size: 20px;

    }

    .eligibility-hero p {

        font-size: 12px;

    }

    .eligibility-card {

        padding: 18px;

    }

    .eligibility-result {

        padding: 18px;

    }

}


@media (max-width: 576px) {

    .card-heading h5 {

        font-size: 14px;

    }

    .card-heading small {

        font-size: 10px;

    }

    .form-field .form-control,
    .form-field .form-select {

        height: 44px;

        font-size: 12px;

    }

    .result-heading {

        font-size: 14px;

    }

}


/* =========================================================
   PAGE ANIMATION
========================================================= */

.eligibility-page > * {

    animation:
        eligibilityFade .45s ease both;

}

@keyframes eligibilityFade {

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

    <div class="eligibility-page">


        <!-- =================================================
             HERO
        ================================================== -->

        <div class="eligibility-hero">

            <div class="eligibility-hero-content">

                <div class="eligibility-hero-icon">

                    <i class="bi bi-person-check"></i>

                </div>

                <h2>

                    Eligibility Check

                </h2>

                <p>

                    Check whether you meet the basic eligibility
                    requirements for DUET undergraduate admission.

                </p>

            </div>

        </div>


        <!-- =================================================
             FLASH MESSAGE
        ================================================== -->

        <?php if ($flash): ?>

            <div
                class="alert alert-<?= e($flash['type']); ?>
                       alert-dismissible fade show
                       eligibility-alert mb-4"
                role="alert"
            >

                <i class="bi bi-exclamation-circle-fill me-2"></i>

                <?= e($flash['message']); ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <!-- =================================================
             ELIGIBILITY RESULT
        ================================================== -->

        <?php if ($eligibilityResult): ?>


            <!-- =================================================
                 ELIGIBLE
            ================================================== -->

            <?php if ($eligibilityResult['eligible']): ?>

                <div class="eligibility-result eligible-result">

                    <div class="d-flex align-items-start gap-3">

                        <div class="result-icon">

                            <i class="bi bi-check-lg"></i>

                        </div>


                        <div class="flex-grow-1">

                            <div class="result-heading">

                                You are eligible for admission.

                            </div>

                            <div class="result-description">

                                Based on your academic information and
                                Diploma Technology, you are eligible for:

                            </div>


                            <!-- =================================================
                                 ELIGIBLE DEPARTMENTS
                            ================================================== -->

                            <?php if (
                                !empty(
                                    $eligibilityResult['departments']
                                )
                            ): ?>

                                <div class="row g-3">


                                    <?php foreach (
                                        $eligibilityResult['departments']
                                        as $department
                                    ): ?>

                                        <div class="col-md-6">

                                            <div
                                                class="department-result-card"
                                            >

                                                <div class="department-code">

                                                    <?= e(
                                                        $department['code']
                                                    ); ?>

                                                </div>

                                                <div class="department-name">

                                                    <?= e(
                                                        $department['name']
                                                    ); ?>

                                                </div>

                                                <div class="department-degree">

                                                    <?= e(
                                                        $department['degree']
                                                    ); ?>

                                                </div>

                                                <div class="department-seats">

                                                    <i
                                                        class="bi bi-people-fill"
                                                    ></i>

                                                    Seats:
                                                    <?= (int)
                                                        $department['seats'];
                                                    ?>

                                                </div>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>


                                </div>


                                <!-- APPLY BUTTON -->

                                <div class="mt-4">

                                    <a
                                        href="<?= APP_URL ?>/apply.php"
                                        class="btn btn-primary apply-btn"
                                    >

                                        <i
                                            class="bi bi-file-earmark-plus me-2"
                                        ></i>

                                        Apply for Admission

                                    </a>

                                </div>


                            <?php endif; ?>


                        </div>

                    </div>

                </div>


            <!-- =================================================
                 NOT ELIGIBLE
            ================================================== -->

            <?php else: ?>

                <div
                    class="eligibility-result
                           not-eligible-result"
                >

                    <div class="d-flex align-items-start gap-3">


                        <div class="result-icon">

                            <i class="bi bi-x-lg"></i>

                        </div>


                        <div class="flex-grow-1">

                            <div class="result-heading">

                                You are not eligible for admission.

                            </div>

                            <div class="result-description mb-3">

                                Please check the following
                                requirement(s):

                            </div>


                            <?php if (
                                !empty(
                                    $eligibilityResult['reasons']
                                )
                            ): ?>

                                <ul class="reason-list">

                                    <?php foreach (
                                        $eligibilityResult['reasons']
                                        as $reason
                                    ): ?>

                                        <li>

                                            <i
                                                class="bi bi-x-circle-fill
                                                       reason-icon"
                                            ></i>

                                            <span>

                                                <?= e($reason); ?>

                                            </span>

                                        </li>

                                    <?php endforeach; ?>

                                </ul>

                            <?php endif; ?>


                        </div>

                    </div>

                </div>

            <?php endif; ?>


        <?php endif; ?>


        <!-- =================================================
             MAIN CONTENT
        ================================================== -->

        <div class="row g-4">


            <!-- =================================================
                 ELIGIBILITY FORM
            ================================================== -->

            <div class="col-lg-8">

                <div class="eligibility-card">


                    <div class="card-heading">

                        <div class="card-heading-icon">

                            <i class="bi bi-clipboard-check"></i>

                        </div>

                        <div>

                            <h5>

                                Enter Academic Information

                            </h5>

                            <small>

                                Enter your academic information
                                to check eligibility.

                            </small>

                        </div>

                    </div>


                    <form
                        action="<?= APP_URL ?>/actions/eligibility_action.php"
                        method="POST"
                    >


                        <div class="row g-4">


                            <!-- =================================================
                                 SSC GPA
                            ================================================== -->

                            <div class="col-md-6">

                                <div class="form-field">

                                    <label
                                        for="ssc_gpa"
                                        class="form-label"
                                    >

                                        SSC / Equivalent GPA

                                        <span class="required-star">
                                            *
                                        </span>

                                    </label>


                                    <div class="input-wrapper">

                                        <i
                                            class="bi bi-mortarboard
                                                   input-wrapper-icon"
                                        ></i>

                                        <input
                                            type="number"
                                            id="ssc_gpa"
                                            name="ssc_gpa"
                                            class="form-control"
                                            min="0"
                                            max="5"
                                            step="0.01"
                                            placeholder="Example: 4.50"
                                            value="<?= e($sscGpaValue); ?>"
                                            required
                                        >

                                    </div>


                                    <small class="field-help">

                                        Minimum required: 3.00

                                    </small>

                                </div>

                            </div>


                            <!-- =================================================
                                 DIPLOMA CGPA
                            ================================================== -->

                            <div class="col-md-6">

                                <div class="form-field">

                                    <label
                                        for="diploma_cgpa"
                                        class="form-label"
                                    >

                                        Diploma CGPA

                                        <span class="required-star">
                                            *
                                        </span>

                                    </label>


                                    <div class="input-wrapper">

                                        <i
                                            class="bi bi-award
                                                   input-wrapper-icon"
                                        ></i>

                                        <input
                                            type="number"
                                            id="diploma_cgpa"
                                            name="diploma_cgpa"
                                            class="form-control"
                                            min="0"
                                            max="4"
                                            step="0.01"
                                            placeholder="Example: 3.50"
                                            value="<?= e(
                                                $diplomaCgpaValue
                                            ); ?>"
                                            required
                                        >

                                    </div>


                                    <small class="field-help">

                                        Minimum required: 3.00

                                    </small>

                                </div>

                            </div>


                            <!-- =================================================
                                 PASSING YEAR
                            ================================================== -->

                            <div class="col-md-6">

                                <div class="form-field">

                                    <label
                                        for="diploma_passing_year"
                                        class="form-label"
                                    >

                                        Diploma Passing Year

                                        <span class="required-star">
                                            *
                                        </span>

                                    </label>


                                    <div class="input-wrapper">

                                       

                                        <select
                                            id="diploma_passing_year"
                                            name="diploma_passing_year"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">

                                                Select Passing Year

                                            </option>


                                            <?php foreach (
                                                $diplomaYears
                                                as $year
                                            ): ?>

                                                <option
                                                    value="<?= $year; ?>"
                                                    <?= (
                                                        (string)
                                                        $passingYearValue
                                                        === (string)
                                                        $year
                                                    )
                                                        ? 'selected'
                                                        : ''
                                                    ?>
                                                >

                                                    <?= $year; ?>

                                                </option>

                                            <?php endforeach; ?>


                                        </select>

                                    </div>

                                </div>

                            </div>


                            <!-- =================================================
                                 DIPLOMA TECHNOLOGY
                            ================================================== -->

                            <div class="col-md-6">

                                <div class="form-field">

                                    <label
                                        for="technology_id"
                                        class="form-label"
                                    >

                                        Diploma Technology

                                        <span class="required-star">
                                            *
                                        </span>

                                    </label>


                                    <div class="input-wrapper">

                                        

                                        <select
                                            id="technology_id"
                                            name="technology_id"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">

                                                Select Diploma Technology

                                            </option>


                                            <?php foreach (
                                                $technologies
                                                as $technology
                                            ): ?>

                                                <option
                                                    value="<?= (int)
                                                        $technology['id'];
                                                    ?>"
                                                    <?= (
                                                        (string)
                                                        $technologyValue
                                                        === (string)
                                                        $technology['id']
                                                    )
                                                        ? 'selected'
                                                        : ''
                                                    ?>
                                                >

                                                    <?= e(
                                                        $technology['name']
                                                    ); ?>

                                                </option>

                                            <?php endforeach; ?>


                                        </select>

                                    </div>

                                </div>

                            </div>


                        </div>


                        <!-- =================================================
                             SUBMIT
                        ================================================== -->

                        <div class="mt-4">

                            <button
                                type="submit"
                                class="btn btn-primary check-btn"
                            >

                                <i
                                    class="bi bi-search me-2"
                                ></i>

                                Check Eligibility

                            </button>

                        </div>


                    </form>


                </div>

            </div>


            <!-- =================================================
                 BASIC REQUIREMENTS
            ================================================== -->

            <div class="col-lg-4">

                <div class="eligibility-card">


                    <div class="card-heading">

                        <div class="card-heading-icon">

                            <i class="bi bi-shield-check"></i>

                        </div>

                        <div>

                            <h5>

                                Basic Requirements

                            </h5>

                            <small>

                                Minimum requirements

                            </small>

                        </div>

                    </div>


                    <!-- SSC -->

                    <div class="requirement-item">

                        <div class="requirement-icon">

                            <i class="bi bi-mortarboard"></i>

                        </div>

                        <div>

                            <div class="requirement-title">

                                SSC / Equivalent

                            </div>

                            <p class="requirement-text">

                                Minimum GPA 3.00

                            </p>

                        </div>

                    </div>


                    <!-- Diploma -->

                    <div class="requirement-item">

                        <div class="requirement-icon">

                            <i class="bi bi-book"></i>

                        </div>

                        <div>

                            <div class="requirement-title">

                                Diploma

                            </div>

                            <p class="requirement-text">

                                Minimum CGPA 3.00

                            </p>

                        </div>

                    </div>


                    <!-- Passing Year -->

                    <div class="requirement-item">

                        <div class="requirement-icon">

                            <i class="bi bi-calendar-check"></i>

                        </div>

                        <div>

                            <div class="requirement-title">

                                Passing Year

                            </div>

                            <p class="requirement-text">

                                Current year or previous year

                            </p>

                        </div>

                    </div>


                    <!-- Important -->

                    <div class="important-box">

                        <div class="important-box-title">

                            <i class="bi bi-info-circle me-1"></i>

                            Important

                        </div>

                        <p>

                            Department eligibility depends on your
                            selected diploma technology.

                        </p>

                    </div>


                </div>

            </div>


        </div>


    </div>

</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>