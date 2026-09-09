<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';

global $conn;


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));

}

$csrfToken =
    $_SESSION['csrf_token'];


/*
|--------------------------------------------------------------------------
| Current Year
|--------------------------------------------------------------------------
*/

$currentYear =
    (int) date('Y');


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$search =
    trim($_GET['search'] ?? '');


/*
|--------------------------------------------------------------------------
| Get Active Departments
|--------------------------------------------------------------------------
*/

$departments = [];


$stmt = $conn->prepare("
    SELECT
        id,
        code,
        name,
        seats

    FROM departments

    WHERE is_active = 1

    ORDER BY id ASC
");


$stmt->execute();


$result =
    $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    $departments[] = $row;

}


$stmt->close();


/*
|--------------------------------------------------------------------------
| Get Authorization Signature
|--------------------------------------------------------------------------
*/

$authorizationSignature = null;


$stmt = $conn->prepare("
    SELECT
        authorization_signature

    FROM admit_card_settings

    WHERE id = 1

    LIMIT 1
");


$stmt->execute();


$signatureSetting =
    $stmt->get_result()->fetch_assoc();


$stmt->close();


if ($signatureSetting) {

    $authorizationSignature =
        $signatureSetting[
            'authorization_signature'
        ];

}


/*
|--------------------------------------------------------------------------
| Get Department Schedules
|--------------------------------------------------------------------------
*/

$schedules = [];


$sql = "
    SELECT

        s.id,
        s.admission_year,
        s.department_id,
        s.exam_date,
        s.exam_shift,
        s.exam_center,
        s.status,

        d.code AS department_code,
        d.name AS department_name,

        (
            SELECT COUNT(*)

            FROM applications a

            INNER JOIN payments p
                ON p.application_id = a.id

            WHERE a.department_id = s.department_id

              AND a.admission_year =
                  s.admission_year

              AND p.payment_status = 'paid'

              AND a.status IN (
                  'submitted',
                  'under_review',
                  'accepted'
              )

        ) AS paid_applicants,


        (
            SELECT COUNT(*)

            FROM admit_cards ac

            INNER JOIN applications a2
                ON a2.id = ac.application_id

            WHERE a2.department_id =
                  s.department_id

              AND a2.admission_year =
                  s.admission_year

              AND ac.status = 'published'

        ) AS published_cards


    FROM admit_card_schedules s


    INNER JOIN departments d
        ON d.id = s.department_id
";


$params = [];
$types = '';


if ($search !== '') {

    $sql .= "
        WHERE
            d.code LIKE ?

            OR d.name LIKE ?

            OR CAST(
                s.admission_year AS CHAR
            ) LIKE ?
    ";


    $searchValue =
        '%' . $search . '%';


    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;


    $types = 'sss';

}


$sql .= "
    ORDER BY
        s.admission_year DESC,
        d.id ASC
";


$stmt =
    $conn->prepare($sql);


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

    $schedules[] = $row;

}


$stmt->close();


/*
|--------------------------------------------------------------------------
| Selected Schedule
|--------------------------------------------------------------------------
*/

$selectedSchedule = null;


$selectedId =
    (int)($_GET['schedule_id'] ?? 0);


