<?php

/**
 * Smart DUET Admission Management System
 * Admin Applications Management
 *
 * Features:
 * - Search applications
 * - Filter by department
 * - Filter by payment status
 * - Filter by application status
 * - View applicant information
 * - View uploaded documents
 * - View payment information
 * - Verify / reject payment
 * - Update application status
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';


// ======================================================
// CSRF TOKEN
// ======================================================

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] = bin2hex(
        random_bytes(32)
    );
}


// ======================================================
// FILTER VALUES
// ======================================================

$search = trim(
    $_GET['search'] ?? ''
);

$departmentId = (int) (
    $_GET['department_id'] ?? 0
);

$paymentFilter = trim(
    $_GET['payment_status'] ?? ''
);

$applicationFilter = trim(
    $_GET['application_status'] ?? ''
);


// ======================================================
// VALID FILTER VALUES
// ======================================================

$allowedPaymentFilters = [
    '',
    'pending',
    'paid',
    'failed'
];

$allowedApplicationFilters = [
    '',
    'payment_pending',
    'submitted',
    'under_review',
    'accepted',
    'rejected'
];


if (
    !in_array(
        $paymentFilter,
        $allowedPaymentFilters,
        true
    )
) {

    $paymentFilter = '';

}


if (
    !in_array(
        $applicationFilter,
        $allowedApplicationFilters,
        true
    )
) {

    $applicationFilter = '';

}


// ======================================================
// GET DEPARTMENTS
// ======================================================

$departments = [];

$stmt = $conn->prepare("
    SELECT
        id,
        code,
        name

    FROM departments

    WHERE is_active = 1

    ORDER BY code ASC
");

if ($stmt) {

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $departments[] = $row;

    }

    $stmt->close();
}


// ======================================================
// APPLICATIONS ARRAY
// ======================================================

$applications = [];


// ======================================================
// GET APPLICATIONS
// ======================================================
//
// We use dynamic conditions with prepared statements.
//
// Search:
// Application No
// Applicant Name
// Identity Number
// Email
//
// Filters:
// Department
// Payment Status
// Application Status
//
// ======================================================

$sql = "
    SELECT

        a.id AS application_id,
        a.application_no,
        a.admission_year,
        a.ssc_gpa,
        a.diploma_cgpa,
        a.diploma_passing_year,
        a.quota,
        a.status AS application_status,
        a.submitted_at,

        ap.id AS applicant_id,
        ap.full_name,
        ap.father_name,
        ap.mother_name,
        ap.date_of_birth,
        ap.gender,
        ap.identity_number,

        u.email,
        u.mobile,

        d.code AS department_code,
        d.name AS department_name,
        d.faculty,
        d.degree,

        t.name AS technology_name,

        doc.applicant_photo,
        doc.signature,
        doc.identity_document,
        doc.quota_document,

        p.id AS payment_id,
        p.payment_method,
        p.amount,
        p.transaction_id,
        p.payment_status,
        p.paid_at

    FROM applications a

    INNER JOIN applicants ap
        ON a.applicant_id = ap.id

    INNER JOIN users u
        ON ap.user_id = u.id

    INNER JOIN departments d
        ON a.department_id = d.id

    INNER JOIN diploma_technologies t
        ON a.technology_id = t.id

    LEFT JOIN documents doc
        ON a.id = doc.application_id

    LEFT JOIN payments p
        ON a.id = p.application_id

    WHERE 1 = 1
";


// ======================================================
// SEARCH CONDITION
// ======================================================

$params = [];
$types = '';


if ($search !== '') {

    $sql .= "
        AND (
            a.application_no LIKE ?
            OR ap.full_name LIKE ?
            OR ap.identity_number LIKE ?
            OR u.email LIKE ?
        )
    ";

    $searchValue =
        '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= 'ssss';
}


// ======================================================
// DEPARTMENT FILTER
// ======================================================

if ($departmentId > 0) {

    $sql .= "
        AND a.department_id = ?
    ";

    $params[] = $departmentId;

    $types .= 'i';
}


// ======================================================
// PAYMENT FILTER
// ======================================================

if ($paymentFilter !== '') {

    /*
    | If there is no payment record,
    | it will not match paid/failed/pending.
    */

    $sql .= "
        AND p.payment_status = ?
    ";

    $params[] = $paymentFilter;

    $types .= 's';
}


// ======================================================
// APPLICATION STATUS FILTER
// ======================================================

