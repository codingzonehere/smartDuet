<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Apply for Admission";

$userId = getUserId();

if (!$userId) {
    redirect(APP_URL . '/login.php');
}


/*
|--------------------------------------------------------------------------
| Get Current User Profile
|--------------------------------------------------------------------------
*/

$profile = [
    'full_name'       => '',
    'father_name'     => '',
    'mother_name'     => '',
    'mobile'          => '',
    'email'           => '',
    'date_of_birth'   => '',
    'gender'          => '',
    'identity_number' => ''
];

$stmt = $conn->prepare("
    SELECT
        u.email,
        u.mobile,
        a.full_name,
        a.father_name,
        a.mother_name,
        a.date_of_birth,
        a.gender,
        a.identity_number
    FROM users u
    LEFT JOIN applicants a
        ON a.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param('i', $userId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $profile = [
            'full_name'       => $row['full_name'] ?? '',
            'father_name'     => $row['father_name'] ?? '',
            'mother_name'     => $row['mother_name'] ?? '',
            'mobile'          => $row['mobile'] ?? '',
            'email'           => $row['email'] ?? '',
            'date_of_birth'   => $row['date_of_birth'] ?? '',
            'gender'          => $row['gender'] ?? '',
            'identity_number' => $row['identity_number'] ?? ''
        ];
    }

    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| Current Year
|--------------------------------------------------------------------------
*/

$currentYear = (int) date('Y');


/*
|--------------------------------------------------------------------------
| Diploma Passing Years
|--------------------------------------------------------------------------
*/

$diplomaYears = [
    $currentYear,
    $currentYear - 1,
    $currentYear - 2
];


/*
|--------------------------------------------------------------------------
| Get Active Diploma Technologies
|--------------------------------------------------------------------------
*/

$diplomaTechnologies = [];

$result = $conn->query("
    SELECT
        id,
        name
    FROM diploma_technologies
    WHERE is_active = 1
    ORDER BY name ASC
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $diplomaTechnologies[] = $row;

    }
}


/*
|--------------------------------------------------------------------------
| Get Technology → Department Mapping
|--------------------------------------------------------------------------
*/

$departmentData = [];

$result = $conn->query("
    SELECT
        t.id AS technology_id,
        t.name AS technology_name,

        d.id AS department_id,
        d.code,
        d.name AS department_name,
        d.faculty,
        d.degree,
        d.duration,
        d.seats

    FROM technology_department_eligibility tde

    INNER JOIN diploma_technologies t
        ON t.id = tde.technology_id

    INNER JOIN departments d
        ON d.id = tde.department_id

    WHERE t.is_active = 1
      AND d.is_active = 1

    ORDER BY
        t.name ASC,
        d.id ASC
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $technologyId = (int) $row['technology_id'];

        if (!isset($departmentData[$technologyId])) {

            $departmentData[$technologyId] = [];

        }

        $departmentData[$technologyId][] = [

            'id' =>
                (int) $row['department_id'],

            'code' =>
                $row['code'],

            'name' =>
                $row['department_name'],

            'faculty' =>
                $row['faculty'],

            'degree' =>
                $row['degree'],

            'duration' =>
                $row['duration'],

            'seats' =>
                (int) $row['seats']
        ];
    }
}


$departmentJson = json_encode(
    $departmentData,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);


require_once __DIR__ . '/includes/header.php';

?>

<style>

.apply-page {
    max-width: 1200px;
    margin: 0 auto;
}

.apply-hero {
    background: linear-gradient(
        135deg,
        #0d6efd 0%,
        #2563eb 50%,
        #1d4ed8 100%
    );
    color: #fff;
    border-radius: 16px;
    padding: 28px 30px;
    margin-bottom: 25px;
    box-shadow: 0 10px 25px rgba(37, 99, 235, .15);
}

.apply-hero h2 {
    font-weight: 700;
    margin-bottom: 6px;
}

.apply-hero p {
    margin-bottom: 0;
    opacity: .9;
}