if ($selectedId > 0) {

    $stmt = $conn->prepare("
        SELECT

            s.id,
            s.admission_year,
            s.department_id,
            s.exam_date,
            s.exam_shift,
            s.exam_center,
            s.status,

            d.code AS department_code,
            d.name AS department_name

        FROM admit_card_schedules s

        INNER JOIN departments d
            ON d.id = s.department_id

        WHERE s.id = ?

        LIMIT 1
    ");


    $stmt->bind_param(
        'i',
        $selectedId
    );


    $stmt->execute();


    $selectedSchedule =
        $stmt->get_result()->fetch_assoc();


    $stmt->close();

}


/*
|--------------------------------------------------------------------------
| Flash Message
|--------------------------------------------------------------------------
*/

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
        Admit Cards - Admin | Smart DUET
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


    <style>

        /*
        |--------------------------------------------------------------------------
        | Signature Preview
        |--------------------------------------------------------------------------
        */

        .signature-preview-box {

            min-height: 120px;

            border: 1px solid #dee2e6;

            border-radius: 10px;

            background: #fafafa;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 15px;

        }


        .signature-preview-box img {

            max-width: 260px;

            max-height: 85px;

            object-fit: contain;

        }


        /*
        |--------------------------------------------------------------------------
        | Publish Info
        |--------------------------------------------------------------------------
        */

        .publish-info {

            background: #f0f8ff;

            border-left: 4px solid #0d6efd;

            padding: 12px 15px;

            border-radius: 6px;

            font-size: 13px;

        }


        /*
        |--------------------------------------------------------------------------
        | Schedule Status
        |--------------------------------------------------------------------------
        */

        .schedule-published {

            color: #198754;

            font-weight: 600;

        }

    </style>

</head>


<body class="admin-body">


<div class="admin-wrapper">

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


        <a href="<?= APP_URL ?>/admin/admit-cards.php"
            class="active"
        >

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
         MAIN
    ========================================================== -->

    <main class="admin-main">


        <!-- =====================================================
             HERO
        ====================================================== -->

        <section class="admin-hero">


            <div>

                <h1>

                    <i class="bi bi-card-heading"></i>

                    Admit Card Management

                </h1>


                <p>

                    Manage department-wise examination
                    schedules and admit cards.

                </p>

            </div>


        </section>



        <div class="admin-content">


            <!-- =================================================
                 FLASH MESSAGE
            ================================================== -->

            <?php if ($flash): ?>

                <div
                    class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show"
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
                 AUTHORIZATION SIGNATURE
            ================================================== -->

            <div class="admin-card mb-4">


                <div class="admin-card-title">

                    <span>

                        <i class="bi bi-pen"></i>

                        Authorization Signature

                    </span>

                </div>


                <div class="row g-4 align-items-center">


                    <!-- Current Signature -->

                    <div class="col-md-5">


                        <label class="admin-form-label">

                            Current Signature

                        </label>


                        <div
                            class="signature-preview-box"
                        >


                            <?php if (
                                !empty(
                                    $authorizationSignature
                                )
                            ): ?>


                                <img
                                    src="<?= APP_URL ?>/assets/uploads/signatures/<?= e(
                                        basename(
                                            $authorizationSignature
                                        )
                                    ) ?>"
                                    alt="Authorization Signature"
                                >


                            <?php else: ?>


                                <div class="text-muted text-center">

                                    <i class="bi bi-pen fs-3"></i>

                                    <div class="mt-2">

                                        No authorization signature
                                        uploaded.

                                    </div>

                                </div>


                            <?php endif; ?>


                        </div>


                    </div>



                    <!-- Upload -->

                    <div class="col-md-7">


                        <form
                            method="POST"
                            action="<?= APP_URL ?>/admin/actions/admit_card_action.php"
                            enctype="multipart/form-data"
                        >


                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e(
                                    $csrfToken
                                ) ?>"
                            >


                            <input
                                type="hidden"
                                name="action"
                                value="save_authorization_signature"
                            >


                            <label class="admin-form-label">

                                Upload / Replace Signature

                            </label>


                            <input
                                type="file"
                                name="authorization_signature"
                                class="form-control admin-form-control"
                                accept=".jpg,.jpeg,.png"
                                required
                            >


                            <div class="admin-small-text mt-2">

                                JPG, JPEG or PNG only.
                                Maximum size: 500 KB.

                            </div>


                            <button
                                type="submit"
                                class="btn btn-admin mt-3"
                            >

                                <i class="bi bi-upload"></i>

                                Save Signature

                            </button>


                        </form>


                    </div>


                </div>


            </div>



            <!-- =================================================
                 CREATE / EDIT SCHEDULE
            ================================================== -->

            <div class="admin-card mb-4">


                <div class="admin-card-title">

                    <span>

                        <i class="bi bi-calendar-event"></i>

                        <?= $selectedSchedule
                            ? 'Edit Admit Card Schedule'
                            : 'Create Admit Card Schedule'
                        ?>

                    </span>

                </div>


                <form
                    method="POST"
                    action="<?= APP_URL ?>/admin/actions/admit_card_action.php"
                >


                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(
                            $csrfToken
                        ) ?>"
                    >


                    <input
                        type="hidden"
                        name="action"
                        value="save_schedule"
                    >


                    <?php if ($selectedSchedule): ?>

                        <input
                            type="hidden"
                            name="schedule_id"
                            value="<?= (int)
                                $selectedSchedule['id']
                            ?>"
                        >

                    <?php endif; ?>


                    <div class="row g-3">


                        <!-- Admission Year -->

                        <div class="col-md-4">


                            <label class="admin-form-label">

                                Admission Year

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <select
                                name="admission_year"
                                class="form-select admin-form-select"
                                required
                            >


                                <?php

                                $scheduleYear =
                                    $selectedSchedule[
                                        'admission_year'
                                    ]
                                    ?? $currentYear;

                                ?>


                                <?php for (
                                    $year =
                                        $currentYear - 1;

                                    $year <=
                                        $currentYear + 2;

                                    $year++
                                ): ?>


                                    <option
                                        value="<?= $year ?>"
                                        <?= (
                                            (int)
                                            $scheduleYear
                                            === $year
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >

                                        <?= $year ?>

                                    </option>


                                <?php endfor; ?>


                            </select>


                        </div>



                        <!-- Department -->

                        <div class="col-md-8">


                            <label class="admin-form-label">

                                Department

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <select
                                name="department_id"
                                class="form-select admin-form-select"
                                required
                            >


                                <option value="">

                                    Select Department

                                </option>


                                <?php foreach (
                                    $departments
                                    as $department
                                ): ?>


                                    <option
                                        value="<?= (int)
                                            $department['id']
                                        ?>"
                                        <?= (
                                            $selectedSchedule &&

                                            (int)
                                            $selectedSchedule[
                                                'department_id'
                                            ]
                                            ===
                                            (int)
                                            $department['id']
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
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



                        <!-- Exam Date -->

                        <div class="col-md-4">


                            <label class="admin-form-label">

                                Exam Date

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <input
                                type="date"
                                name="exam_date"
                                class="form-control admin-form-control"
                                value="<?= e(
                                    $selectedSchedule[
                                        'exam_date'
                                    ]
                                    ?? ''
                                ) ?>"
                                required
                            >


                        </div>



                        <!-- Shift -->

                        <div class="col-md-8">


                            <label class="admin-form-label">

                                Shift / Time

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                name="exam_shift"
                                class="form-control admin-form-control"
                                value="<?= e(
                                    $selectedSchedule[
                                        'exam_shift'
                                    ]
                                    ?? ''
                                ) ?>"
                                placeholder="Example: Morning — 10:00 AM - 12:00 PM"
                                maxlength="100"
                                required
                            >


                        </div>



                        <!-- Center -->

                        <div class="col-12">


                            <label class="admin-form-label">

                                Exam Center / Location

                                <span class="text-danger">
                                    *
                                </span>

                            </label>


                            <textarea
                                name="exam_center"
                                class="form-control admin-form-control"
                                rows="3"
                                maxlength="255"
                                placeholder="Example: DUET Main Campus, Gazipur"
                                required
                            ><?= e(
                                $selectedSchedule[
                                    'exam_center'
                                ]
                                ?? ''
                            ) ?></textarea>


                        </div>



                        <!-- Status -->

                        <div class="col-md-4">


                            <label class="admin-form-label">

                                Schedule Status

                            </label>


                            <select
                                name="status"
                                class="form-select admin-form-select"
                            >


                                <option
                                    value="draft"
                                    <?= (
                                        (
                                            $selectedSchedule[
                                                'status'
                                            ]
                                            ?? 'draft'
                                        )
                                        === 'draft'
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    Draft

                                </option>


                                <option
                                    value="published"
                                    <?= (
                                        (
                                            $selectedSchedule[
                                                'status'
                                            ]
                                            ?? ''
                                        )
                                        === 'published'
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    Published

                                </option>


                            </select>


                        </div>



                        <!-- Explanation -->

                        <div class="col-md-8 d-flex align-items-end">


                            <div class="publish-info w-100">

                                <i class="bi bi-info-circle"></i>

                                When published, admit cards will be
                                generated automatically for applicants
                                whose payment has been verified as
                                <strong>Paid</strong>.

                            </div>


                        </div>



                        <!-- Buttons -->

                        <div class="col-12">


                            <hr>


                            <button
                                type="submit"
                                class="btn btn-admin"
                            >

                                <i class="bi bi-save"></i>

                                Save Schedule

                            </button>


                            <?php if (
                                $selectedSchedule
                            ): ?>


                                <a
                                    href="<?= APP_URL ?>/admin/admit-cards.php"
                                    class="btn btn-outline-secondary ms-2"
                                >

                                    Cancel

                                </a>


                            <?php endif; ?>


                        </div>


                    </div>


                </form>


            </div>



            <!-- =================================================
                 SEARCH
            ================================================== -->

            <div class="admin-card mb-4">


                <div class="admin-card-title">

                    <span>

                        <i class="bi bi-search"></i>

                        Search Schedules

                    </span>

                </div>


                <form
                    method="GET"
                    action="<?= APP_URL ?>/admin/admit-cards.php"
                    class="row g-3"
                >


                    <div class="col-md-9">


                        <input
                            type="text"
                            name="search"
                            class="form-control admin-form-control"
                            value="<?= e(
                                $search
                            ) ?>"
                            placeholder="Department code, department name or admission year"
                        >


                    </div>


                    <div class="col-md-3">


                        <button
                            type="submit"
                            class="btn btn-admin w-100"
                        >

                            <i class="bi bi-search"></i>

                            Search

                        </button>


                    </div>


                </form>


            </div>



            <!-- =================================================
                 SCHEDULE LIST
            ================================================== -->

            <div class="admin-card">


                <div class="admin-card-title">


                    <span>

                        <i class="bi bi-calendar3"></i>

                        Department Schedules

                    </span>


                    <span class="badge bg-success">

                        <?= count(
                            $schedules
                        ) ?>

                    </span>


                </div>



                <?php if (
                    empty($schedules)
                ): ?>


                    <div class="admin-empty">


                        <i class="bi bi-calendar-x fs-1"></i>


                        <p class="mt-2 mb-0">

                            No admit card schedule found.

                        </p>


                    </div>


                <?php else: ?>


                    <div class="table-responsive">


                        <table class="table align-middle">


                            <thead>


                            <tr>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Exam Date
                                </th>

                                <th>
                                    Shift / Time
                                </th>

                                <th>
                                    Center
                                </th>

                                <th>
                                    Paid
                                </th>

                                <th>
                                    Published
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>


                            </thead>


                            <tbody>


                            <?php foreach (
                                $schedules
                                as $schedule
                            ): ?>


                                <tr>


                                    <!-- Department -->

                                    <td>


                                        <strong>

                                            <?= e(
                                                $schedule[
                                                    'department_code'
                                                ]
                                            ) ?>

                                        </strong>


                                        <div class="admin-small-text">

                                            <?= e(
                                                $schedule[
                                                    'department_name'
                                                ]
                                            ) ?>

                                        </div>


                                        <div class="admin-small-text">

                                            Admission Year:

                                            <?= e(
                                                $schedule[
                                                    'admission_year'
                                                ]
                                            ) ?>

                                        </div>


                                    </td>



                                    <!-- Date -->

                                    <td>


                                        <?php

                                        $timestamp =
                                            strtotime(
                                                $schedule[
                                                    'exam_date'
                                                ]
                                            );

                                        echo $timestamp

                                            ? e(
                                                date(
                                                    'd M Y',
                                                    $timestamp
                                                )
                                            )

                                            : '-';

                                        ?>


                                    </td>



                                    <!-- Shift -->

                                    <td>

                                        <?= e(
                                            $schedule[
                                                'exam_shift'
                                            ]
                                        ) ?>

                                    </td>



                                    <!-- Center -->

                                    <td>

                                        <?= e(
                                            $schedule[
                                                'exam_center'
                                            ]
                                        ) ?>

                                    </td>



                                    <!-- Paid -->

                                    <td>


                                        <span
                                            class="badge bg-success"
                                        >

                                            <?= (int)
                                                $schedule[
                                                    'paid_applicants'
                                                ] ?>

                                            Paid

                                        </span>


                                    </td>



                                    <!-- Published -->

                                    <td>


                                        <span
                                            class="badge bg-primary"
                                        >

                                            <?= (int)
                                                $schedule[
                                                    'published_cards'
                                                ] ?>

                                        </span>


                                    </td>



                                    <!-- Status -->

                                    <td>


                                        <?php if (
                                            $schedule[
                                                'status'
                                            ]
                                            === 'published'
                                        ): ?>


                                            <span
                                                class="badge bg-success"
                                            >

                                                Published

                                            </span>


                                        <?php else: ?>


                                            <span
                                                class="badge bg-secondary"
                                            >

                                                Draft

                                            </span>


                                        <?php endif; ?>


                                    </td>



                                    <!-- Actions -->

                                    <td>


                                        <a
                                            href="<?= APP_URL ?>/admin/admit-cards.php?schedule_id=<?= (int)$schedule['id'] ?>"
                                            class="btn btn-sm btn-admin mb-1"
                                        >

                                            <i class="bi bi-pencil-square"></i>

                                            Edit

                                        </a>



                                        <?php if (
                                            $schedule[
                                                'status'
                                            ]
                                            === 'published'
                                        ): ?>


                                            <form
                                                method="POST"
                                                action="<?= APP_URL ?>/admin/actions/admit_card_action.php"
                                                class="d-inline"
                                            >


                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e(
                                                        $csrfToken
                                                    ) ?>"
                                                >


                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="publish_schedule"
                                                >


                                                <input
                                                    type="hidden"
                                                    name="schedule_id"
                                                    value="<?= (int)
                                                        $schedule['id']
                                                    ?>"
                                                >


                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-success mb-1"
                                                    onclick="return confirm('Sync all paid applicants for this department?');"
                                                >

                                                    <i class="bi bi-arrow-repeat"></i>

                                                    Sync Paid

                                                </button>


                                            </form>


                                        <?php else: ?>


                                            <form
                                                method="POST"
                                                action="<?= APP_URL ?>/admin/actions/admit_card_action.php"
                                                class="d-inline"
                                            >


                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e(
                                                        $csrfToken
                                                    ) ?>"
                                                >


                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="publish_schedule"
                                                >


                                                <input
                                                    type="hidden"
                                                    name="schedule_id"
                                                    value="<?= (int)
                                                        $schedule['id']
                                                    ?>"
                                                >


                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-success mb-1"
                                                    onclick="return confirm('Publish admit cards for all paid applicants of this department?');"
                                                >

                                                    <i class="bi bi-send"></i>

                                                    Publish

                                                </button>


                                            </form>


                                        <?php endif; ?>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                            </tbody>


                        </table>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </main>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>