if ($applicationFilter !== '') {

    $sql .= "
        AND a.status = ?
    ";

    $params[] =
        $applicationFilter;

    $types .= 's';
}


// ======================================================
// ORDER
// ======================================================

$sql .= "
    ORDER BY a.id DESC
    LIMIT 100
";


// ======================================================
// EXECUTE QUERY
// ======================================================

$stmt = $conn->prepare($sql);


if ($stmt) {

    if (!empty($params)) {

        $stmt->bind_param(
            $types,
            ...$params
        );

    }

    $stmt->execute();

    $result =
        $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $applications[] = $row;

    }

    $stmt->close();
}


// ======================================================
// CHECK WHETHER FILTER IS USED
// ======================================================

$hasFilter =
    $search !== ''
    || $departmentId > 0
    || $paymentFilter !== ''
    || $applicationFilter !== '';


// ======================================================
// FLASH MESSAGE
// ======================================================

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
        Applications | Smart DUET Admin
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="<?= APP_URL ?>/css/admin.css"
    >
    <link
        rel="icon"
        type="image/x-icon"
        href="<?= APP_URL ?>/assets/images/duet-logo.png"
    >

</head>


<body class="admin-body">


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="admin-sidebar">


    <div class="admin-logo">

        <?php

        $logoPath =
            __DIR__ .
            '/../assets/images/duet-logo.png';

        if (file_exists($logoPath)):

        ?>

            <img
                src="<?= APP_URL ?>/assets/images/duet-logo.png"
                alt="DUET Logo"
            >

        <?php endif; ?>


        <h5>
            Smart DUET
        </h5>


        <small>
            Admin Panel
        </small>

    </div>


    <div class="admin-menu">


        <div class="admin-menu-title">
            Main
        </div>


        <a href="<?= APP_URL ?>/admin/dashboard.php">

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <div class="admin-menu-title">
            Admission
        </div>


        <a href="<?= APP_URL ?>/admin/applicants.php">

            <i class="bi bi-people"></i>

            Applicants

        </a>


        <a
            href="<?= APP_URL ?>/admin/applications.php"
            class="active"
        >

            <i class="bi bi-file-earmark-text"></i>

            Applications

        </a>


        <a href="<?= APP_URL ?>/admin/admit-cards.php">

            <i class="bi bi-person-vcard"></i>

            Admit Cards

        </a>


        <a href="<?= APP_URL ?>/admin/results.php">

            <i class="bi bi-trophy"></i>

            Results

        </a>


        <div class="admin-menu-title">
            Information
        </div>


        <a href="<?= APP_URL ?>/admin/notices.php">

            <i class="bi bi-megaphone"></i>

            Notices

        </a>


        <a href="<?= APP_URL ?>/admin/departments.php">

            <i class="bi bi-building"></i>

            Departments

        </a>


        <div class="admin-menu-title">
            Account
        </div>


        <a href="<?= APP_URL ?>/admin/profile.php">

            <i class="bi bi-person-circle"></i>

            Profile

        </a>


        <a href="<?= APP_URL ?>/admin/logout.php">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>


    </div>