.step-wrapper {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 25px;
    border: 1px solid #e5e7eb;
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.step-indicator::before {
    content: "";
    position: absolute;
    top: 20px;
    left: 7%;
    right: 7%;
    height: 2px;
    background: #e5e7eb;
    z-index: 0;
}

.step-item {
    position: relative;
    z-index: 1;
    text-align: center;
    flex: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    margin: auto;
    border-radius: 50%;
    background: #e5e7eb;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.step-item.active .step-number {
    background: #0d6efd;
    color: #fff;
}

.step-item.completed .step-number {
    background: #198754;
    color: #fff;
}

.step-label {
    margin-top: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
}

.step-item.active .step-label {
    color: #0d6efd;
}

.step-item.completed .step-label {
    color: #198754;
}

.form-step {
    display: none;
}

.form-step.active {
    display: block;
}

.form-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .04);
}

.section-title {
    color: #1e293b;
    font-weight: 700;
}

.section-title i {
    color: #0d6efd;
    margin-right: 8px;
}

.info-box {
    background: #eff6ff;
    border: 1px solid #dbeafe;
    border-radius: 10px;
    padding: 14px 16px;
    color: #1e40af;
}

.info-box i {
    margin-right: 5px;
}

.department-info-box {
    display: none;
    border-radius: 10px;
    padding: 15px;
    margin-top: 15px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.department-info-box.show {
    display: block;
}

.department-info-box.multiple {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1e40af;
}

.department-count {
    font-size: 17px;
    font-weight: 700;
}

.document-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
}

.review-box {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.review-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 12px 15px;
    border-bottom: 1px solid #e2e8f0;
}

.review-row:last-child {
    border-bottom: none;
}

.review-label {
    color: #64748b;
    font-weight: 600;
}

.review-value {
    font-weight: 600;
    color: #1e293b;
    text-align: right;
}

@media (max-width: 768px) {

    .apply-hero {
        padding: 22px;
    }

    .step-label {
        font-size: 11px;
    }

    .step-number {
        width: 34px;
        height: 34px;
        font-size: 13px;
    }

    .step-indicator::before {
        top: 17px;
    }

    .form-card {
        padding: 18px;
    }

    .review-row {
        flex-direction: column;
        gap: 4px;
    }

    .review-value {
        text-align: left;
    }
}

</style>


<div class="content-area">

<div class="apply-page">


<!-- =========================================================
     HERO
========================================================= -->

<div class="apply-hero">

    <h2>
        <i class="bi bi-file-earmark-text"></i>
        Apply for Admission
    </h2>

    <p>
        Complete your undergraduate admission application
        step by step.
    </p>

</div>


<!-- =========================================================
     STEP INDICATOR
========================================================= -->

<div class="step-wrapper">

<div class="step-indicator">

    <div class="step-item active" id="stepIndicator1">
        <div class="step-number">1</div>
        <div class="step-label">Personal</div>
    </div>

    <div class="step-item" id="stepIndicator2">
        <div class="step-number">2</div>
        <div class="step-label">Academic</div>
    </div>

    <div class="step-item" id="stepIndicator3">
        <div class="step-number">3</div>
        <div class="step-label">Department</div>
    </div>

    <div class="step-item" id="stepIndicator4">
        <div class="step-number">4</div>
        <div class="step-label">Documents</div>
    </div>

    <div class="step-item" id="stepIndicator5">
        <div class="step-number">5</div>
        <div class="step-label">Payment & Review</div>
    </div>

</div>

</div>


<!-- =========================================================
     APPLICATION FORM
========================================================= -->

<form
    action="<?= APP_URL ?>/actions/application_action.php"
    method="POST"
    enctype="multipart/form-data"
    id="applicationForm"
>


<!-- =========================================================
     STEP 1
========================================================= -->

<div class="form-step active" id="step1">

<div class="form-card">

<h3 class="section-title">
    <i class="bi bi-person"></i>
    Personal Information
</h3>

<div class="info-box mb-4">

    <i class="bi bi-info-circle"></i>

    Your profile information is automatically loaded
    from your account.

</div>


<div class="row g-4">

<div class="col-md-6">

<label class="form-label">
    Full Name <span class="text-danger">*</span>
</label>

<input
    type="text"
    class="form-control"
    name="full_name"
    value="<?= e($profile['full_name']); ?>"
    required
>

</div>


<div class="col-md-6">

<label class="form-label">
    Father's Name <span class="text-danger">*</span>
</label>

<input
    type="text"
    class="form-control"
    name="father_name"
    value="<?= e($profile['father_name']); ?>"
    required
>

</div>


<div class="col-md-6">

