<?php

// ======================================================
// Smart DUET Admission Management System
// Admin Profile
// ======================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';


// ------------------------------------------------------
// Admin Authentication
// ------------------------------------------------------

if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


// ------------------------------------------------------
// Get Admin ID
// ------------------------------------------------------

$adminId = (int) $_SESSION['admin_id'];


// ------------------------------------------------------
// Get Admin Information
// ------------------------------------------------------

$stmt = $conn->prepare(
    "SELECT
        id,
        full_name,
        email,
        mobile,
        status,
        created_at
     FROM admins
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param('i', $adminId);
$stmt->execute();

$result = $stmt->get_result();


// Admin account not found
if ($result->num_rows !== 1) {

    session_unset();
    session_destroy();

    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}

$admin = $result->fetch_assoc();

$stmt->close();


// ------------------------------------------------------
// CSRF Token
// ------------------------------------------------------

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] = bin2hex(
        random_bytes(32)
    );
}

$csrfToken = $_SESSION['csrf_token'];


// ------------------------------------------------------
// Flash Message
// ------------------------------------------------------

$flash = getFlashMessage();


// ------------------------------------------------------
// Page Title
// ------------------------------------------------------

$pageTitle = 'Admin Profile';

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


        <a href="<?= APP_URL ?>/admin/profile.php"
            class="active"
        >

            <i class="bi bi-person-circle"></i>

            Profile

        </a>


        <a href="<?= APP_URL ?>/admin/logout.php">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>


    </div>

</aside>


<!-- ==================================================
     MAIN CONTENT
================================================== -->

<main class="admin-main">


    <!-- Admin Hero -->

    <section class="admin-hero">

        <div>

            <h1>
                Admin Profile
            </h1>

            <p>
                Manage your administrator account information and password.
            </p>

        </div>

    </section>


    <!-- Content -->

    <div class="admin-content">


        <!-- Flash Message -->

        <?php if ($flash): ?>

            <div
                class="alert alert-<?php echo e($flash['type']); ?>"
            >

                <?php echo e($flash['message']); ?>

            </div>

        <?php endif; ?>


        <div class="row g-4">


            <!-- =========================================
                 ACCOUNT INFORMATION
            ========================================== -->

            <div class="col-lg-7">

                <div class="admin-card h-100">


                    <div class="admin-card-title">

                        <i class="bi bi-person-circle"></i>

                        Account Information

                    </div>


                    <p class="admin-small-text mb-4">

                        Update your administrator account information.

                    </p>


                    <form
                        method="POST"
                        action="<?php echo APP_URL; ?>/admin/actions/profile_action.php"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo e($csrfToken); ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="update_info"
                        >


                        <!-- Full Name -->

                        <div class="mb-3">

                            <label class="admin-form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control admin-form-control"
                                value="<?php echo e($admin['full_name']); ?>"
                                required
                            >

                        </div>


                        <!-- Email -->

                        <div class="mb-3">

                            <label class="admin-form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control admin-form-control"
                                value="<?php echo e($admin['email']); ?>"
                                required
                            >

                        </div>


                        <!-- Mobile -->

                        <div class="mb-4">

                            <label class="admin-form-label">
                                Mobile Number
                            </label>

                            <input
                                type="text"
                                name="mobile"
                                class="form-control admin-form-control"
                                value="<?php echo e($admin['mobile'] ?? ''); ?>"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-admin"
                        >

                            <i class="bi bi-check2-circle me-1"></i>

                            Update Information

                        </button>

                    </form>

                </div>

            </div>


            <!-- =========================================
                 ACCOUNT DETAILS
            ========================================== -->

            <div class="col-lg-5">

                <div class="admin-card h-100">


                    <div class="admin-card-title">

                        <i class="bi bi-info-circle"></i>

                        Account Details

                    </div>


                    <div class="admin-data-row">

                        <span>
                            Admin ID
                        </span>

                        <strong>
                            #<?php echo e($admin['id']); ?>
                        </strong>

                    </div>


                    <div class="admin-data-row">

                        <span>
                            Status
                        </span>

                        <?php if ($admin['status'] === 'active'): ?>

                            <span class="badge bg-success">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="badge bg-danger">
                                Blocked
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="admin-data-row">

                        <span>
                            Account Created
                        </span>

                        <strong>

                            <?php
                            echo e(
                                date(
                                    'd M Y',
                                    strtotime($admin['created_at'])
                                )
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="mt-4 p-3 rounded bg-light">

                        <div class="admin-small-text">

                            <i class="bi bi-shield-check me-1"></i>

                            Your administrator account is stored
                            separately from applicant accounts.

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 CHANGE PASSWORD
            ========================================== -->

            <div class="col-lg-7">

                <div class="admin-card">


                    <div class="admin-card-title">

                        <i class="bi bi-key"></i>

                        Change Password

                    </div>


                    <p class="admin-small-text mb-4">

                        Use your current password to set a new password.

                    </p>


                    <form
                        method="POST"
                        action="<?php echo APP_URL; ?>/admin/actions/profile_action.php"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo e($csrfToken); ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="change_password"
                        >


                        <!-- Current Password -->

                        <div class="mb-3">

                            <label class="admin-form-label">
                                Current Password
                            </label>

                            <input
                                type="password"
                                name="current_password"
                                class="form-control admin-form-control"
                                required
                            >

                        </div>


                        <!-- New Password -->

                        <div class="mb-3">

                            <label class="admin-form-label">
                                New Password
                            </label>

                            <input
                                type="password"
                                name="new_password"
                                class="form-control admin-form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <!-- Confirm Password -->

                        <div class="mb-4">

                            <label class="admin-form-label">
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control admin-form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-admin"
                        >

                            <i class="bi bi-lock me-1"></i>

                            Change Password

                        </button>

                    </form>

                </div>

            </div>


            <!-- =========================================
                 SECURITY
            ========================================== -->

            <div class="col-lg-5">

                <div class="admin-card">


                    <div class="admin-card-title">

                        <i class="bi bi-shield-lock"></i>

                        Security

                    </div>


                    <div class="admin-small-text mb-3">

                        Keep your administrator account secure.

                    </div>


                    <ul class="admin-small-text ps-3 mb-0">

                        <li class="mb-2">
                            Never share your admin password.
                        </li>

                        <li class="mb-2">
                            Use a strong password.
                        </li>

                        <li class="mb-2">
                            Always logout after using the system.
                        </li>

                        <li>
                            Do not leave your admin session open on shared computers.
                        </li>

                    </ul>


                    <a
                        href="<?php echo APP_URL; ?>/admin/logout.php"
                        class="btn btn-outline-danger w-100 mt-4"
                    >

                        <i class="bi bi-box-arrow-right me-1"></i>

                        Logout

                    </a>

                </div>

            </div>


        </div>

    </div>

</main>


</body>

</html>