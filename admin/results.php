<?php

/**
 * Smart DUET Admission Management System
 * Admin Result Management
 *
 * Features:
 * - Search applicant/application
 * - Filter by department
 * - Filter by result status
 * - View application information
 * - Add/update merit position
 * - Add/update result status
 * - Add/update remarks
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

$resultFilter = trim(
    $_GET['result_status'] ?? ''
);


// ======================================================
// VALID RESULT FILTER
// ======================================================

$allowedResultFilters = [
    '',
    'pending',
    'selected',
    'waiting',
    'not_selected'
];


if (
    !in_array(
        $resultFilter,
        $allowedResultFilters,
        true
    )
) {

    $resultFilter = '';

}


// ======================================================
// GET ACTIVE DEPARTMENTS
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

    $result =
        $stmt->get_result();


    while (
        $row =
        $result->fetch_assoc()
    ) {

        $departments[] = $row;

    }


    $stmt->close();

}


// ======================================================
// RESULT DATA
// ======================================================

$results = [];


// ======================================================
// BUILD QUERY
// ======================================================

$sql = "
    SELECT

        a.id AS application_id,
        a.application_no,
        a.admission_year,
        a.status AS application_status,

        ap.full_name,
        ap.father_name,

        d.id AS department_id,
        d.code AS department_code,
        d.name AS department_name,

        t.name AS technology_name,

        r.id AS result_id,
        r.merit_position,
        r.result_status,
        r.published_date,
        r.remarks

    FROM applications a

    INNER JOIN applicants ap
        ON a.applicant_id = ap.id

    INNER JOIN departments d
        ON a.department_id = d.id

    INNER JOIN diploma_technologies t
        ON a.technology_id = t.id

    LEFT JOIN results r
        ON a.id = r.application_id

    WHERE 1 = 1
";


// ======================================================
// SEARCH
// ======================================================

$params = [];

$types = '';


if ($search !== '') {

    $sql .= "
        AND (
            a.application_no LIKE ?
            OR ap.full_name LIKE ?
            OR ap.father_name LIKE ?
        )
    ";


    $searchValue =
        '%' . $search . '%';


    $params[] =
        $searchValue;

    $params[] =
        $searchValue;

    $params[] =
        $searchValue;


    $types .= 'sss';

}


// ======================================================
// DEPARTMENT FILTER
// ======================================================

if ($departmentId > 0) {

    $sql .= "
        AND a.department_id = ?
    ";


    $params[] =
        $departmentId;


    $types .= 'i';

}


// ======================================================
// RESULT STATUS FILTER
// ======================================================

if ($resultFilter !== '') {

    if ($resultFilter === 'pending') {

        /*
        | If no result record exists,
        | it is also treated as Pending.
        */

        $sql .= "
            AND (
                r.result_status = 'pending'
                OR r.result_status IS NULL
            )
        ";

    } else {

        $sql .= "
            AND r.result_status = ?
        ";


        $params[] =
            $resultFilter;


        $types .= 's';

    }

}


// ======================================================
// ORDER
// ======================================================

$sql .= "
    ORDER BY
        a.department_id ASC,
        r.merit_position IS NULL ASC,
        r.merit_position ASC,
        a.id DESC

    LIMIT 100
";


// ======================================================
// EXECUTE QUERY
// ======================================================

$stmt =
    $conn->prepare($sql);


if ($stmt) {

    if (!empty($params)) {

        $stmt->bind_param(
            $types,
            ...$params
        );

    }


    $stmt->execute();


    $queryResult =
        $stmt->get_result();


    while (
        $row =
        $queryResult->fetch_assoc()
    ) {

        /*
        |--------------------------------------------------
        | If no result record exists,
        | show Pending.
        |--------------------------------------------------
        */

        if (
            empty(
                $row['result_status']
            )
        ) {

            $row['result_status'] =
                'pending';

        }


        $results[] =
            $row;

    }


    $stmt->close();

}


// ======================================================
// CHECK FILTER
// ======================================================

$hasFilter =
    $search !== ''
    || $departmentId > 0
    || $resultFilter !== '';