<label class="form-label">
    Mother's Name <span class="text-danger">*</span>
</label>

<input
    type="text"
    class="form-control"
    name="mother_name"
    value="<?= e($profile['mother_name']); ?>"
    required
>

</div>


<div class="col-md-6">

<label class="form-label">
    Mobile Number <span class="text-danger">*</span>
</label>

<input
    type="text"
    class="form-control"
    name="mobile"
    value="<?= e($profile['mobile']); ?>"
    required
>

</div>


<div class="col-md-6">

<label class="form-label">
    Email
</label>

<input
    type="email"
    class="form-control"
    value="<?= e($profile['email']); ?>"
    readonly
>

</div>


<div class="col-md-6">

<label class="form-label">
    Date of Birth <span class="text-danger">*</span>
</label>

<input
    type="date"
    class="form-control"
    name="date_of_birth"
    value="<?= e($profile['date_of_birth']); ?>"
    required
>

</div>


<div class="col-md-6">

<label class="form-label">
    Gender <span class="text-danger">*</span>
</label>

<select
    class="form-select"
    name="gender"
    required
>

<option value="">Select Gender</option>

<option
    value="Male"
    <?= $profile['gender'] === 'Male' ? 'selected' : ''; ?>
>
    Male
</option>

<option
    value="Female"
    <?= $profile['gender'] === 'Female' ? 'selected' : ''; ?>
>
    Female
</option>

<option
    value="Other"
    <?= $profile['gender'] === 'Other' ? 'selected' : ''; ?>
>
    Other
</option>

</select>

</div>


<div class="col-md-6">

<label class="form-label">
    NID / Birth Registration Number
    <span class="text-danger">*</span>
</label>

<input
    type="text"
    class="form-control"
    name="identity_number"
    value="<?= e($profile['identity_number']); ?>"
    required
>

</div>

</div>


<div class="d-flex justify-content-end mt-4">

<button
    type="button"
    class="btn btn-primary"
    onclick="nextStep(1)"
>

Next
<i class="bi bi-arrow-right"></i>

</button>

</div>

</div>

</div>


<!-- =========================================================
     STEP 2
========================================================= -->

<div class="form-step" id="step2">

<div class="form-card">

<h3 class="section-title">

<i class="bi bi-mortarboard"></i>

Academic Information

</h3>


<div class="row g-4">


<div class="col-md-6">

<label class="form-label">

SSC / Equivalent GPA
<span class="text-danger">*</span>

</label>

<input
    type="number"
    step="0.01"
    min="0"
    max="5"
    class="form-control"
    name="ssc_gpa"
    placeholder="0.00 - 5.00"
    required
>

</div>


<div class="col-md-6">

<label class="form-label">

Diploma CGPA
<span class="text-danger">*</span>

</label>

<input
    type="number"
    step="0.01"
    min="0"
    max="4"
    class="form-control"
    name="diploma_cgpa"
    placeholder="0.00 - 4.00"
    required
>

</div>


<div class="col-md-6">

<label class="form-label">

Diploma Passing Year
<span class="text-danger">*</span>

</label>

<select
    class="form-select"
    name="diploma_passing_year"
    required
>

<option value="">Select Passing Year</option>

<?php foreach ($diplomaYears as $year): ?>

<option value="<?= $year; ?>">
    <?= $year; ?>
</option>

<?php endforeach; ?>

</select>

</div>


<div class="col-md-6">

<label class="form-label">

Diploma Technology
<span class="text-danger">*</span>

</label>

<select
    class="form-select"
    name="technology_id"
    id="diplomaTechnology"
    required
>

<option value="">
    Select Diploma Technology
</option>

<?php foreach ($diplomaTechnologies as $technology): ?>

<option value="<?= (int) $technology['id']; ?>">

<?= e($technology['name']); ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="d-flex justify-content-between mt-4">

<button
    type="button"
    class="btn btn-outline-primary"
    onclick="prevStep(2)"
>

<i class="bi bi-arrow-left"></i>
Previous

</button>


<button
    type="button"
    class="btn btn-primary"
    onclick="nextStep(2)"
>

Next
<i class="bi bi-arrow-right"></i>

</button>

</div>

</div>

</div>


<!-- =========================================================
     STEP 3
========================================================= -->

<div class="form-step" id="step3">

<div class="form-card">

<h3 class="section-title">

