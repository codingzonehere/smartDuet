<?php

/**
 * Smart DUET Admission Management System
 * Admin Applicants Management
 *
 * Purpose:
 * - Search applicants
 * - View applicant information
 * - View account status
 * - Activate / Block applicant account
 */

// ======================================================
// CENTRALIZED ADMIN AUTHENTICATION
// ======================================================

require_once __DIR__ . '/auth.php';


// ======================================================
// HELPER FUNCTIONS
// ======================================================

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
// SEARCH
// ======================================================

$search = trim($_GET['search'] ?? '');

$applicants = [];


// ------------------------------------------------------
// Search only when user enters something
// ------------------------------------------------------

if ($search !== '') {

    $sql = "
        SELECT
            ap.id,
            ap.full_name,
            ap.father_name,
            ap.mother_name,
            ap.date_of_birth,
            ap.gender,
            ap.identity_number,

            u.id AS user_id,
            u.email,
            u.mobile,
            u.status AS account_status,
            u.created_at

        FROM applicants ap

        INNER JOIN users u
            ON ap.user_id = u.id

        WHERE
            ap.full_name LIKE ?
            OR ap.identity_number LIKE ?
            OR u.email LIKE ?
            OR u.mobile LIKE ?

        ORDER BY ap.id DESC

        LIMIT 50
    ";


    $stmt = $conn->prepare($sql);


    if ($stmt) {

        $searchValue = '%' . $search . '%';


        $stmt->bind_param(
            'ssss',
            $searchValue,
            $searchValue,
            $searchValue,
            $searchValue
        );


        $stmt->execute();


        $result = $stmt->get_result();


        while ($row = $result->fetch_assoc()) {

            $applicants[] = $row;
        }


        $stmt->close();
    }
}


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
        Applicants | Smart DUET Admin
    </title>


    <!-- ==================================================
         BOOTSTRAP
    ================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- ==================================================
         BOOTSTRAP ICONS
    ================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- ==================================================
         ADMIN CSS
    ================================================== -->

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


<!-- =========================================================
     ADMIN SIDEBAR
========================================================= -->

<aside class="admin-sidebar">


    <!-- Logo -->

    <div class="admin-logo">

        <?php

        $logoPath = __DIR__ . '/../assets/images/duet-logo.png';

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


    <!-- Menu -->

    <div class="admin-menu">


        <!-- Main -->

        <div class="admin-menu-title">
            Main
        </div>


        <a href="<?= APP_URL ?>/admin/dashboard.php">

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <!-- Admission -->

        <div class="admin-menu-title">
            Admission
        </div>


        <a
            href="<?= APP_URL ?>/admin/applicants.php"
            class="active"
        >

            <i class="bi bi-people"></i>

            Applicants

        </a>


        <a href="<?= APP_URL ?>/admin/applications.php">

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


        <!-- Information -->

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


        <!-- Account -->

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


