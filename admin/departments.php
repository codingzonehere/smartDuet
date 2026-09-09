<?php
/**
 * Smart DUET Admission Management System
 * Admin Department Management
 *
 * Purpose:
 * - View all departments
 * - View department information
 * - Update available seat numbers
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';


/* =========================================================
   ADMIN AUTHENTICATION
   ========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit;
}


/* =========================================================
   CSRF TOKEN
   ========================================================= */

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));

}


/* =========================================================
   GET DEPARTMENTS
   ========================================================= */

$sql = "
    SELECT
        id,
        code,
        name,
        faculty,
        degree,
        duration,
        seats,
        is_active
    FROM departments
    ORDER BY id ASC
";


$result = $conn->query($sql);

$departments = [];


if ($result) {

    while ($row = $result->fetch_assoc()) {

        $departments[] = $row;

    }

}


/* =========================================================
   FLASH MESSAGE
   ========================================================= */

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
        Departments | Smart DUET Admin
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


<!-- =========================================================
     ADMIN SIDEBAR
     ========================================================= -->

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



        <div class="admin-menu-title">

            Information

        </div>


        <a href="<?= APP_URL ?>/admin/notices.php">

            <i class="bi bi-megaphone"></i>

            Notices

        </a>


        <a
            href="<?= APP_URL ?>/admin/departments.php"
            class="active"
        >

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



<!-- =========================================================
     MAIN CONTENT
     ========================================================= -->