<i class="bi bi-building"></i>

Department Selection

</h3>


<div class="info-box mb-4">

<i class="bi bi-info-circle"></i>

Your eligible departments are automatically
determined according to your Diploma Technology.

</div>


<div class="mb-4">

<label class="form-label">

Select Department
<span class="text-danger">*</span>

</label>

<select
    class="form-select"
    name="department_id"
    id="department"
    required
    disabled
>

<option value="">
    Select Diploma Technology first
</option>

</select>

</div>


<div
    class="department-info-box"
    id="departmentInfoBox"
>

<div id="departmentInfoText"></div>

</div>


<div class="d-flex justify-content-between mt-4">

<button
    type="button"
    class="btn btn-outline-primary"
    onclick="prevStep(3)"
>

<i class="bi bi-arrow-left"></i>
Previous

</button>


<button
    type="button"
    class="btn btn-primary"
    onclick="nextStep(3)"
>

Next
<i class="bi bi-arrow-right"></i>

</button>

</div>

</div>

</div>


<!-- =========================================================
     STEP 4
========================================================= -->

<div class="form-step" id="step4">

<div class="form-card">

<h3 class="section-title">

<i class="bi bi-file-earmark-arrow-up"></i>

Documents & Quota

</h3>


<div class="row g-4">


<!-- PHOTO -->

<div class="col-md-6">

<div class="document-box">

<label class="form-label">

Applicant Photo
<span class="text-danger">*</span>

</label>

<input
    type="file"
    class="form-control"
    name="photo"
    id="photo"
    accept="image/jpeg,image/png"
    required
>

<small class="text-muted">

JPG/JPEG/PNG.
Recommended: 250 × 250 pixels.
Maximum: <strong>100 KB</strong>.

</small>

</div>

</div>


<!-- SIGNATURE -->

<div class="col-md-6">

<div class="document-box">

<label class="form-label">

Signature
<span class="text-danger">*</span>

</label>

<input
    type="file"
    class="form-control"
    name="signature"
    id="signature"
    accept="image/jpeg,image/png"
    required
>

<small class="text-muted">

JPG/JPEG/PNG.
Recommended: 250 × 50 pixels.
Maximum: <strong>100 KB</strong>.

</small>

</div>

</div>


<!-- ID DOCUMENT -->

<div class="col-md-6">

<div class="document-box">

<label class="form-label">

NID / Birth Registration Document
<span class="text-danger">*</span>

</label>

<input
    type="file"
    class="form-control"
    name="identity_document"
    id="identity_document"
    accept=".pdf,image/jpeg,image/png"
    required
>

<small class="text-muted">

PDF/JPG/JPEG/PNG.
Maximum: <strong>5 MB</strong>.

</small>

</div>

</div>


<!-- QUOTA -->

<div class="col-md-6">

<div class="document-box">

<label class="form-label">

Quota
<span class="text-danger">*</span>

</label>

<select
    class="form-select"
    name="quota"
    id="quota"
    required
>

<option value="">Select Quota</option>

<option value="None">None</option>

<option value="Freedom Fighter's Son/Daughter">
    Freedom Fighter's Son/Daughter
</option>

<option value="Tribal">
    Tribal
</option>

</select>

</div>

</div>


<!-- QUOTA DOCUMENT -->

<div
    class="col-12"
    id="quotaDocumentWrapper"
    style="display:none;"
>

<div class="document-box">

<label class="form-label">

Quota Supporting Document
<span class="text-danger">*</span>

</label>

<input
    type="file"
    class="form-control"
    name="quota_document"
    id="quotaDocument"
    accept=".pdf,application/pdf"
>

<small class="text-muted">

PDF only.
Maximum: <strong>5 MB</strong>.

</small>

</div>

</div>


</div>


<div class="d-flex justify-content-between mt-4">

<button
    type="button"
    class="btn btn-outline-primary"
    onclick="prevStep(4)"
>

<i class="bi bi-arrow-left"></i>
Previous

</button>


<button
    type="button"
    class="btn btn-primary"
    onclick="nextStep(4)"
>

Next
<i class="bi bi-arrow-right"></i>

</button>

</div>

</div>

</div>


<!-- =========================================================
     STEP 5
========================================================= -->

<div class="form-step" id="step5">

<div class="form-card">

<h3 class="section-title">