// ======================================================
// FLASH MESSAGE
// ======================================================

$flash =
    getFlashMessage();

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
        Results | Smart DUET Admin
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


        if (
            file_exists($logoPath)
        ):

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


        <a
            href="<?= APP_URL ?>/admin/dashboard.php"
        >

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <div class="admin-menu-title">
            Admission
        </div>


        <a
            href="<?= APP_URL ?>/admin/applicants.php"
        >

            <i class="bi bi-people"></i>

            Applicants

        </a>


        <a
            href="<?= APP_URL ?>/admin/applications.php"
        >

            <i class="bi bi-file-earmark-text"></i>

            Applications

        </a>


        <a
            href="<?= APP_URL ?>/admin/admit-cards.php"
        >

            <i class="bi bi-person-vcard"></i>

            Admit Cards

        </a>


        <a
            href="<?= APP_URL ?>/admin/results.php"
            class="active"
        >

            <i class="bi bi-trophy"></i>

            Results

        </a>


        <div class="admin-menu-title">
            Information
        </div>


        <a
            href="<?= APP_URL ?>/admin/notices.php"
        >

            <i class="bi bi-megaphone"></i>

            Notices

        </a>


        <a
            href="<?= APP_URL ?>/admin/departments.php"
        >

            <i class="bi bi-building"></i>

            Departments

        </a>


        <div class="admin-menu-title">
            Account
        </div>


        <a
            href="<?= APP_URL ?>/admin/profile.php"
        >

            <i class="bi bi-person-circle"></i>

            Profile

        </a>


        <a
            href="<?= APP_URL ?>/admin/logout.php"
        >

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

            <i class="bi bi-trophy me-2"></i>

            Result Management

        </h2>


        <p>

            Manage applicant merit positions
            and admission results.

        </p>


    </section>


    <!-- =================================================
         CONTENT
    ================================================== -->

    <section class="admin-content">


        <!-- FLASH -->

        <?php if ($flash): ?>


            <div
                class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show"
                role="alert"
            >

                <?= e(
                    $flash['message']
                ) ?>


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

                Search & Filter Results

            </div>


            <form method="GET">


                <div class="row g-3 mb-3">


                    <!-- SEARCH -->

                    <div class="col-lg-5">


                        <label class="admin-form-label">

                            Search

                        </label>


                        <input
                            type="text"
                            name="search"
                            class="form-control admin-form-control"
                            placeholder="Application No, Applicant Name or Father's Name"
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
                                    <?= $departmentId ===
                                        (int) $department['id']
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


                    <!-- RESULT STATUS -->

                    <div class="col-lg-4">


                        <label class="admin-form-label">

                            Result Status

                        </label>


                        <select
                            name="result_status"
                            class="form-select admin-form-select"
                        >


                            <option value="">

                                All Result Status

                            </option>


                            <option
                                value="pending"
                                <?= $resultFilter === 'pending'
                                    ? 'selected'
                                    : '' ?>
                            >

                                Pending

                            </option>


                            <option
                                value="selected"
                                <?= $resultFilter === 'selected'
                                    ? 'selected'
                                    : '' ?>
                            >

                                Selected

                            </option>


                            <option
                                value="waiting"
                                <?= $resultFilter === 'waiting'
                                    ? 'selected'
                                    : '' ?>
                            >

                                Waiting List

                            </option>


                            <option
                                value="not_selected"
                                <?= $resultFilter === 'not_selected'
                                    ? 'selected'
                                    : '' ?>
                            >

                                Not Selected

                            </option>


                        </select>


                    </div>


                </div>


                <!-- BUTTONS -->

                <div class="row g-3">


                    <div class="col-md-6">


                        <button
                            type="submit"
                            class="btn btn-admin w-100"
                        >

                            <i class="bi bi-search me-1"></i>

                            Search / Filter

                        </button>


                    </div>


                    <div class="col-md-6">


                        <a
                            href="<?= APP_URL ?>/admin/results.php"
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
             RESULT LIST
        ================================================== -->

        <?php if ($hasFilter): ?>


            <div class="admin-card">


                <div class="admin-card-title">


                    <i class="bi bi-list-ul me-2"></i>

                    Applicants


                    <span class="admin-small-text">

                        (<?= count($results) ?> found)

                    </span>


                </div>


                <?php if (
                    empty($results)
                ): ?>


                    <div class="admin-empty">


                        <i class="bi bi-trophy"></i>


                        <h6>

                            No Applicant Found

                        </h6>


                        <p>

                            Try changing your search
                            or filters.

                        </p>


                    </div>


                <?php else: ?>


                    <?php foreach (
                        $results
                        as $result
                    ): ?>


                        <div class="admin-data-row">


                            <!-- BASIC INFORMATION -->

                            <div class="row align-items-center g-3">


                                <!-- APPLICATION -->

                                <div class="col-lg-3">


                                    <div class="admin-small-text">

                                        Application No.

                                    </div>


                                    <div
                                        class="fw-bold admin-primary-text"
                                    >

                                        <?= e(
                                            $result['application_no']
                                        ) ?>

                                    </div>


                                    <div class="admin-small-text mt-1">

                                        <?= e(
                                            $result['full_name']
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
                                            $result['department_code']
                                        ) ?>

                                        -

                                        <?= e(
                                            $result['department_name']
                                        ) ?>

                                    </div>


                                    <div class="admin-small-text">

                                        <?= e(
                                            $result['technology_name']
                                        ) ?>

                                    </div>


                                </div>


                                <!-- MERIT -->

                                <div class="col-lg-2">


                                    <div class="admin-small-text">

                                        Merit Position

                                    </div>


                                    <?php if (
                                        !empty(
                                            $result['merit_position']
                                        )
                                    ): ?>


                                        <span
                                            class="fw-bold fs-5"
                                        >

                                            <?= e(
                                                $result['merit_position']
                                            ) ?>

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="text-muted"
                                        >

                                            Not Set

                                        </span>


                                    <?php endif; ?>


                                </div>


                                <!-- RESULT STATUS -->

                                <div class="col-lg-2">


                                    <div class="admin-small-text">

                                        Result

                                    </div>


                                    <?php

                                    $resultStatus =
                                        $result['result_status'];

                                    $resultClass =
                                        'bg-secondary';


                                    if (
                                        $resultStatus
                                        === 'selected'
                                    ) {

                                        $resultClass =
                                            'bg-success';

                                    } elseif (
                                        $resultStatus
                                        === 'waiting'
                                    ) {

                                        $resultClass =
                                            'bg-warning text-dark';

                                    } elseif (
                                        $resultStatus
                                        === 'not_selected'
                                    ) {

                                        $resultClass =
                                            'bg-danger';

                                    } elseif (
                                        $resultStatus
                                        === 'pending'
                                    ) {

                                        $resultClass =
                                            'bg-secondary';

                                    }

                                    ?>


                                    <span
                                        class="badge <?= $resultClass ?>"
                                    >

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

                                    </span>


                                </div>


                                <!-- MANAGE -->

                                <div
                                    class="col-lg-2 text-lg-end"
                                >


                                    <button
                                        type="button"
                                        class="btn btn-outline-success btn-sm"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#result<?= (int) $result['application_id'] ?>"
                                    >

                                        <i class="bi bi-pencil-square me-1"></i>

                                        Manage

                                    </button>


                                </div>


                            </div>


                            <!-- =================================================
                                 RESULT FORM
                            ================================================== -->

                            <div
                                class="collapse"
                                id="result<?= (int) $result['application_id'] ?>"
                            >


                                <div
                                    class="admin-result-form mt-3"
                                >


                                    <!-- APPLICANT INFO -->

                                    <h6 class="fw-bold mb-3">

                                        <i class="bi bi-person me-2"></i>

                                        Applicant Information

                                    </h6>


                                    <div class="row g-3 mb-4">


                                        <!-- FULL NAME -->

                                        <div class="col-md-4">


                                            <div
                                                class="admin-small-text"
                                            >

                                                Full Name

                                            </div>


                                            <strong>

                                                <?= e(
                                                    $result['full_name']
                                                ) ?>

                                            </strong>


                                        </div>


                                        <!-- APPLICATION -->

                                        <div class="col-md-4">


                                            <div
                                                class="admin-small-text"
                                            >

                                                Application No.

                                            </div>


                                            <strong>

                                                <?= e(
                                                    $result['application_no']
                                                ) ?>

                                            </strong>


                                        </div>


                                        <!-- FATHER -->

                                        <div class="col-md-4">


                                            <div
                                                class="admin-small-text"
                                            >

                                                Father's Name

                                            </div>


                                            <strong>

                                                <?= e(
                                                    $result['father_name']
                                                ) ?>

                                            </strong>


                                        </div>


                                        <!-- DEPARTMENT -->

                                        <div class="col-md-4">


                                            <div
                                                class="admin-small-text"
                                            >

                                                Department

                                            </div>


                                            <strong>

                                                <?= e(
                                                    $result['department_code']
                                                ) ?>

                                                -

                                                <?= e(
                                                    $result['department_name']
                                                ) ?>

                                            </strong>


                                        </div>


                                        <!-- TECHNOLOGY -->

                                        <div class="col-md-4">


                                            <div
                                                class="admin-small-text"
                                            >

                                                Technology

                                            </div>


                                            <strong>

                                                <?= e(
                                                    $result['technology_name']
                                                ) ?>

                                            </strong>


                                        </div>


                                    </div>


                                    <!-- =================================================
                                         RESULT UPDATE
                                    ================================================== -->

                                    <form
                                        method="POST"
                                        action="<?= APP_URL ?>/admin/actions/result_action.php"
                                    >


                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(
                                                $_SESSION['csrf_token']
                                            ) ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="application_id"
                                            value="<?= (int) $result['application_id'] ?>"
                                        >


                                        <div class="row g-3">


                                            <!-- MERIT POSITION -->

                                            <div class="col-md-4">


                                                <label
                                                    class="admin-form-label"
                                                >

                                                    Merit Position

                                                </label>


                                                <input
                                                    type="number"
                                                    name="merit_position"
                                                    class="form-control admin-form-control"
                                                    min="1"
                                                    placeholder="Example: 1"
                                                    value="<?= e(
                                                        $result['merit_position'] ?? ''
                                                    ) ?>"
                                                >


                                            </div>


                                            <!-- RESULT STATUS -->

                                            <div class="col-md-4">


                                                <label
                                                    class="admin-form-label"
                                                >

                                                    Result Status

                                                </label>


                                                <select
                                                    name="result_status"
                                                    class="form-select admin-form-select"
                                                    required
                                                >


                                                    <option
                                                        value="pending"
                                                        <?= $resultStatus === 'pending'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >

                                                        Pending

                                                    </option>


                                                    <option
                                                        value="selected"
                                                        <?= $resultStatus === 'selected'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >

                                                        Selected

                                                    </option>


                                                    <option
                                                        value="waiting"
                                                        <?= $resultStatus === 'waiting'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >

                                                        Waiting List

                                                    </option>


                                                    <option
                                                        value="not_selected"
                                                        <?= $resultStatus === 'not_selected'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >

                                                        Not Selected

                                                    </option>


                                                </select>


                                            </div>


                                            <!-- REMARKS -->

                                            <div class="col-md-4">


                                                <label
                                                    class="admin-form-label"
                                                >

                                                    Remarks

                                                </label>


                                                <input
                                                    type="text"
                                                    name="remarks"
                                                    class="form-control admin-form-control"
                                                    placeholder="Optional remarks"
                                                    value="<?= e(
                                                        $result['remarks'] ?? ''
                                                    ) ?>"
                                                >


                                            </div>


                                            <!-- SAVE -->

                                            <div class="col-12">


                                                <button
                                                    type="submit"
                                                    class="btn btn-admin"
                                                >

                                                    <i class="bi bi-check-circle me-1"></i>

                                                    Save Result

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


                    <i class="bi bi-trophy"></i>


                    <h5>

                        Result Management

                    </h5>


                    <p class="mb-0">

                        Search or filter applicants
                        to manage their admission results.

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