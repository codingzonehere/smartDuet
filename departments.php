<?php

/*
|--------------------------------------------------------------------------
| DEPARTMENT INFORMATION
|--------------------------------------------------------------------------
| Applicant Department Information
|
| Department data is loaded dynamically from the database.
|--------------------------------------------------------------------------
*/


// --------------------------------------------------
// Authentication + Common Functions
// --------------------------------------------------

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';


// --------------------------------------------------
// Page Title
// --------------------------------------------------

$pageTitle = 'Department Information';


// --------------------------------------------------
// Current Admission Information
// --------------------------------------------------

$admissionYear = (int) date('Y');

$session = $admissionYear . '-' . ($admissionYear + 1);


// ==================================================
// GET ACTIVE DEPARTMENTS FROM DATABASE
// ==================================================

$departments = [];

$sql = "
    SELECT
        id,
        code,
        name,
        faculty,
        degree,
        duration,
        seats
    FROM departments
    WHERE is_active = 1
    ORDER BY id ASC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die('Failed to load departments.');

}

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    /*
    |--------------------------------------------------------------------------
    | Department Icon
    |--------------------------------------------------------------------------
    | Icon is selected according to department code.
    |--------------------------------------------------------------------------
    */

    switch ($row['code']) {

        case 'CE':

            $row['icon'] = 'bi-building';

            break;


        case 'EEE':

            $row['icon'] = 'bi-lightning-charge';

            break;


        case 'CSE':

            $row['icon'] = 'bi-cpu';

            break;


        case 'ME':

            $row['icon'] = 'bi-gear';

            break;


        case 'TE':

            $row['icon'] = 'bi-grid-3x3';

            break;


        case 'Arch':

            $row['icon'] = 'bi-house';

            break;


        case 'IPE':

            $row['icon'] = 'bi-diagram-3';

            break;


        case 'MME':

            $row['icon'] = 'bi-box-seam';

            break;


        case 'ChE':

            $row['icon'] = 'bi-droplet';

            break;


        case 'FE':

            $row['icon'] = 'bi-cup-hot';

            break;


        default:

            $row['icon'] = 'bi-building';

            break;

    }


    /*
    |--------------------------------------------------------------------------
    | Faculty Color Class
    |--------------------------------------------------------------------------
    |
    | Civil      -> Blue
    | EEE        -> Green
    | Mechanical -> Orange
    |--------------------------------------------------------------------------
    */

    $facultyName = strtolower(
        trim($row['faculty'])
    );


    if (
        strpos(
            $facultyName,
            'civil engineering'
        ) !== false
        &&
        strpos(
            $facultyName,
            'electrical'
        ) === false
    ) {

        $row['faculty_class'] = 'civil';

    } elseif (
        strpos(
            $facultyName,
            'electrical and electronic engineering'
        ) !== false
    ) {

        $row['faculty_class'] = 'eee';

    } else {

        $row['faculty_class'] = 'mechanical';

    }


    /*
    |--------------------------------------------------------------------------
    | Faculty Filter Value
    |--------------------------------------------------------------------------
    */

    $row['faculty_filter'] =
        strtolower(
            preg_replace(
                '/[^a-z0-9]+/i',
                '-',
                $row['faculty']
            )
        );


    $departments[] = $row;

}


$stmt->close();


// ==================================================
// CALCULATE TOTAL SEATS
// ==================================================

$totalSeats = 0;


foreach ($departments as $department) {

    $totalSeats += (int) $department['seats'];

}


// ==================================================
// GET UNIQUE FACULTIES
// ==================================================

$faculties = [];


foreach ($departments as $department) {

    $faculty = $department['faculty'];

    $filterValue = $department['faculty_filter'];


    if (!isset($faculties[$filterValue])) {

        $faculties[$filterValue] = $faculty;

    }

}


// ==================================================
// COMMON HEADER
// ==================================================

require_once __DIR__ . '/includes/header.php';

?>


<style>

/* =========================================================
   FACULTY WISE DEPARTMENT CARD COLORS
========================================================= */


/* ---------------------------------------------------------
   Common Department Card
--------------------------------------------------------- */

.department-card .custom-card {

    border-top: 4px solid transparent;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;

}


.department-card .custom-card:hover {

    transform: translateY(-3px);

    box-shadow:
        0 8px 22px rgba(0, 0, 0, 0.07);

}


/* ---------------------------------------------------------
   Civil Engineering
   Blue
--------------------------------------------------------- */

.department-card.faculty-civil .custom-card {

    border-top-color: #0d6efd;

}


.department-card.faculty-civil .stat-icon {

    background: #eaf2ff;

    color: #0d6efd;

}


.department-card.faculty-civil .department-code {

    background: #eaf2ff !important;

    color: #0d6efd !important;

}


/* ---------------------------------------------------------
   Electrical & Electronic Engineering
   Green
--------------------------------------------------------- */

