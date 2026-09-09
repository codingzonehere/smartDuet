<?php
/**
 * Smart DUET Admission Management System
 * Admin Notice Management
 *
 * Purpose:
 * - Add notice
 * - Edit notice
 * - Publish / Draft notice
 * - Delete notice
 * - Upload optional PDF
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
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


/* =========================================================
   GET NOTICES
   ========================================================= */

$sql = "
    SELECT
        id,
        title,
        description,
        pdf_file,
        admission_year,
        published_date,
        status,
        created_at
    FROM notices
    ORDER BY published_date DESC, id DESC
";

$result = $conn->query($sql);

$notices = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $notices[] = $row;
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

    <title>Notices | Smart DUET Admin</title>


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
        $logoPath = __DIR__ . '/../assets/images/duet-logo.png';

        if (file_exists($logoPath)):
        ?>

            <img
                src="<?= APP_URL ?>/assets/images/duet-logo.png"
                alt="DUET Logo"
            >

        <?php endif; ?>


        <h5>Smart DUET</h5>

        <small>Admin Panel</small>

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


        <a
            href="<?= APP_URL ?>/admin/notices.php"
            class="active"
        >

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



<!-- =========================================================
     MAIN CONTENT
     ========================================================= -->

<main class="admin-main">


    <!-- HERO -->

    <section class="admin-hero">

        <h2>

            <i class="bi bi-megaphone me-2"></i>

            Notice Management

        </h2>

        <p>
            Create and manage admission notices.
        </p>

    </section>



    <!-- CONTENT -->

    <section class="admin-content">


        <!-- FLASH -->

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
             ADD NOTICE
             ================================================= -->

        <div class="admin-card mb-4">


            <div class="admin-card-title">

                <i class="bi bi-plus-circle me-2"></i>

                Add New Notice

            </div>


            <form
                method="POST"
                action="<?= APP_URL ?>/admin/actions/notice_action.php"
                enctype="multipart/form-data"
            >


                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>"
                >


                <input
                    type="hidden"
                    name="action"
                    value="create"
                >


                <div class="row g-3">


                    <!-- TITLE -->

                    <div class="col-md-8">

                        <label class="admin-form-label mb-1">
                            Notice Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            maxlength="255"
                            required
                        >

                    </div>


                    <!-- YEAR -->

                    <div class="col-md-4">

                        <label class="admin-form-label mb-1">
                            Admission Year
                        </label>

                        <input
                            type="number"
                            name="admission_year"
                            class="form-control"
                            min="2000"
                            max="2100"
                            placeholder="Example: 2026"
                        >

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="col-md-8">

                        <label class="admin-form-label mb-1">
                            Description
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                            placeholder="Short notice description"
                        ></textarea>

                    </div>


                    <!-- DATE -->

                    <div class="col-md-4">

                        <label class="admin-form-label mb-1">
                            Published Date
                        </label>

                        <input
                            type="date"
                            name="published_date"
                            class="form-control"
                            value="<?= date('Y-m-d') ?>"
                            required
                        >

                    </div>


                    <!-- PDF -->

                    <div class="col-md-8">

                        <label class="admin-form-label mb-1">
                            Notice PDF
                        </label>

                        <input
                            type="file"
                            name="pdf_file"
                            class="form-control"
                            accept=".pdf"
                        >

                        <div class="admin-small-text mt-1">
                            Optional. PDF only, maximum 5MB.
                        </div>

                    </div>


                    <!-- STATUS -->

                    <div class="col-md-4">

                        <label class="admin-form-label mb-1">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                            required
                        >

                            <option value="published">
                                Published
                            </option>

                            <option value="draft">
                                Draft
                            </option>

                        </select>

                    </div>


                    <!-- BUTTON -->

                    <div class="col-12 text-end">

                        <button
                            type="submit"
                            class="btn btn-admin"
                        >

                            <i class="bi bi-plus-circle me-1"></i>

                            Add Notice

                        </button>

                    </div>

                </div>

            </form>

        </div>



        <!-- =================================================
             NOTICE LIST
             ================================================= -->

        <div class="admin-card">


            <div class="admin-card-title">

                <i class="bi bi-list-ul me-2"></i>

                Existing Notices

                <span class="admin-small-text">
                    (<?= count($notices) ?>)
                </span>

            </div>



            <?php if (empty($notices)): ?>


                <div class="admin-empty">

                    <i class="bi bi-megaphone"></i>

                    <h6>
                        No Notices Found
                    </h6>

                    <p>
                        Add a notice using the form above.
                    </p>

                </div>


            <?php else: ?>


                <?php foreach ($notices as $notice): ?>


                    <div class="admin-data-row">


                        <!-- BASIC NOTICE -->

                        <div class="row align-items-center g-3">


                            <div class="col-lg-5">

                                <div class="fw-bold admin-primary-text">

                                    <?= e($notice['title']) ?>

                                </div>


                                <?php if (!empty($notice['description'])): ?>

                                    <div class="admin-small-text mt-1">

                                        <?= e(
                                            mb_strimwidth(
                                                $notice['description'],
                                                0,
                                                100,
                                                '...'
                                            )
                                        ) ?>

                                    </div>

                                <?php endif; ?>

                            </div>


                            <div class="col-lg-2">

                                <div class="admin-small-text">
                                    Year
                                </div>

                                <strong>

                                    <?= e(
                                        $notice['admission_year']
                                        ?? 'All'
                                    ) ?>

                                </strong>

                            </div>


                            <div class="col-lg-2">

                                <div class="admin-small-text">
                                    Date
                                </div>

                                <strong>
                                    <?= e($notice['published_date']) ?>
                                </strong>

                            </div>


                            <div class="col-lg-1">

                                <?php if (
                                    $notice['status'] === 'published'
                                ): ?>

                                    <span class="badge text-bg-success">
                                        Published
                                    </span>

                                <?php else: ?>

                                    <span class="badge text-bg-secondary">
                                        Draft
                                    </span>

                                <?php endif; ?>

                            </div>


                            <div class="col-lg-2 text-lg-end">

                                <button
                                    type="button"
                                    class="btn btn-outline-success btn-sm"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#notice<?= (int) $notice['id'] ?>"
                                >

                                    <i class="bi bi-pencil-square me-1"></i>

                                    Manage

                                </button>

                            </div>

                        </div>



                        <!-- =================================================
                             EDIT FORM
                             ================================================= -->

                        <div
                            class="collapse"
                            id="notice<?= (int) $notice['id'] ?>"
                        >

                            <div class="admin-result-form mt-3">


                                <form
                                    method="POST"
                                    action="<?= APP_URL ?>/admin/actions/notice_action.php"
                                    enctype="multipart/form-data"
                                >


                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e($_SESSION['csrf_token']) ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="action"
                                        value="update"
                                    >


                                    <input
                                        type="hidden"
                                        name="notice_id"
                                        value="<?= (int) $notice['id'] ?>"
                                    >


                                    <div class="row g-3">


                                        <!-- TITLE -->

                                        <div class="col-md-8">

                                            <label class="admin-form-label mb-1">
                                                Notice Title
                                            </label>

                                            <input
                                                type="text"
                                                name="title"
                                                class="form-control"
                                                maxlength="255"
                                                value="<?= e($notice['title']) ?>"
                                                required
                                            >

                                        </div>


                                        <!-- YEAR -->

                                        <div class="col-md-4">

                                            <label class="admin-form-label mb-1">
                                                Admission Year
                                            </label>

                                            <input
                                                type="number"
                                                name="admission_year"
                                                class="form-control"
                                                min="2000"
                                                max="2100"
                                                value="<?= e(
                                                    $notice['admission_year']
                                                    ?? ''
                                                ) ?>"
                                            >

                                        </div>


                                        <!-- DESCRIPTION -->

                                        <div class="col-md-8">

                                            <label class="admin-form-label mb-1">
                                                Description
                                            </label>

                                            <textarea
                                                name="description"
                                                class="form-control"
                                                rows="3"
                                            ><?= e($notice['description'] ?? '') ?></textarea>

                                        </div>


                                        <!-- DATE -->

                                        <div class="col-md-4">

                                            <label class="admin-form-label mb-1">
                                                Published Date
                                            </label>

                                            <input
                                                type="date"
                                                name="published_date"
                                                class="form-control"
                                                value="<?= e($notice['published_date']) ?>"
                                                required
                                            >

                                        </div>


                                        <!-- PDF -->

                                        <div class="col-md-8">

                                            <label class="admin-form-label mb-1">
                                                Replace PDF
                                            </label>

                                            <input
                                                type="file"
                                                name="pdf_file"
                                                class="form-control"
                                                accept=".pdf"
                                            >

                                            <?php if (!empty($notice['pdf_file'])): ?>

                                                <a
                                                    href="<?= APP_URL ?>/assets/uploads/notices/<?= e($notice['pdf_file']) ?>"
                                                    target="_blank"
                                                    class="btn btn-sm btn-outline-secondary mt-2"
                                                >

                                                    <i class="bi bi-file-earmark-pdf me-1"></i>

                                                    View Current PDF

                                                </a>

                                            <?php endif; ?>

                                        </div>


                                        <!-- STATUS -->

                                        <div class="col-md-4">

                                            <label class="admin-form-label mb-1">
                                                Status
                                            </label>

                                            <select
                                                name="status"
                                                class="form-select"
                                                required
                                            >

                                                <option
                                                    value="published"
                                                    <?= $notice['status'] === 'published'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    Published
                                                </option>

                                                <option
                                                    value="draft"
                                                    <?= $notice['status'] === 'draft'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    Draft
                                                </option>

                                            </select>

                                        </div>


                                        <!-- BUTTONS -->

                                        <div class="col-12 text-end">


                                            <button
                                                type="submit"
                                                class="btn btn-admin"
                                            >

                                                <i class="bi bi-check-circle me-1"></i>

                                                Update Notice

                                            </button>


                                            <button
                                                type="submit"
                                                name="delete_notice"
                                                value="1"
                                                class="btn btn-outline-danger ms-2"
                                                onclick="return confirm('Are you sure you want to delete this notice?');"
                                            >

                                                <i class="bi bi-trash me-1"></i>

                                                Delete

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


    </section>

</main>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>