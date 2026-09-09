<?php

// ======================================================
// Smart DUET Admission Management System
// Admin Dashboard
// ======================================================

// Centralized Admin Authentication
require_once __DIR__ . '/auth.php';


// Database + helper functions
require_once __DIR__ . '/../includes/functions.php';


// ------------------------------------------------------
// Get Current Admin Information
// ------------------------------------------------------

$adminId = (int) $_SESSION['admin_id'];

$stmt = $conn->prepare(
    "SELECT full_name, email
     FROM admins
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param('i', $adminId);
$stmt->execute();

$result = $stmt->get_result();

$admin = $result->fetch_assoc();

$stmt->close();


// ------------------------------------------------------
// Dashboard Statistics
// ------------------------------------------------------

// Total Applicants
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applicants"
);

$totalApplicants = (int) $result->fetch_assoc()['total'];


// Total Applications
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications"
);

$totalApplications = (int) $result->fetch_assoc()['total'];


// Pending Applications
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE status IN ('submitted', 'under_review')"
);

$pendingApplications = (int) $result->fetch_assoc()['total'];


// Accepted Applications
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE status = 'accepted'"
);

$acceptedApplications = (int) $result->fetch_assoc()['total'];


// Published Admit Cards
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM admit_cards
     WHERE status = 'published'"
);

$publishedAdmitCards = (int) $result->fetch_assoc()['total'];


// Published Results
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM results
     WHERE result_status != 'pending'"
);

$publishedResults = (int) $result->fetch_assoc()['total'];


// Published Notices
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM notices
     WHERE status = 'published'"
);

$publishedNotices = (int) $result->fetch_assoc()['total'];


// Active Departments
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM departments
     WHERE is_active = 1"
);

$activeDepartments = (int) $result->fetch_assoc()['total'];


// ------------------------------------------------------
// Recent Applications
// ------------------------------------------------------

$recentApplications = [];

$stmt = $conn->prepare(
    "SELECT
        a.application_no,
        ap.full_name,
        d.name AS department_name,
        a.status,
        a.submitted_at
     FROM applications a
     INNER JOIN applicants ap
        ON a.applicant_id = ap.id
     LEFT JOIN departments d
        ON a.department_id = d.id
     ORDER BY a.id DESC
     LIMIT 5"
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $recentApplications[] = $row;
}

$stmt->close();


// ------------------------------------------------------
// Page Title
// ------------------------------------------------------

$pageTitle = 'Admin Dashboard';

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
        <?php echo e($pageTitle); ?> - Smart DUET
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
        href="<?php echo APP_URL; ?>/css/admin.css"
    >
    <link
        rel="icon"
        type="image/x-icon"
        href="<?= APP_URL ?>/assets/images/duet-logo.png"
    >

</head>


<body class="admin-body">


<!-- ==================================================
     SIDEBAR
================================================== -->

<aside class="admin-sidebar">


    <!-- Logo -->

<div class="admin-logo">

        
            <img src="/smart-duet/assets/images/duet-logo.png" alt="DUET Logo">

        

        <h5>
            Smart DUET
        </h5>


        <small>
            Admin Panel
        </small>

    </div>

    <!-- Menu -->

    <div class="admin-menu">


        <!-- Main -->

        <div class="admin-menu-title">
            MAIN
        </div>


        <a
            href="<?php echo APP_URL; ?>/admin/dashboard.php"
            class="admin-menu-item active"
        >

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <!-- Admission -->

        <div class="admin-menu-title">
            ADMISSION
        </div>


        <a
            href="<?php echo APP_URL; ?>/admin/applicants.php"
            class="admin-menu-item"
        >

            <i class="bi bi-people"></i>

            Applicants

        </a>


        <a
            href="<?php echo APP_URL; ?>/admin/applications.php"
            class="admin-menu-item"
        >

            <i class="bi bi-file-earmark-text"></i>

            Applications

        </a>


        <a
            href="<?php echo APP_URL; ?>/admin/admit-cards.php"
            class="admin-menu-item"
        >

            <i class="bi bi-person-vcard"></i>

            Admit Cards

        </a>


        <a
            href="<?php echo APP_URL; ?>/admin/results.php"
            class="admin-menu-item"
        >

            <i class="bi bi-award"></i>

            Results

        </a>


        <!-- Management -->

        <div class="admin-menu-title">
            MANAGEMENT
        </div>


        <a
            href="<?php echo APP_URL; ?>/admin/notices.php"
            class="admin-menu-item"
        >

            <i class="bi bi-megaphone"></i>

            Notices

        </a>


        <a
            href="<?php echo APP_URL; ?>/admin/departments.php"
            class="admin-menu-item"
        >

            <i class="bi bi-building"></i>

            Departments

        </a>


        <!-- Account -->

        <div class="admin-menu-title">
            ACCOUNT
        </div>


        <a
            href="<?php echo APP_URL; ?>/admin/profile.php"
            class="admin-menu-item"
        >

            <i class="bi bi-person-circle"></i>

            Profile

        </a>


        <a
            href="<?php echo APP_URL; ?>/admin/logout.php"
            class="admin-menu-item"
        >

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>


    </div>