</aside>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="admin-main">


    <!-- =================================================
         HERO
    ================================================== -->

    <section class="admin-hero">

        <h2>

            <i class="bi bi-file-earmark-text me-2"></i>

            Applications

        </h2>


        <p>
            Search, filter and manage admission applications.
        </p>

    </section>


    <!-- =================================================
         CONTENT
    ================================================== -->

    <section class="admin-content">


        <!-- =================================================
             FLASH MESSAGE
        ================================================== -->

        <?php if ($flash): ?>

            <div
                class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show"
                role="alert"
            >

                <?= e($flash['message']) ?>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <!-- =================================================
             SEARCH & FILTER
        ================================================== -->

        <div class="admin-card mb-4">


            <div class="admin-card-title">

                <i class="bi bi-search me-2"></i>

                Search & Filter Applications

            </div>


            <form method="GET">


                <!-- SEARCH -->

                <div class="row g-3 mb-3">


                    <div class="col-lg-6">

                        <label class="admin-form-label">

                            Search

                        </label>


                        <input
                            type="text"
                            name="search"
                            class="form-control admin-form-control"
                            placeholder="Application No, Applicant Name, NID/Birth Registration or Email"
                            value="<?= e($search) ?>"
                        >

                    </div>


                    <!-- DEPARTMENT -->

                    <div class="col-lg-3">

                        <label class="admin-form-label">

                            Department

                        </label>


                        <select
                            name="department_id"
                            class="form-select admin-form-select"
                        >

                            <option value="0">

                                All Departments

                            </option>


                            <?php foreach (
                                $departments
                                as $department
                            ): ?>

                                <option
                                    value="<?= (int) $department['id'] ?>"
                                    <?= $departmentId === (int) $department['id']
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= e(
                                        $department['code']
                                    ) ?>

                                    -
                                    <?= e(
                                        $department['name']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>


                        </select>

                    </div>


                    <!-- PAYMENT -->

                    <div class="col-lg-3">

                        <label class="admin-form-label">

                            Payment Status

                        </label>


                        <select
                            name="payment_status"
                            class="form-select admin-form-select"
                        >

                            <option value="">

                                All Payment Status

                            </option>


                            <option
                                value="paid"
                                <?= $paymentFilter === 'paid'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Paid
                            </option>


                            <option
                                value="pending"
                                <?= $paymentFilter === 'pending'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Pending
                            </option>


                            <option
                                value="failed"
                                <?= $paymentFilter === 'failed'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Failed
                            </option>

                        </select>

                    </div>


                </div>


                <!-- APPLICATION STATUS -->

                <div class="row g-3 align-items-end">


                    <div class="col-lg-6">

                        <label class="admin-form-label">

                            Application Status

                        </label>


                        <select
                            name="application_status"
                            class="form-select admin-form-select"
                        >

                            <option value="">

                                All Application Status

                            </option>


                            <option
                                value="payment_pending"
                                <?= $applicationFilter === 'payment_pending'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Payment Pending
                            </option>


                            <option
                                value="submitted"
                                <?= $applicationFilter === 'submitted'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Submitted
                            </option>


                            <option
                                value="under_review"
                                <?= $applicationFilter === 'under_review'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Under Review
                            </option>


                            <option
                                value="accepted"
                                <?= $applicationFilter === 'accepted'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Accepted
                            </option>


                            <option
                                value="rejected"
                                <?= $applicationFilter === 'rejected'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Rejected
                            </option>

                        </select>

                    </div>


                    <!-- BUTTONS -->

                    <div class="col-lg-3">

                        <button
                            type="submit"
                            class="btn btn-admin w-100"
                        >

                            <i class="bi bi-search me-1"></i>

                            Search / Filter

                        </button>

                    </div>


                    <div class="col-lg-3">

                        <a
                            href="<?= APP_URL ?>/admin/applications.php"
                            class="btn btn-outline-secondary w-100"
                        >

                            <i class="bi bi-arrow-counterclockwise me-1"></i>

                            Reset Filters

                        </a>

                    </div>


                </div>


            </form>


        </div>


        <!-- =================================================
             RESULTS
        ================================================== -->

        <?php if ($hasFilter): ?>


            <div class="admin-card">


                <div class="admin-card-title">

                    <i class="bi bi-list-ul me-2"></i>

                    Applications

                    <span class="admin-small-text">

                        (<?= count($applications) ?> found)

                    </span>

                </div>


                <?php if (empty($applications)): ?>


                    <div class="admin-empty">

                        <i class="bi bi-file-earmark-x"></i>


                        <h6>
                            No Application Found
                        </h6>


                        <p>
                            Try changing your search or filters.
                        </p>

                    </div>


                <?php else: ?>


                    <?php foreach (
                        $applications
                        as $application
                    ): ?>


                        <div class="admin-data-row">


                            <!-- =================================================
                                 BASIC APPLICATION INFO
                            ================================================== -->

                            <div class="row align-items-center g-3">


                                <!-- APPLICATION -->

                                <div class="col-lg-3">


                                    <div class="admin-small-text">
                                        Application No.
                                    </div>


                                    <div class="fw-bold admin-primary-text">

                                        <?= e(
                                            $application['application_no']
                                        ) ?>

                                    </div>


                                    <div class="admin-small-text mt-1">

                                        <?= e(
                                            $application['full_name']
                                        ) ?>

                                    </div>


                                </div>


                                <!-- DEPARTMENT -->

                                <div class="col-lg-3">


                                    <div class="admin-small-text">
                                        Department
                                    </div>


                                    <div class="fw-semibold">

                                        <?= e(
                                            $application['department_code']
                                        ) ?>

                                        -
                                        <?= e(
                                            $application['department_name']
                                        ) ?>

                                    </div>


                                    <div class="admin-small-text">

                                        <?= e(
                                            $application['technology_name']
                                        ) ?>

                                    </div>


                                </div>


                                <!-- PAYMENT -->

                                <div class="col-lg-2">


                                    <div class="admin-small-text">
                                        Payment
                                    </div>


                                    <?php

                                    $paymentStatus =
                                        $application['payment_status']
                                        ?? 'pending';

                                    ?>


                                    <?php if (
                                        $paymentStatus === 'paid'
                                    ): ?>


                                        <span class="badge text-bg-success">

                                            <i class="bi bi-check-circle me-1"></i>

                                            Paid

                                        </span>


                                    <?php elseif (
                                        $paymentStatus === 'failed'
                                    ): ?>


                                        <span class="badge text-bg-danger">

                                            <i class="bi bi-x-circle me-1"></i>

                                            Failed

                                        </span>


                                    <?php else: ?>


                                        <span class="badge text-bg-warning">

                                            <i class="bi bi-clock me-1"></i>

                                            Pending

                                        </span>


                                    <?php endif; ?>


                                </div>


                                <!-- APPLICATION STATUS -->

                                <div class="col-lg-2">


                                    <div class="admin-small-text">
                                        Application Status
                                    </div>


                                    <?php

                                    $status =
                                        $application['application_status'];

                                    $statusClass =
                                        'bg-secondary';


                                    if (
                                        $status === 'submitted'
                                    ) {

                                        $statusClass =
                                            'bg-primary';

                                    } elseif (
                                        $status === 'payment_pending'
                                    ) {

                                        $statusClass =
                                            'bg-warning text-dark';

                                    } elseif (
                                        $status === 'under_review'
                                    ) {

                                        $statusClass =
                                            'bg-info text-dark';

                                    } elseif (
                                        $status === 'accepted'
                                    ) {

                                        $statusClass =
                                            'bg-success';

                                    } elseif (
                                        $status === 'rejected'
                                    ) {

                                        $statusClass =
                                            'bg-danger';

                                    }

                                    ?>


                                    <span
                                        class="badge <?= $statusClass ?>"
                                    >

                                        <?= e(
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $status
                                                )
                                            )
                                        ) ?>

                                    </span>

                                </div>


                                <!-- DETAILS -->

                                <div class="col-lg-2 text-lg-end">


                                    <button
                                        type="button"
                                        class="btn btn-outline-success btn-sm"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#application<?= (int) $application['application_id'] ?>"
                                    >

                                        <i class="bi bi-eye me-1"></i>

                                        Details

                                    </button>

                                </div>


                            </div>


                            <!-- =================================================
                                 DETAILS
                            ================================================== -->

                            <div
                                class="collapse"
                                id="application<?= (int) $application['application_id'] ?>"
                            >


                                <div class="admin-result-form mt-3">


                                    <!-- APPLICANT -->

                                    <h6 class="fw-bold mb-3">

                                        <i class="bi bi-person me-2"></i>

                                        Applicant Information

                                    </h6>


                                    <div class="row g-3 mb-4">


                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Full Name
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['full_name']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Father's Name
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['father_name']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Mother's Name
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['mother_name']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Date of Birth
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['date_of_birth']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Gender
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['gender']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Identity Number
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['identity_number']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-6">

                                            <div class="admin-small-text">
                                                Email
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['email']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-6">

                                            <div class="admin-small-text">
                                                Mobile
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['mobile']
                                                ) ?>
                                            </strong>

                                        </div>

                                    </div>


                                    <!-- ACADEMIC -->

                                    <h6 class="fw-bold mb-3">

                                        <i class="bi bi-mortarboard me-2"></i>

                                        Academic & Application Information

                                    </h6>


                                    <div class="row g-3 mb-4">


                                        <div class="col-md-3">

                                            <div class="admin-small-text">
                                                SSC GPA
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['ssc_gpa']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-3">

                                            <div class="admin-small-text">
                                                Diploma CGPA
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['diploma_cgpa']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-3">

                                            <div class="admin-small-text">
                                                Passing Year
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['diploma_passing_year']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-3">

                                            <div class="admin-small-text">
                                                Quota
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['quota']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-6">

                                            <div class="admin-small-text">
                                                Department
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['department_name']
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div class="col-md-6">

                                            <div class="admin-small-text">
                                                Technology
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $application['technology_name']
                                                ) ?>
                                            </strong>

                                        </div>

                                    </div>


                                    <!-- =================================================
                                         PAYMENT VERIFICATION
                                    ================================================== -->

                                    <div class="border rounded p-3 mb-4">


                                        <h6 class="fw-bold mb-3">

                                            <i class="bi bi-credit-card me-2"></i>

                                            Payment Verification

                                        </h6>


                                        <?php if (
                                            $application['payment_id']
                                        ): ?>


                                            <div class="row g-3">


                                                <div class="col-md-4">

                                                    <div class="admin-small-text">
                                                        Payment Method
                                                    </div>

                                                    <strong>
                                                        <?= e(
                                                            $application['payment_method']
                                                        ) ?>
                                                    </strong>

                                                </div>


                                                <div class="col-md-4">

                                                    <div class="admin-small-text">
                                                        Amount
                                                    </div>

                                                    <strong>
                                                        ৳<?= e(
                                                            number_format(
                                                                (float) $application['amount'],
                                                                2
                                                            )
                                                        ) ?>
                                                    </strong>

                                                </div>


                                                <div class="col-md-4">

                                                    <div class="admin-small-text">
                                                        Transaction ID
                                                    </div>

                                                    <strong class="text-break">

                                                        <?= e(
                                                            $application['transaction_id']
                                                        ) ?>

                                                    </strong>

                                                </div>


                                            </div>


                                            <hr>


                                            <?php if (
                                                $paymentStatus === 'pending'
                                            ): ?>

                                                <div class="alert alert-warning">

                                                    <i class="bi bi-hourglass-split me-1"></i>

                                                    Payment is waiting for admin verification.

                                                </div>


                                            <?php elseif (
                                                $paymentStatus === 'paid'
                                            ): ?>

                                                <div class="alert alert-success">

                                                    <i class="bi bi-check-circle me-1"></i>

                                                    Payment verified successfully.

                                                    <?php if (
                                                        $application['paid_at']
                                                    ): ?>

                                                        <br>

                                                        <small>

                                                            Verified:
                                                            <?= e(
                                                                date(
                                                                    'd M Y, h:i A',
                                                                    strtotime(
                                                                        $application['paid_at']
                                                                    )
                                                                )
                                                            ) ?>

                                                        </small>

                                                    <?php endif; ?>

                                                </div>


                                            <?php else: ?>

                                                <div class="alert alert-danger">

                                                    <i class="bi bi-x-circle me-1"></i>

                                                    Payment marked as failed.

                                                </div>

                                            <?php endif; ?>


                                            <!-- PAYMENT ACTION -->

                                            <form
                                                method="POST"
                                                action="<?= APP_URL ?>/admin/actions/payment_action.php"
                                                class="mt-3"
                                            >


                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e($_SESSION['csrf_token']) ?>"
                                                >


                                                <!-- IMPORTANT:
                                                     payment_action.php expects payment_id -->

                                                <input
                                                    type="hidden"
                                                    name="payment_id"
                                                    value="<?= (int) $application['payment_id'] ?>"
                                                >


                                                <div class="row g-3 align-items-end">


                                                    <div class="col-md-8">

                                                        <label class="admin-form-label">

                                                            Payment Status

                                                        </label>


                                                        <select
                                                            name="payment_status"
                                                            class="form-select"
                                                            required
                                                        >

                                                            <option
                                                                value="pending"
                                                                <?= $paymentStatus === 'pending'
                                                                    ? 'selected'
                                                                    : '' ?>
                                                            >
                                                                Pending
                                                            </option>


                                                            <option
                                                                value="paid"
                                                                <?= $paymentStatus === 'paid'
                                                                    ? 'selected'
                                                                    : '' ?>
                                                            >
                                                                Paid / Verified
                                                            </option>


                                                            <option
                                                                value="failed"
                                                                <?= $paymentStatus === 'failed'
                                                                    ? 'selected'
                                                                    : '' ?>
                                                            >
                                                                Failed
                                                            </option>

                                                        </select>

                                                    </div>


                                                    <div class="col-md-4">

                                                        <button
                                                            type="submit"
                                                            class="btn btn-admin w-100"
                                                        >

                                                            <i class="bi bi-shield-check me-1"></i>

                                                            Update Payment

                                                        </button>

                                                    </div>


                                                </div>


                                            </form>


                                        <?php else: ?>


                                            <div class="alert alert-danger mb-0">

                                                <i class="bi bi-exclamation-triangle me-1"></i>

                                                No payment record found for this application.

                                            </div>

                                        <?php endif; ?>


                                    </div>


                                    <!-- =================================================
                                         DOCUMENTS
                                    ================================================== -->

                                    <h6 class="fw-bold mb-3">

                                        <i class="bi bi-file-earmark-arrow-down me-2"></i>

                                        Documents

                                    </h6>


                                    <div class="row g-2 mb-4">


                                        <?php if (
                                            !empty(
                                                $application['applicant_photo']
                                            )
                                        ): ?>

                                            <div class="col-md-3">

                                                <a
                                                    href="<?= APP_URL ?>/assets/uploads/photos/<?= rawurlencode($application['applicant_photo']) ?>"
                                                    target="_blank"
                                                    class="btn btn-outline-secondary w-100"
                                                >

                                                    <i class="bi bi-image me-1"></i>

                                                    Photo

                                                </a>

                                            </div>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty(
                                                $application['signature']
                                            )
                                        ): ?>

                                            <div class="col-md-3">

                                                <a
                                                    href="<?= APP_URL ?>/assets/uploads/signatures/<?= rawurlencode($application['signature']) ?>"
                                                    target="_blank"
                                                    class="btn btn-outline-secondary w-100"
                                                >

                                                    <i class="bi bi-pen me-1"></i>

                                                    Signature

                                                </a>

                                            </div>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty(
                                                $application['identity_document']
                                            )
                                        ): ?>

                                            <div class="col-md-3">

                                                <a
                                                    href="<?= APP_URL ?>/assets/uploads/documents/<?= rawurlencode($application['identity_document']) ?>"
                                                    target="_blank"
                                                    class="btn btn-outline-secondary w-100"
                                                >

                                                    <i class="bi bi-card-text me-1"></i>

                                                    Identity Document

                                                </a>

                                            </div>

                                        <?php endif; ?>


                                        <?php if (
                                            !empty(
                                                $application['quota_document']
                                            )
                                        ): ?>

                                            <div class="col-md-3">

                                                <a
                                                    href="<?= APP_URL ?>/assets/uploads/quota/<?= rawurlencode($application['quota_document']) ?>"
                                                    target="_blank"
                                                    class="btn btn-outline-secondary w-100"
                                                >

                                                    <i class="bi bi-file-pdf me-1"></i>

                                                    Quota Document

                                                </a>

                                            </div>

                                        <?php endif; ?>


                                    </div>


                                    <!-- =================================================
                                         APPLICATION STATUS
                                    ================================================== -->

                                    <h6 class="fw-bold mb-3">

                                        <i class="bi bi-arrow-repeat me-2"></i>

                                        Application Status

                                    </h6>


                                    <form
                                        method="POST"
                                        action="<?= APP_URL ?>/admin/actions/application_action.php"
                                    >


                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($_SESSION['csrf_token']) ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="application_id"
                                            value="<?= (int) $application['application_id'] ?>"
                                        >


                                        <div class="row g-3 align-items-end">


                                            <div class="col-md-8">

                                                <label class="admin-form-label">

                                                    Application Status

                                                </label>


                                                <select
                                                    name="status"
                                                    class="form-select"
                                                    required
                                                >

                                                    <option
                                                        value="payment_pending"
                                                        <?= $status === 'payment_pending'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        Payment Pending
                                                    </option>


                                                    <option
                                                        value="submitted"
                                                        <?= $status === 'submitted'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        Submitted
                                                    </option>


                                                    <option
                                                        value="under_review"
                                                        <?= $status === 'under_review'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        Under Review
                                                    </option>


                                                    <option
                                                        value="accepted"
                                                        <?= $status === 'accepted'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        Accepted
                                                    </option>


                                                    <option
                                                        value="rejected"
                                                        <?= $status === 'rejected'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        Rejected
                                                    </option>

                                                </select>

                                            </div>


                                            <div class="col-md-4">

                                                <button
                                                    type="submit"
                                                    class="btn btn-admin w-100"
                                                >

                                                    <i class="bi bi-check-circle me-1"></i>

                                                    Update Status

                                                </button>

                                            </div>


                                        </div>


                                    </form>


                                </div>


                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php endif; ?>


            </div>


        <?php else: ?>


            <div class="admin-card">


                <div class="admin-empty">

                    <i class="bi bi-file-earmark-text"></i>


                    <h5>
                        Application Management
                    </h5>


                    <p class="mb-0">

                        Search or filter applications
                        to review applicant information,
                        payment and application status.

                    </p>

                </div>


            </div>

        <?php endif; ?>


    </section>

</main>


<!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>