<i class="bi bi-credit-card"></i>

Payment & Review

</h3>


<div class="row g-4 mb-4">


<div class="col-md-6">

<label class="form-label">

Payment Method
<span class="text-danger">*</span>

</label>

<select
    class="form-select"
    name="payment_method"
    required
>

<option value="">
    Select Payment Method
</option>

<option value="Rocket">
    Rocket
</option>

<option value="bKash">
    bKash
</option>

<option value="Agrani Education Fee Pay">
    Agrani Education Fee Pay
</option>

</select>

</div>


<div class="col-md-6">

<label class="form-label">

Payment / Transaction ID
<span class="text-danger">*</span>

</label>

<input
    type="text"
    class="form-control"
    name="transaction_id"
    placeholder="Enter transaction ID"
    maxlength="100"
    required
>

</div>

</div>


<h5 class="fw-bold mb-3">

<i class="bi bi-check-circle text-primary"></i>

Application Summary

</h5>


<div class="review-box mb-4">

<div class="review-row">

<span class="review-label">
Applicant Name
</span>

<span
    class="review-value"
    id="reviewName"
>
-
</span>

</div>


<div class="review-row">

<span class="review-label">
Mobile
</span>

<span
    class="review-value"
    id="reviewMobile"
>
-
</span>

</div>


<div class="review-row">

<span class="review-label">
Diploma Technology
</span>

<span
    class="review-value"
    id="reviewTechnology"
>
-
</span>

</div>


<div class="review-row">

<span class="review-label">
Diploma Passing Year
</span>

<span
    class="review-value"
    id="reviewYear"
>
-
</span>

</div>


<div class="review-row">

<span class="review-label">
Selected Department
</span>

<span
    class="review-value"
    id="reviewDepartment"
>
-
</span>

</div>


<div class="review-row">

<span class="review-label">
Quota
</span>

<span
    class="review-value"
    id="reviewQuota"
>
-
</span>

</div>

</div>


<div class="form-check mb-4">

<input
    class="form-check-input"
    type="checkbox"
    name="declaration"
    id="declaration"
    value="1"
    required
>

<label
    class="form-check-label"
    for="declaration"
>

I declare that all information provided
in this application is correct and complete.

</label>

</div>


<div class="d-flex justify-content-between">

<button
    type="button"
    class="btn btn-outline-primary"
    onclick="prevStep(5)"
>

<i class="bi bi-arrow-left"></i>
Previous

</button>


<button
    type="submit"
    class="btn btn-success"
    id="submitApplicationBtn"
>

<i class="bi bi-check-circle"></i>

Submit Application

</button>

</div>

</div>

</div>


</form>

</div>

</div>


<script>

const departmentData =
    <?= $departmentJson ?: '{}'; ?>;


let currentStep = 1;


/*
|--------------------------------------------------------------------------
| Show Step
|--------------------------------------------------------------------------
*/

function showStep(step) {

    document
        .querySelectorAll('.form-step')
        .forEach(function(element) {

            element.classList.remove('active');

        });


    const target =
        document.getElementById(
            'step' + step
        );


    if (target) {

        target.classList.add('active');

    }


    for (let i = 1; i <= 5; i++) {

        const indicator =
            document.getElementById(
                'stepIndicator' + i
            );


        if (!indicator) {
            continue;
        }


        indicator.classList.remove('active');
        indicator.classList.remove('completed');


        if (i < step) {

            indicator.classList.add(
                'completed'
            );

        }
        else if (i === step) {

            indicator.classList.add(
                'active'
            );

        }

    }


    currentStep = step;


    if (step === 5) {

        updateReview();

    }


    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });

}


/*
|--------------------------------------------------------------------------
| Next Step
|--------------------------------------------------------------------------
*/

function nextStep(step) {

    const current =
        document.getElementById(
            'step' + step
        );


    if (!current) {
        return;
    }


    const inputs =
        current.querySelectorAll(
            'input, select, textarea'
        );


    for (const input of inputs) {

        if (!input.checkValidity()) {

            input.reportValidity();

            return;

        }

    }


    if (step === 3) {

        const department =
            document.getElementById(
                'department'
            ).value;


        if (!department) {

            alert(
                'Please select a department.'
            );

            return;

        }

    }


    showStep(step + 1);

}


/*
|--------------------------------------------------------------------------
| Previous Step
|--------------------------------------------------------------------------
*/

