<?php

/*
|--------------------------------------------------------------------------
| ELIGIBILITY ACTION
|--------------------------------------------------------------------------
| Process eligibility check.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';


// --------------------------------------------------
// Only POST Request
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(APP_URL . '/eligibility.php');

}


// --------------------------------------------------
// Get Form Data
// --------------------------------------------------

$sscGpa = trim($_POST['ssc_gpa'] ?? '');

$diplomaCgpa = trim($_POST['diploma_cgpa'] ?? '');

$diplomaPassingYear =
    trim($_POST['diploma_passing_year'] ?? '');

$technologyId =
    (int) ($_POST['technology_id'] ?? 0);


// --------------------------------------------------
// Basic Validation
// --------------------------------------------------

if (
    $sscGpa === '' ||
    $diplomaCgpa === '' ||
    $diplomaPassingYear === '' ||
    $technologyId <= 0
) {

    setFlashMessage(
        'danger',
        'Please fill in all required fields.'
    );

    redirect(APP_URL . '/eligibility.php');

}


// --------------------------------------------------
// Convert Values
// --------------------------------------------------

$sscGpa = (float) $sscGpa;

$diplomaCgpa = (float) $diplomaCgpa;

$diplomaPassingYear =
    (int) $diplomaPassingYear;


// --------------------------------------------------
// Validate GPA / CGPA Range
// --------------------------------------------------

if ($sscGpa < 0 || $sscGpa > 5) {

    setFlashMessage(
        'danger',
        'SSC GPA must be between 0 and 5.'
    );

    redirect(APP_URL . '/eligibility.php');

}


if ($diplomaCgpa < 0 || $diplomaCgpa > 4) {

    setFlashMessage(
        'danger',
        'Diploma CGPA must be between 0 and 4.'
    );

    redirect(APP_URL . '/eligibility.php');

}


// --------------------------------------------------
// Validate Passing Year
// --------------------------------------------------
//
// IMPORTANT:
// Keep the existing project year logic unchanged.
//
// Dropdown:
// Current Year - 1
// Current Year - 2
//
// Both are accepted by the existing project rule.
//

$currentYear = (int) date('Y');

$allowedYears = [
    $currentYear - 1,
    $currentYear - 2
];


if (!in_array(
    $diplomaPassingYear,
    $allowedYears,
    true
)) {

    setFlashMessage(
        'danger',
        'Invalid diploma passing year.'
    );

    redirect(APP_URL . '/eligibility.php');

}


// --------------------------------------------------
// Validate Technology
// --------------------------------------------------

$sql = "
    SELECT
        id,
        name
    FROM diploma_technologies
    WHERE id = ?
    AND is_active = 1
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $technologyId
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    setFlashMessage(
        'danger',
        'Invalid diploma technology selected.'
    );

    redirect(APP_URL . '/eligibility.php');

}


$technology = $result->fetch_assoc();

$stmt->close();


// --------------------------------------------------
// Prepare Result
// --------------------------------------------------

$eligible = true;

$reasons = [];


// --------------------------------------------------
// Check SSC GPA
// --------------------------------------------------

if ($sscGpa < 3.00) {

    $eligible = false;

    $reasons[] =
        'SSC / Equivalent GPA must be at least 3.00.';

}


// --------------------------------------------------
// Check Diploma CGPA
// --------------------------------------------------

if ($diplomaCgpa < 3.00) {

    $eligible = false;

    $reasons[] =
        'Diploma CGPA must be at least 3.00.';

}


// --------------------------------------------------
// Check Passing Year
// --------------------------------------------------
//
// Keep existing project logic.
//
// Current Year - 1 and
// Current Year - 2
// are accepted.
//

if (!in_array(
    $diplomaPassingYear,
    $allowedYears,
    true
)) {

    $eligible = false;

    $reasons[] =
        'Invalid diploma passing year.';

}


// --------------------------------------------------
// Find Eligible Departments
// --------------------------------------------------
//
// IMPORTANT:
//
// Department eligibility is NOT hard-coded here.
//
// It comes directly from:
//
// technology_department_eligibility
//
// So the corrected database mapping will automatically
// work for all 10 DUET departments.
//

$departments = [];

$sql = "
    SELECT
        d.id,
        d.code,
        d.name,
        d.faculty,
        d.degree,
        d.duration,
        d.seats

    FROM technology_department_eligibility tde

    INNER JOIN departments d
        ON tde.department_id = d.id

    WHERE tde.technology_id = ?

    AND d.is_active = 1

    ORDER BY d.name ASC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $technologyId
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    $departments[] = $row;

}

$stmt->close();


// --------------------------------------------------
// If Technology Has No Department
// --------------------------------------------------

if (empty($departments)) {

    $eligible = false;

    $reasons[] =
        'No eligible DUET department was found for the selected diploma technology.';

}


// --------------------------------------------------
// Save Result in Session
// --------------------------------------------------

$_SESSION['eligibility_result'] = [

    'eligible' => $eligible,

    'ssc_gpa' => $sscGpa,

    'diploma_cgpa' => $diplomaCgpa,

    'diploma_passing_year' => $diplomaPassingYear,

    'technology_id' => $technologyId,

    'technology_name' => $technology['name'],

    'departments' => $eligible
        ? $departments
        : [],

    'reasons' => $reasons

];


// --------------------------------------------------
// Redirect Back
// --------------------------------------------------

redirect(APP_URL . '/eligibility.php');