<main class="admin-main">


    <!-- =====================================================
         HERO
         ===================================================== -->

    <section class="admin-hero">


        <h2>

            <i class="bi bi-building me-2"></i>

            Department Management

        </h2>


        <p>

            View and manage admission departments
            and available seats.

        </p>


    </section>



    <!-- =====================================================
         CONTENT
         ===================================================== -->

    <section class="admin-content">


        <!-- =================================================
             FLASH MESSAGE
             ================================================= -->

        <?php if ($flash): ?>


            <div
                class="alert
                       alert-<?= e($flash['type']) ?>
                       alert-dismissible
                       fade show"
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
             DEPARTMENT LIST
             ================================================= -->

        <div class="admin-card">


            <div class="admin-card-title">


                <i class="bi bi-building me-2"></i>


                Departments


                <span class="admin-small-text">

                    (<?= count($departments) ?>)

                </span>


            </div>



            <?php if (empty($departments)): ?>


                <div class="admin-empty">


                    <i class="bi bi-building-x"></i>


                    <h6>

                        No Departments Found

                    </h6>


                </div>


            <?php else: ?>


                <?php foreach ($departments as $department): ?>


                    <div class="admin-data-row">


                        <div
                            class="row
                                   align-items-center
                                   g-3"
                        >


                            <!-- =================================
                                 DEPARTMENT
                            ================================== -->

                            <div class="col-lg-4">


                                <div
                                    class="fw-bold
                                           admin-primary-text"
                                >

                                    <?= e(
                                        $department['name']
                                    ) ?>


                                </div>


                                <div class="admin-small-text">


                                    Code:

                                    <?= e(
                                        $department['code']
                                    ) ?>


                                </div>


                            </div>



                            <!-- =================================
                                 FACULTY
                            ================================== -->

                            <div class="col-lg-2">


                                <div class="admin-small-text">

                                    Faculty

                                </div>


                                <strong>

                                    <?= e(
                                        $department['faculty']
                                    ) ?>

                                </strong>


                            </div>



                            <!-- =================================
                                 DEGREE
                            ================================== -->

                            <div class="col-lg-2">


                                <div class="admin-small-text">

                                    Degree

                                </div>


                                <strong>

                                    <?= e(
                                        $department['degree']
                                    ) ?>

                                </strong>


                            </div>



                            <!-- =================================
                                 SEATS
                            ================================== -->

                            <div class="col-lg-1">


                                <div class="admin-small-text">

                                    Seats

                                </div>


                                <strong>

                                    <?= e(
                                        $department['seats']
                                    ) ?>

                                </strong>


                            </div>



                            <!-- =================================
                                 STATUS
                            ================================== -->

                            <div class="col-lg-1">


                                <?php if (
                                    (int)
                                    $department['is_active']
                                    === 1
                                ): ?>


                                    <span
                                        class="badge
                                               text-bg-success"
                                    >

                                        Active

                                    </span>


                                <?php else: ?>


                                    <span
                                        class="badge
                                               text-bg-secondary"
                                    >

                                        Inactive

                                    </span>


                                <?php endif; ?>


                            </div>



                            <!-- =================================
                                 DETAILS BUTTON
                            ================================== -->

                            <div
                                class="col-lg-2
                                       text-lg-end"
                            >


                                <button
                                    type="button"
                                    class="btn
                                           btn-outline-success
                                           btn-sm"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#department<?= (int) $department['id'] ?>"
                                >

                                    <i
                                        class="bi bi-eye me-1"
                                    ></i>

                                    Details

                                </button>


                            </div>


                        </div>



                        <!-- =================================================
                             DETAILS
                        ================================================= -->

                        <div
                            class="collapse"
                            id="department<?= (int) $department['id'] ?>"
                        >


                            <div
                                class="admin-result-form
                                       mt-3"
                            >


                                <div class="row g-3">


                                    <!-- NAME -->

                                    <div class="col-md-6">


                                        <div
                                            class="admin-small-text"
                                        >

                                            Department Name

                                        </div>


                                        <strong>

                                            <?= e(
                                                $department['name']
                                            ) ?>

                                        </strong>


                                    </div>



                                    <!-- CODE -->

                                    <div class="col-md-3">


                                        <div
                                            class="admin-small-text"
                                        >

                                            Code

                                        </div>


                                        <strong>

                                            <?= e(
                                                $department['code']
                                            ) ?>

                                        </strong>


                                    </div>



                                    <!-- DURATION -->

                                    <div class="col-md-3">


                                        <div
                                            class="admin-small-text"
                                        >

                                            Duration

                                        </div>


                                        <strong>

                                            <?= e(
                                                $department['duration']
                                            ) ?>

                                        </strong>


                                    </div>



                                    <!-- FACULTY -->

                                    <div class="col-md-6">


                                        <div
                                            class="admin-small-text"
                                        >

                                            Faculty

                                        </div>


                                        <strong>

                                            <?= e(
                                                $department['faculty']
                                            ) ?>

                                        </strong>


                                    </div>



                                    <!-- DEGREE -->

                                    <div class="col-md-3">


                                        <div
                                            class="admin-small-text"
                                        >

                                            Degree

                                        </div>


                                        <strong>

                                            <?= e(
                                                $department['degree']
                                            ) ?>

                                        </strong>


                                    </div>



                                    <!-- SEATS -->

                                    <div class="col-md-3">


                                        <div
                                            class="admin-small-text"
                                        >

                                            Current Seats

                                        </div>


                                        <strong
                                            class="admin-primary-text"
                                        >

                                            <?= e(
                                                $department['seats']
                                            ) ?>


                                        </strong>


                                    </div>



                                    <!-- =================================================
                                         SEAT UPDATE FORM
                                    ================================================= -->

                                    <div class="col-12">


                                        <hr>


                                        <form
                                            method="POST"
                                            action="<?= APP_URL ?>/admin/actions/department_action.php"
                                        >


                                            <!-- CSRF -->

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e(
                                                    $_SESSION['csrf_token']
                                                ) ?>"
                                            >


                                            <!-- Department ID -->

                                            <input
                                                type="hidden"
                                                name="department_id"
                                                value="<?= (int) $department['id'] ?>"
                                            >


                                            <!-- Action -->

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="update_seats"
                                            >



                                            <div
                                                class="row
                                                       g-3
                                                       align-items-end"
                                            >


                                                <!-- Seat Number -->

                                                <div class="col-md-8">


                                                    <label
                                                        class="admin-form-label
                                                               mb-1"
                                                    >

                                                        Available Seats

                                                    </label>


                                                    <input
                                                        type="number"
                                                        name="seats"
                                                        class="form-control"
                                                        min="0"
                                                        max="10000"
                                                        step="1"
                                                        value="<?= (int) $department['seats'] ?>"
                                                        required
                                                    >


                                                    <div
                                                        class="admin-small-text
                                                               mt-1"
                                                    >

                                                        Enter the total
                                                        available seats
                                                        for this department.

                                                    </div>


                                                </div>



                                                <!-- Update Button -->

                                                <div class="col-md-4">


                                                    <button
                                                        type="submit"
                                                        class="btn
                                                               btn-admin
                                                               w-100"
                                                    >

                                                        <i
                                                            class="bi
                                                                   bi-check-circle
                                                                   me-1"
                                                        ></i>

                                                        Update Seats

                                                    </button>


                                                </div>


                                            </div>


                                        </form>


                                    </div>


                                </div>


                            </div>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php endif; ?>


        </div>


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