function prevStep(step) {

    showStep(step - 1);

}


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}


/*
|--------------------------------------------------------------------------
| Update Departments
|--------------------------------------------------------------------------
*/

function updateDepartments() {

    const technologySelect =
        document.getElementById(
            'diplomaTechnology'
        );


    const departmentSelect =
        document.getElementById(
            'department'
        );


    const infoBox =
        document.getElementById(
            'departmentInfoBox'
        );


    const infoText =
        document.getElementById(
            'departmentInfoText'
        );


    const technologyId =
        technologySelect.value;


    departmentSelect.innerHTML = '';

    departmentSelect.disabled = true;

    infoBox.classList.remove('show');

    infoBox.classList.remove('multiple');

    infoText.innerHTML = '';


    if (!technologyId) {

        const option =
            document.createElement('option');

        option.value = '';

        option.textContent =
            'Select Diploma Technology first';

        departmentSelect.appendChild(option);

        return;

    }


    const departments =
        departmentData[technologyId] || [];


    if (departments.length === 0) {

        const option =
            document.createElement('option');

        option.value = '';

        option.textContent =
            'No eligible department found';

        departmentSelect.appendChild(option);

        infoBox.classList.add('show');

        infoText.innerHTML = `

            <i class="bi bi-exclamation-circle"></i>

            No eligible department found for this
            Diploma Technology.

        `;

        return;

    }


    departmentSelect.disabled = false;


    const defaultOption =
        document.createElement('option');

    defaultOption.value = '';

    defaultOption.textContent =
        'Select Department';

    departmentSelect.appendChild(
        defaultOption
    );


    departments.forEach(function(department) {

        const option =
            document.createElement('option');


        /*
        IMPORTANT:
        Send numeric department ID.
        */

        option.value =
            department.id;


        option.textContent =
            department.name +
            ' (' +
            department.code +
            ')';


        departmentSelect.appendChild(
            option
        );

    });


    infoBox.classList.add('show');


    if (departments.length === 1) {

        infoText.innerHTML = `

            <i class="bi bi-check-circle"></i>

            You are eligible for

            <strong>1 department</strong>:

            <strong>
                ${escapeHtml(
                    departments[0].name
                )}

                (${escapeHtml(
                    departments[0].code
                )})
            </strong>.

        `;

    }
    else {

        infoBox.classList.add('multiple');


        const names =
            departments
                .map(function(department) {

                    return `
                        <strong>
                            ${escapeHtml(
                                department.name
                            )}
                            (${escapeHtml(
                                department.code
                            )})
                        </strong>
                    `;

                })
                .join(', ');


        infoText.innerHTML = `

            <div class="department-count mb-1">

                <i class="bi bi-buildings"></i>

                You are eligible for
                ${departments.length}
                departments.

            </div>

            <div>

                Eligible departments:
                ${names}

            </div>

        `;

    }

}


/*
|--------------------------------------------------------------------------
| Technology Change
|--------------------------------------------------------------------------
*/

document
    .getElementById(
        'diplomaTechnology'
    )
    .addEventListener(
        'change',
        updateDepartments
    );


/*
|--------------------------------------------------------------------------
| Quota Change
|--------------------------------------------------------------------------
*/

document
    .getElementById(
        'quota'
    )
    .addEventListener(
        'change',
        function() {

            const wrapper =
                document.getElementById(
                    'quotaDocumentWrapper'
                );


            const input =
                document.getElementById(
                    'quotaDocument'
                );


            if (
                this.value &&
                this.value !== 'None'
            ) {

                wrapper.style.display =
                    'block';

                input.required = true;

            }
            else {

                wrapper.style.display =
                    'none';

                input.required = false;

                input.value = '';

            }

        }
    );


/*
|--------------------------------------------------------------------------
| Client-side File Size Check
|--------------------------------------------------------------------------
|
| This gives immediate error before PHP submission.
|--------------------------------------------------------------------------
*/

function checkFileSize(
    input,
    maxSize,
    label
) {

    if (
        !input.files ||
        !input.files[0]
    ) {

        return true;

    }


    const file =
        input.files[0];


    if (file.size > maxSize) {

        const maxKB =
            Math.round(
                maxSize / 1024
            );


        alert(
            label +
            ' must be ' +
            maxKB +
            ' KB or smaller.'
        );


        input.value = '';

        return false;

    }


    return true;

}


