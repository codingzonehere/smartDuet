<?php

/*
|--------------------------------------------------------------------------
| NOTICES
|--------------------------------------------------------------------------
| Applicant Admission Notices
|--------------------------------------------------------------------------
*/


// --------------------------------------------------
// Authentication + Common Functions
// --------------------------------------------------

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';


// --------------------------------------------------
// Get Logged-in User ID
// --------------------------------------------------

$userId = getUserId();


// --------------------------------------------------
// Current Admission Year
// --------------------------------------------------

$currentYear = (int) date('Y');


// ==================================================
// PDF VIEWER
// ==================================================
//
// When applicant clicks "View Notice",
// the PDF is opened in a new browser tab.
//
// Content-Disposition: inline
// means browser should DISPLAY the PDF,
// not force a download.
//
// Notice files are stored in:
// assets/uploads/notices/
// ==================================================

if (isset($_GET['view']) && $_GET['view'] !== '') {

    $fileName = basename($_GET['view']);

    $noticeDirectory = __DIR__ . '/assets/uploads/notices/';

    $filePath = $noticeDirectory . $fileName;


    // --------------------------------------------------
    // Security: Only PDF files are allowed
    // --------------------------------------------------

    $extension = strtolower(
        pathinfo($fileName, PATHINFO_EXTENSION)
    );

    if ($extension !== 'pdf') {

        http_response_code(400);

        exit('Invalid notice file.');

    }


    // --------------------------------------------------
    // Check File Exists
    // --------------------------------------------------

    if (!is_file($filePath)) {

        http_response_code(404);

        exit('Notice file not found.');

    }


    // --------------------------------------------------
    // Send PDF to Browser
    // --------------------------------------------------

    header('Content-Type: application/pdf');

    header(
        'Content-Disposition: inline; filename="' .
        str_replace('"', '', $fileName) .
        '"'
    );

    header('Content-Length: ' . filesize($filePath));

    header('Cache-Control: private, max-age=0, must-revalidate');

    header('Pragma: public');

    readfile($filePath);

    exit;
}


// ==================================================
// SEARCH
// ==================================================

$search = trim($_GET['search'] ?? '');


// ==================================================
// GET PUBLISHED NOTICES
// ==================================================
//
// Only published notices are visible.
//
// Current admission year notices and general notices
// are displayed.
//
// Latest notice appears first.
// ==================================================

$sql = "
    SELECT
        id,
        title,
        description,
        pdf_file,
        admission_year,
        published_date
    FROM notices
    WHERE status = 'published'
      AND (
            admission_year = ?
            OR admission_year IS NULL
          )
";

$params = [$currentYear];

$types = "i";


if ($search !== '') {

    $sql .= "
        AND (
            title LIKE ?
            OR description LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ss";
}


$sql .= "
    ORDER BY published_date DESC, id DESC
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die('Failed to load notices.');

}


$stmt->bind_param(
    $types,
    ...$params
);


$stmt->execute();


$result = $stmt->get_result();


$notices = [];


while ($row = $result->fetch_assoc()) {

    $notices[] = $row;

}


$stmt->close();


// --------------------------------------------------
// Page Title
// --------------------------------------------------

$pageTitle = 'Admission Notices';


// --------------------------------------------------
// Common Header + Sidebar
// --------------------------------------------------

require_once __DIR__ . '/includes/header.php';

?>


<!-- =====================================================
     NOTICE PAGE
====================================================== -->