.department-card.faculty-eee .custom-card {

    border-top-color: #198754;

}


.department-card.faculty-eee .stat-icon {

    background: #e9f7ef;

    color: #198754;

}


.department-card.faculty-eee .department-code {

    background: #e9f7ef !important;

    color: #198754 !important;

}


/* ---------------------------------------------------------
   Mechanical Engineering
   Orange
--------------------------------------------------------- */

.department-card.faculty-mechanical .custom-card {

    border-top-color: #fd7e14;

}


.department-card.faculty-mechanical .stat-icon {

    background: #fff1e6;

    color: #fd7e14;

}


.department-card.faculty-mechanical .department-code {

    background: #fff1e6 !important;

    color: #fd7e14 !important;

}


/* ---------------------------------------------------------
   Mobile
--------------------------------------------------------- */

@media (max-width: 768px) {

    .department-card .custom-card {

        border-top-width: 4px;

    }

}

</style>


<!-- =====================================================
     DEPARTMENT PAGE
====================================================== -->

<div class="content-area">


    <!-- =================================================
         BLUE HERO SECTION
    ================================================== -->

    <div class="welcome-box mb-4">

        <div class="row align-items-center">

            <div class="col-md-9">

                <h2 class="fw-bold mb-2">

                    Departments & Programs

                </h2>

                <p class="mb-0">

                    Explore undergraduate programs, faculties,
                    degree duration and available seats.

                </p>

            </div>


            <div class="col-md-3 text-md-end d-none d-md-block">

                <i
                    class="bi bi-building"
                    style="font-size: 58px; opacity: .25;"
                >
                </i>

            </div>

        </div>

    </div>



    <!-- =================================================
         SUMMARY CARDS
    ================================================== -->

    <div class="row g-3 mb-4">


        <!-- Total Departments -->

        <div class="col-sm-6 col-lg-4">

            <div class="stat-card">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="stat-label">

                            Admission Departments

                        </div>

                        <div class="stat-number">

                            <?= count($departments); ?>

                        </div>

                    </div>


                    <div class="stat-icon">

                        <i class="bi bi-building"></i>

                    </div>

                </div>

            </div>

        </div>



        <!-- Total Seats -->

        <div class="col-sm-6 col-lg-4">

            <div class="stat-card">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="stat-label">

                            Total Seats

                        </div>

                        <div class="stat-number">

                            <?= $totalSeats; ?>

                        </div>

                    </div>


                    <div class="stat-icon">

                        <i class="bi bi-people"></i>

                    </div>

                </div>

            </div>

        </div>



        <!-- Admission Session -->

        <div class="col-sm-6 col-lg-4">

            <div class="stat-card">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="stat-label">

                            Admission Session

                        </div>

                        <div
                            class="stat-number"
                            style="font-size: 22px;"
                        >

                            <?= e($session); ?>

                        </div>

                    </div>


                    <div class="stat-icon">

                        <i class="bi bi-calendar-event"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =================================================
         SEARCH + FACULTY FILTER
    ================================================== -->

    <div class="custom-card mb-4">

        <div class="row g-3">


            <!-- Search -->

            <div class="col-lg-7">

                <div class="input-group">

                    <span class="input-group-text bg-white">

                        <i class="bi bi-search"></i>

                    </span>


                    <input
                        type="text"
                        id="departmentSearch"
                        class="form-control"
                        placeholder="Search department..."
                        onkeyup="filterDepartments()"
                    >

                </div>

            </div>



            <!-- Faculty Filter -->

            <div class="col-lg-5">

                <select
                    id="facultyFilter"
                    class="form-select"
                    onchange="filterDepartments()"
                >

                    <option value="">

                        All Faculties

                    </option>


                    <?php foreach ($faculties as $value => $faculty): ?>

                        <option value="<?= e($value); ?>">

                            <?= e($faculty); ?>

                        </option>

                    <?php endforeach; ?>


                </select>

            </div>

        </div>

    </div>



    <!-- =================================================
         DEPARTMENT LIST
    ================================================== -->

    <div
        class="row g-4"
        id="departmentList"
    >


        <?php foreach ($departments as $department): ?>


            <?php

            /*
            |--------------------------------------------------------------------------
            | Searchable Text
            |--------------------------------------------------------------------------
            */

            $searchText = strtolower(
                $department['name'] . ' ' .
                $department['code'] . ' ' .
                $department['faculty']
            );

            ?>


            <!-- =================================================
                 DEPARTMENT CARD
            ================================================== -->

            <div
                class="col-md-6 col-xl-4
                       department-card
                       faculty-<?= e($department['faculty_class']); ?>"
                data-search="<?= e($searchText); ?>"
                data-faculty="<?= e($department['faculty_filter']); ?>"
            >

                <div class="custom-card h-100">


                    <!-- -----------------------------------------
                         CARD TOP
                    ------------------------------------------ -->

                    <div
                        class="d-flex
                               justify-content-between
                               align-items-start
                               mb-3"
                    >


                        <!-- Department Icon -->

                        <div class="stat-icon">

                            <i
                                class="bi
                                <?= e($department['icon']); ?>"
                            >
                            </i>

                        </div>


                        <!-- Department Code -->

                        <span
                            class="badge
                                   department-code
                                   px-3 py-2"
                        >

                            <?= e($department['code']); ?>

                        </span>


                    </div>



                    <!-- -----------------------------------------
                         Department Name
                    ------------------------------------------ -->

                    <h5 class="fw-bold mb-2">

                        <?= e($department['name']); ?>

                    </h5>



                    <!-- -----------------------------------------
                         Faculty
                    ------------------------------------------ -->

                    <p class="small text-muted mb-3">

                        <?= e($department['faculty']); ?>

                    </p>



                    <!-- -----------------------------------------
                         Department Information
                    ------------------------------------------ -->

                    <div class="border-top pt-3">


                        <!-- Degree -->

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-start
                                   gap-3
                                   mb-2"
                        >

                            <span class="text-muted small">

                                Degree

                            </span>


                            <strong
                                class="small text-end"
                            >

                                <?= e($department['degree']); ?>

                            </strong>

                        </div>



                        <!-- Duration -->

                        <div
                            class="d-flex
                                   justify-content-between
                                   mb-2"
                        >

                            <span class="text-muted small">

                                Duration

                            </span>


                            <strong class="small">

                                <?= e($department['duration']); ?>

                            </strong>

                        </div>



                        <!-- Available Seats -->

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-center"
                        >

                            <span class="text-muted small">

                                Available Seats

                            </span>


                            <span class="fw-bold text-primary">

                                <?= (int) $department['seats']; ?>

                                Seats

                            </span>

                        </div>


                    </div>


                </div>

            </div>


        <?php endforeach; ?>


    </div>



    <!-- =================================================
         NO SEARCH RESULT
    ================================================== -->

    <div
        id="noDepartmentResult"
        class="custom-card
               text-center
               py-5
               mt-4
               d-none"
    >

        <i
            class="bi bi-search text-muted"
            style="font-size: 40px;"
        >
        </i>


        <h6 class="mt-3">

            No department found

        </h6>


        <p class="text-muted mb-0">

            Try searching with another department name.

        </p>

    </div>



    <!-- =================================================
         INFORMATION NOTE
    ================================================== -->

    <div class="custom-card mt-4">

        <div class="d-flex gap-3">


            <div class="stat-icon">

                <i class="bi bi-info-circle"></i>

            </div>


            <div>

                <h6 class="fw-bold mb-1">

                    Admission Information

                </h6>


                <p class="text-muted small mb-0">

                    Department, program and seat information
                    is loaded from the admission database.
                    Future admission information can be
                    updated from the administration panel.

                </p>

            </div>


        </div>

    </div>