<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<main class="admin-main">


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="admin-hero">

        <h2>

            <i class="bi bi-people me-2"></i>

            Applicants

        </h2>


        <p>
            View and manage applicant accounts.
        </p>

    </section>


    <!-- =====================================================
         CONTENT
    ====================================================== -->

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
             SEARCH CARD
        ================================================== -->

        <div class="admin-card mb-4">


            <div class="admin-card-title">

                <i class="bi bi-search me-2"></i>

                Search Applicant

            </div>


            <form method="GET">


                <div class="row g-2">


                    <div class="col-md-10">

                        <input
                            type="text"
                            name="search"
                            class="form-control admin-form-control"
                            placeholder="Name, NID/Birth Registration, Email or Mobile"
                            value="<?= e($search) ?>"
                        >

                    </div>


                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-admin w-100"
                        >

                            <i class="bi bi-search me-1"></i>

                            Search

                        </button>

                    </div>


                </div>


            </form>

        </div>


        <!-- =================================================
             SEARCH RESULTS
        ================================================== -->

        <?php if ($search !== ''): ?>


            <div class="admin-card">


                <div class="admin-card-title">


                    Applicants


                    <span class="admin-small-text">

                        (<?= count($applicants) ?> found)

                    </span>


                </div>


                <!-- =================================================
                     NO RESULT
                ================================================== -->

                <?php if (empty($applicants)): ?>


                    <div class="admin-empty">


                        <i class="bi bi-person-x"></i>


                        <h6>
                            No Applicant Found
                        </h6>


                        <p>
                            Try another name, email, mobile or ID.
                        </p>


                    </div>


                <?php else: ?>


                    <!-- =================================================
                         APPLICANT LIST
                    ================================================== -->

                    <?php foreach ($applicants as $applicant): ?>


                        <div class="admin-data-row">


                            <div class="row align-items-center g-3">


                                <!-- =================================
                                     BASIC INFORMATION
                                ================================== -->

                                <div class="col-lg-4">


                                    <div class="fw-bold admin-primary-text">

                                        <?= e($applicant['full_name']) ?>

                                    </div>


                                    <div class="admin-small-text mt-1">

                                        ID:
                                        <?= e($applicant['identity_number']) ?>

                                    </div>


                                </div>


                                <!-- =================================
                                     CONTACT
                                ================================== -->

                                <div class="col-lg-3">


                                    <div class="admin-small-text">
                                        Contact
                                    </div>


                                    <div>

                                        <?= e($applicant['mobile']) ?>

                                    </div>


                                    <div class="admin-small-text">

                                        <?= e($applicant['email']) ?>

                                    </div>


                                </div>


                                <!-- =================================
                                     STATUS
                                ================================== -->

                                <div class="col-lg-2">


                                    <div class="admin-small-text">
                                        Account Status
                                    </div>


                                    <?php if (
                                        $applicant['account_status'] === 'active'
                                    ): ?>


                                        <span class="badge text-bg-success">

                                            Active

                                        </span>


                                    <?php else: ?>


                                        <span class="badge text-bg-danger">

                                            Blocked

                                        </span>


                                    <?php endif; ?>


                                </div>


                                <!-- =================================
                                     VIEW BUTTON
                                ================================== -->

                                <div class="col-lg-3 text-lg-end">


                                    <button
                                        type="button"
                                        class="btn btn-outline-success btn-sm"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#applicant<?= (int) $applicant['id'] ?>"
                                    >

                                        <i class="bi bi-eye me-1"></i>

                                        View Details

                                    </button>


                                </div>


                            </div>


                            <!-- =================================================
                                 APPLICANT DETAILS
                            ================================================== -->

                            <div
                                class="collapse"
                                id="applicant<?= (int) $applicant['id'] ?>"
                            >


                                <div class="admin-result-form mt-3">


                                    <h6 class="fw-bold mb-3">


                                        <i class="bi bi-person-vcard me-2"></i>


                                        Applicant Information


                                    </h6>


                                    <div class="row g-3 mb-4">


                                        <!-- Full Name -->

                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Full Name
                                            </div>

                                            <strong>
                                                <?= e($applicant['full_name']) ?>
                                            </strong>

                                        </div>


                                        <!-- Father's Name -->

                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Father's Name
                                            </div>

                                            <strong>
                                                <?= e($applicant['father_name']) ?>
                                            </strong>

                                        </div>


                                        <!-- Mother's Name -->

                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Mother's Name
                                            </div>

                                            <strong>
                                                <?= e($applicant['mother_name']) ?>
                                            </strong>

                                        </div>


                                        <!-- Date of Birth -->

                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Date of Birth
                                            </div>

                                            <strong>
                                                <?= e($applicant['date_of_birth']) ?>
                                            </strong>

                                        </div>


                                        <!-- Gender -->

                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Gender
                                            </div>

                                            <strong>
                                                <?= e($applicant['gender']) ?>
                                            </strong>

                                        </div>


                                        <!-- Identity -->

                                        <div class="col-md-4">

                                            <div class="admin-small-text">
                                                Identity Number
                                            </div>

                                            <strong>
                                                <?= e($applicant['identity_number']) ?>
                                            </strong>

                                        </div>


                                        <!-- Email -->

                                        <div class="col-md-6">

                                            <div class="admin-small-text">
                                                Email
                                            </div>

                                            <strong>
                                                <?= e($applicant['email']) ?>
                                            </strong>

                                        </div>


                                        <!-- Mobile -->

                                        <div class="col-md-6">

                                            <div class="admin-small-text">
                                                Mobile
                                            </div>

                                            <strong>
                                                <?= e($applicant['mobile']) ?>
                                            </strong>

                                        </div>


                                    </div>


                                    <!-- =================================================
                                         ACCOUNT STATUS
                                    ================================================== -->

                                    <h6 class="fw-bold mb-3">


                                        <i class="bi bi-shield-check me-2"></i>


                                        Account Status


                                    </h6>


                                    <form
                                        method="POST"
                                        action="<?= APP_URL ?>/admin/actions/applicant_action.php"
                                    >


                                        <!-- CSRF Token -->

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($_SESSION['csrf_token']) ?>"
                                        >


                                        <!-- User ID -->

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?= (int) $applicant['user_id'] ?>"
                                        >


                                        <div class="row g-3 align-items-end">


                                            <!-- Status -->

                                            <div class="col-md-8">


                                                <label class="admin-form-label mb-1">

                                                    Account Status

                                                </label>


                                                <select
                                                    name="status"
                                                    class="form-select"
                                                    required
                                                >


                                                    <option
                                                        value="active"
                                                        <?= $applicant['account_status'] === 'active'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >

                                                        Active

                                                    </option>


                                                    <option
                                                        value="blocked"
                                                        <?= $applicant['account_status'] === 'blocked'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >

                                                        Blocked

                                                    </option>


                                                </select>


                                            </div>


                                            <!-- Update -->

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


            <!-- =================================================
                 INITIAL STATE
            ================================================== -->

            <div class="admin-card">


                <div class="admin-empty">


                    <i class="bi bi-people"></i>


                    <h5>
                        Applicant Management
                    </h5>


                    <p class="mb-0">
                        Search an applicant to view their information.
                    </p>


                </div>


            </div>


        <?php endif; ?>


    </section>


</main>


<!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>