/*
|--------------------------------------------------------------------------
| Photo Size
|--------------------------------------------------------------------------
*/

document
    .getElementById('photo')
    .addEventListener(
        'change',
        function() {

            checkFileSize(
                this,
                100 * 1024,
                'Applicant photo'
            );

        }
    );


/*
|--------------------------------------------------------------------------
| Signature Size
|--------------------------------------------------------------------------
*/

document
    .getElementById('signature')
    .addEventListener(
        'change',
        function() {

            checkFileSize(
                this,
                100 * 1024,
                'Signature'
            );

        }
    );


/*
|--------------------------------------------------------------------------
| Identity Document Size
|--------------------------------------------------------------------------
*/

document
    .getElementById('identity_document')
    .addEventListener(
        'change',
        function() {

            checkFileSize(
                this,
                5 * 1024 * 1024,
                'NID / Birth Registration document'
            );

        }
    );


/*
|--------------------------------------------------------------------------
| Quota Document Size
|--------------------------------------------------------------------------
*/

document
    .getElementById('quotaDocument')
    .addEventListener(
        'change',
        function() {

            checkFileSize(
                this,
                5 * 1024 * 1024,
                'Quota supporting document'
            );

        }
    );


/*
|--------------------------------------------------------------------------
| Review
|--------------------------------------------------------------------------
*/

function updateReview() {

    const name =
        document.querySelector(
            '[name="full_name"]'
        ).value;


    const mobile =
        document.querySelector(
            '[name="mobile"]'
        ).value;


    const technologySelect =
        document.getElementById(
            'diplomaTechnology'
        );


    const technology =
        technologySelect
            .options[
                technologySelect.selectedIndex
            ]?.text || '-';


    const year =
        document.querySelector(
            '[name="diploma_passing_year"]'
        ).value;


    const departmentSelect =
        document.getElementById(
            'department'
        );


    const department =
        departmentSelect
            .options[
                departmentSelect.selectedIndex
            ]?.text || '-';


    const quota =
        document.getElementById(
            'quota'
        ).value;


    document.getElementById(
        'reviewName'
    ).textContent = name || '-';


    document.getElementById(
        'reviewMobile'
    ).textContent = mobile || '-';


    document.getElementById(
        'reviewTechnology'
    ).textContent = technology || '-';


    document.getElementById(
        'reviewYear'
    ).textContent = year || '-';


    document.getElementById(
        'reviewDepartment'
    ).textContent = department || '-';


    document.getElementById(
        'reviewQuota'
    ).textContent = quota || '-';

}


/*
|--------------------------------------------------------------------------
| Prevent Double Submit
|--------------------------------------------------------------------------
*/

document
    .getElementById('applicationForm')
    .addEventListener(
        'submit',
        function(event) {

            const photo =
                document.getElementById('photo');

            const signature =
                document.getElementById('signature');

            const identity =
                document.getElementById(
                    'identity_document'
                );

            const quota =
                document.getElementById(
                    'quotaDocument'
                );


            if (
                !checkFileSize(
                    photo,
                    100 * 1024,
                    'Applicant photo'
                )
            ) {

                event.preventDefault();
                return;

            }


            if (
                !checkFileSize(
                    signature,
                    100 * 1024,
                    'Signature'
                )
            ) {

                event.preventDefault();
                return;

            }


            if (
                !checkFileSize(
                    identity,
                    5 * 1024 * 1024,
                    'NID / Birth Registration document'
                )
            ) {

                event.preventDefault();
                return;

            }


            if (
                quota &&
                quota.files.length > 0
            ) {

                if (
                    !checkFileSize(
                        quota,
                        5 * 1024 * 1024,
                        'Quota supporting document'
                    )
                ) {

                    event.preventDefault();
                    return;

                }

            }


            const button =
                document.getElementById(
                    'submitApplicationBtn'
                );


            button.disabled = true;

            button.innerHTML = `

                <span
                    class="spinner-border spinner-border-sm me-1"
                ></span>

                Submitting...

            `;

        }
    );


/*
|--------------------------------------------------------------------------
| Initial Step
|--------------------------------------------------------------------------
*/

showStep(1);

</script>


<?php

require_once __DIR__ . '/includes/footer.php';

?>