<div class="content-area">


    <!-- =================================================
         PAGE HEADER
    ================================================== -->

    <div class="welcome-box mb-4">

        <div>

            <h3 class="fw-bold mb-2">

                Admission Notices

            </h3>

            <p class="mb-0">

                View the latest DUET admission notices
                and important updates.

            </p>

        </div>


        <div class="stat-icon">

            <i class="bi bi-megaphone"></i>

        </div>

    </div>



    <!-- =================================================
         SEARCH + NOTICE LIST
    ================================================== -->

    <div class="row g-4">


        <!-- =================================================
             NOTICE SECTION
        ================================================== -->

        <div class="col-12">

            <div class="custom-card">


                <!-- -----------------------------------------
                     CARD HEADER
                ------------------------------------------ -->

                <div
                    class="d-flex justify-content-between
                           align-items-center
                           flex-wrap gap-2 mb-4"
                >

                    <div>

                        <h5 class="section-title mb-1">

                            Latest Notices

                        </h5>

                        <p class="small text-muted mb-0">

                            Official admission-related
                            announcements.

                        </p>

                    </div>


                    <span class="badge bg-primary">

                        <?= count($notices) ?> Notice<?= count($notices) !== 1 ? 's' : '' ?>

                    </span>

                </div>



                <!-- -----------------------------------------
                     SEARCH
                ------------------------------------------ -->

                <form
                    method="GET"
                    action="notices.php"
                    class="mb-4"
                >

                    <div class="row g-2">


                        <div class="col-md-9">

                            <div class="input-group">

                                <span class="input-group-text bg-white">

                                    <i class="bi bi-search"></i>

                                </span>

                                <input
                                    type="text"
                                    name="search"
                                    value="<?= e($search) ?>"
                                    class="form-control"
                                    placeholder="Search notice..."
                                >

                            </div>

                        </div>


                        <div class="col-md-3">

                            <div class="d-grid">

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >

                                    <i class="bi bi-search me-1"></i>

                                    Search

                                </button>

                            </div>

                        </div>


                    </div>


                    <?php if ($search !== ''): ?>

                        <div class="mt-2">

                            <a
                                href="<?= APP_URL ?>/notices.php"
                                class="small text-decoration-none"
                            >

                                <i class="bi bi-x-circle me-1"></i>

                                Clear Search

                            </a>

                        </div>

                    <?php endif; ?>

                </form>



                <!-- -----------------------------------------
                     NOTICE LIST
                ------------------------------------------ -->

                <?php if (count($notices) > 0): ?>


                    <?php foreach ($notices as $notice): ?>


                        <div class="notice-item">


                            <div class="d-flex gap-3">


                                <!-- Notice Icon -->

                                <div class="stat-icon">

                                    <i class="bi bi-file-earmark-text"></i>

                                </div>



                                <!-- Notice Information -->

                                <div class="flex-grow-1">


                                    <div
                                        class="d-flex
                                               justify-content-between
                                               align-items-start
                                               gap-3
                                               flex-wrap"
                                    >


                                        <div>

                                            <h6 class="mb-1 fw-semibold">

                                                <?= e($notice['title']) ?>

                                            </h6>


                                            <div class="small text-muted">

                                                <i class="bi bi-calendar3 me-1"></i>

                                                Published:

                                                <?php if (!empty($notice['published_date'])): ?>

                                                    <?= e(
                                                        date(
                                                            'd M Y',
                                                            strtotime(
                                                                $notice['published_date']
                                                            )
                                                        )
                                                    ) ?>

                                                <?php else: ?>

                                                    —

                                                <?php endif; ?>

                                            </div>

                                        </div>



                                        <span class="badge bg-success">

                                            Published

                                        </span>


                                    </div>



                                    <!-- Description -->

                                    <?php if (!empty($notice['description'])): ?>

                                        <p class="small text-muted mb-2 mt-2">

                                            <?= e($notice['description']) ?>

                                        </p>

                                    <?php endif; ?>



                                    <!-- Bottom Information -->

                                    <div
                                        class="d-flex
                                               justify-content-between
                                               align-items-center
                                               flex-wrap
                                               gap-2
                                               mt-2"
                                    >


                                        <span class="notice-date">

                                            <i class="bi bi-mortarboard me-1"></i>

                                            Admission Year:

                                            <strong>

                                                <?= e(
                                                    $notice['admission_year']
                                                    ?? $currentYear
                                                ) ?>

                                            </strong>

                                        </span>



                                        <!-- ---------------------------------
                                             VIEW PDF
                                        ---------------------------------- -->

                                        <?php if (!empty($notice['pdf_file'])): ?>

                                            <?php

                                            /*
                                            |--------------------------------------------------------------------------
                                            | PDF FILE
                                            |--------------------------------------------------------------------------
                                            | Only filename is used.
                                            |
                                            | Example:
                                            | admission_notice.pdf
                                            |
                                            | The actual file is located at:
                                            |
                                            | assets/uploads/notices/
                                            |
                                            | We send the request back to this
                                            | same page using ?view=filename.
                                            |
                                            | The PDF viewer section at the top
                                            | sends Content-Disposition: inline.
                                            |--------------------------------------------------------------------------
                                            */

                                            $noticeFile =
                                                basename(
                                                    $notice['pdf_file']
                                                );

                                            $viewUrl =
                                                APP_URL .
                                                '/notices.php?view=' .
                                                rawurlencode(
                                                    $noticeFile
                                                );

                                            ?>


                                            <a
                                                href="<?= e($viewUrl) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-sm btn-outline-primary"
                                            >

                                                <i
                                                    class="bi bi-file-earmark-pdf me-1"
                                                ></i>

                                                View Notice

                                            </a>

                                        <?php endif; ?>


                                    </div>


                                </div>


                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- =====================================
                         NO NOTICE
                    ====================================== -->

                    <div class="text-center py-5">


                        <div class="stat-icon mx-auto mb-3">

                            <i class="bi bi-bell-slash"></i>

                        </div>


                        <?php if ($search !== ''): ?>

                            <h6 class="fw-semibold">

                                No Matching Notice Found

                            </h6>

                            <p class="small text-muted mb-0">

                                No published notice matched
                                your search.

                            </p>

                        <?php else: ?>

                            <h6 class="fw-semibold">

                                No Notices Available

                            </h6>

                            <p class="small text-muted mb-0">

                                There are currently no published
                                admission notices.

                            </p>

                        <?php endif; ?>


                    </div>


                <?php endif; ?>


            </div>

        </div>


    </div>


</div>


<?php

/*
|--------------------------------------------------------------------------
| COMMON FOOTER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/footer.php';

?>