</div>



<!-- =====================================================
     DEPARTMENT SEARCH & FILTER
====================================================== -->

<script>

function filterDepartments() {

    // --------------------------------------------------
    // Get Search Text
    // --------------------------------------------------

    const searchText =
        document
        .getElementById('departmentSearch')
        .value
        .toLowerCase()
        .trim();


    // --------------------------------------------------
    // Get Selected Faculty
    // --------------------------------------------------

    const faculty =
        document
        .getElementById('facultyFilter')
        .value;


    // --------------------------------------------------
    // Get All Department Cards
    // --------------------------------------------------

    const cards =
        document.querySelectorAll(
            '.department-card'
        );


    let visibleCount = 0;


    // --------------------------------------------------
    // Filter Cards
    // --------------------------------------------------

    cards.forEach(function(card) {


        const searchData =
            card.getAttribute(
                'data-search'
            );


        const cardFaculty =
            card.getAttribute(
                'data-faculty'
            );


        const matchesSearch =
            searchData.includes(
                searchText
            );


        const matchesFaculty =
            faculty === '' ||
            cardFaculty === faculty;


        // --------------------------------------------------
        // Show / Hide
        // --------------------------------------------------

        if (
            matchesSearch &&
            matchesFaculty
        ) {

            card.style.display = '';

            visibleCount++;

        } else {

            card.style.display = 'none';

        }

    });


    // --------------------------------------------------
    // No Result Message
    // --------------------------------------------------

    const noResult =
        document.getElementById(
            'noDepartmentResult'
        );


    if (visibleCount === 0) {

        noResult.classList.remove(
            'd-none'
        );

    } else {

        noResult.classList.add(
            'd-none'
        );

    }

}

</script>



<?php

// =====================================================
// COMMON FOOTER
// =====================================================

require_once __DIR__ . '/includes/footer.php';

?>