</aside>


<!-- ==================================================
     MAIN CONTENT
================================================== -->

<main class="admin-main">


    <!-- ==============================================
         HERO
    =============================================== -->

    <section class="admin-hero">


        <div>

            <h1>
                Admin Dashboard
            </h1>

            <p>
                Welcome back,
                <strong>
                    <?php echo e($admin['full_name']); ?>
                </strong>.
                Manage DUET admission activities from here.
            </p>

        </div>


    </section>


    <!-- ==============================================
         CONTENT
    =============================================== -->

    <div class="admin-content">


        <!-- ==========================================
             STATISTICS
        =========================================== -->

        <div class="row g-4 mb-4">


            <!-- Applicants -->

            <div class="col-xl-3 col-md-6">

                <div class="admin-card stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="admin-small-text">
                                Total Applicants
                            </div>

                            <h3 class="mt-2 mb-0">
                                <?php echo $totalApplicants; ?>
                            </h3>

                        </div>

                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
                        </div>

                    </div>

                </div>

            </div>


            <!-- Applications -->

            <div class="col-xl-3 col-md-6">

                <div class="admin-card stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="admin-small-text">
                                Total Applications
                            </div>

                            <h3 class="mt-2 mb-0">
                                <?php echo $totalApplications; ?>
                            </h3>

                        </div>

                        <div class="stat-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>

                    </div>

                </div>

            </div>


            <!-- Pending -->

            <div class="col-xl-3 col-md-6">

                <div class="admin-card stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="admin-small-text">
                                Pending Applications
                            </div>

                            <h3 class="mt-2 mb-0">
                                <?php echo $pendingApplications; ?>
                            </h3>

                        </div>

                        <div class="stat-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>

                    </div>

                </div>

            </div>


            <!-- Accepted -->

            <div class="col-xl-3 col-md-6">

                <div class="admin-card stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="admin-small-text">
                                Accepted
                            </div>

                            <h3 class="mt-2 mb-0">
                                <?php echo $acceptedApplications; ?>
                            </h3>

                        </div>

                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>

                    </div>

                </div>

            </div>


        </div>


        <!-- ==========================================
             SECONDARY STATISTICS
        =========================================== -->

        <div class="row g-4 mb-4">


            <!-- Admit Cards -->

            <div class="col-lg-4 col-md-6">

                <div class="admin-card">

                    <div class="admin-card-title">

                        <i class="bi bi-person-vcard"></i>

                        Published Admit Cards

                    </div>

                    <h3 class="mt-3 mb-0">
                        <?php echo $publishedAdmitCards; ?>
                    </h3>

                    <div class="admin-small-text mt-2">
                        Currently published admit cards
                    </div>

                </div>

            </div>


            <!-- Results -->

            <div class="col-lg-4 col-md-6">

                <div class="admin-card">

                    <div class="admin-card-title">

                        <i class="bi bi-award"></i>

                        Published Results

                    </div>

                    <h3 class="mt-3 mb-0">
                        <?php echo $publishedResults; ?>
                    </h3>

                    <div class="admin-small-text mt-2">
                        Applications with published result status
                    </div>

                </div>

            </div>


            <!-- Notices -->

            <div class="col-lg-4 col-md-6">

                <div class="admin-card">

                    <div class="admin-card-title">

                        <i class="bi bi-megaphone"></i>

                        Published Notices

                    </div>

                    <h3 class="mt-3 mb-0">
                        <?php echo $publishedNotices; ?>
                    </h3>

                    <div class="admin-small-text mt-2">
                        Notices visible to applicants
                    </div>

                </div>

            </div>


        </div>


        <!-- ==========================================
             RECENT APPLICATIONS
        =========================================== -->

        <div class="admin-card">


            <div class="d-flex justify-content-between align-items-center mb-3">

                <div class="admin-card-title mb-0">

                    <i class="bi bi-clock-history"></i>

                    Recent Applications

                </div>


                <a
                    href="<?php echo APP_URL; ?>/admin/applications.php"
                    class="btn btn-sm btn-admin"
                >

                    View All

                    <i class="bi bi-arrow-right ms-1"></i>

                </a>

            </div>


            <?php if (empty($recentApplications)): ?>


                <div class="admin-empty">

                    <i class="bi bi-inbox"></i>

                    <p class="mb-0 mt-2">
                        No applications found.
                    </p>

                </div>


            <?php else: ?>


                <div class="table-responsive">

                    <table class="table align-middle mb-0">


                        <thead>

                            <tr>

                                <th>
                                    Application No.
                                </th>

                                <th>
                                    Applicant
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Submitted
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($recentApplications as $application): ?>


                            <tr>


                                <td>

                                    <strong>
                                        <?php
                                        echo e(
                                            $application['application_no']
                                        );
                                        ?>
                                    </strong>

                                </td>


                                <td>

                                    <?php
                                    echo e(
                                        $application['full_name']
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo e(
                                        $application['department_name']
                                        ?? 'Not selected'
                                    );
                                    ?>

                                </td>


                                <td>


                                    <?php

                                    $status = $application['status'];

                                    $statusClass = 'bg-secondary';

                                    $statusText = ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $status
                                        )
                                    );


                                    if ($status === 'submitted') {

                                        $statusClass = 'bg-primary';

                                    } elseif ($status === 'under_review') {

                                        $statusClass = 'bg-warning text-dark';

                                    } elseif ($status === 'accepted') {

                                        $statusClass = 'bg-success';

                                    } elseif ($status === 'rejected') {

                                        $statusClass = 'bg-danger';

                                    }

                                    ?>


                                    <span
                                        class="badge <?php echo $statusClass; ?>"
                                    >

                                        <?php
                                        echo e($statusText);
                                        ?>

                                    </span>


                                </td>


                                <td>

                                    <?php

                                    if (!empty(
                                        $application['submitted_at']
                                    )) {

                                        echo e(
                                            date(
                                                'd M Y',
                                                strtotime(
                                                    $application['submitted_at']
                                                )
                                            )
                                        );

                                    } else {

                                        echo '—';
                                    }

                                    ?>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php endif; ?>


        </div>


        <!-- ==========================================
             QUICK ACTIONS
        =========================================== -->

        <div class="admin-card mt-4">


            <div class="admin-card-title">

                <i class="bi bi-lightning-charge"></i>

                Quick Actions

            </div>


            <div class="row g-3 mt-1">


                <div class="col-md-3 col-sm-6">

                    <a
                        href="<?php echo APP_URL; ?>/admin/applications.php"
                        class="btn btn-outline-success w-100 py-3"
                    >

                        <i class="bi bi-file-earmark-text d-block fs-4 mb-1"></i>

                        Applications

                    </a>

                </div>


                <div class="col-md-3 col-sm-6">

                    <a
                        href="<?php echo APP_URL; ?>/admin/admit-cards.php"
                        class="btn btn-outline-success w-100 py-3"
                    >

                        <i class="bi bi-person-vcard d-block fs-4 mb-1"></i>

                        Admit Cards

                    </a>

                </div>


                <div class="col-md-3 col-sm-6">

                    <a
                        href="<?php echo APP_URL; ?>/admin/results.php"
                        class="btn btn-outline-success w-100 py-3"
                    >

                        <i class="bi bi-award d-block fs-4 mb-1"></i>

                        Results

                    </a>

                </div>


                <div class="col-md-3 col-sm-6">

                    <a
                        href="<?php echo APP_URL; ?>/admin/notices.php"
                        class="btn btn-outline-success w-100 py-3"
                    >

                        <i class="bi bi-megaphone d-block fs-4 mb-1"></i>

                        Notices

                    </a>

                </div>


            </div>

        </div>


    </div>

</main>


</body>

</html>