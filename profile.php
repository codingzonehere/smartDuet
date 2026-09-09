<?php

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
| Applicant Profile
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';


// Page title
$pageTitle = 'Profile';


// --------------------------------------------------
// Get Logged-in User ID
// --------------------------------------------------

$userId = getUserId();


// --------------------------------------------------
// Get Applicant + User Information
// --------------------------------------------------

$profile = [
    'full_name'       => '',
    'father_name'     => '',
    'mother_name'     => '',
    'email'           => '',
    'mobile'          => '',
    'date_of_birth'   => '',
    'gender'          => '',
    'identity_number' => ''
];


$sql = "
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
        ON u.id = a.user_id

    WHERE u.id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $profile = $result->fetch_assoc();

}

$stmt->close();


// --------------------------------------------------
// Flash Message
// --------------------------------------------------

$flash = getFlashMessage();


// --------------------------------------------------
// Common Header
// --------------------------------------------------

require_once __DIR__ . '/includes/header.php';

?>


<!-- =====================================================
     PROFILE CONTENT
====================================================== -->

<div class="content-area">


    <!-- =================================================
         BLUE HERO / PAGE HEADER
    ================================================== -->

    <div class="welcome-box mb-4">

        <div>

            <h3 class="fw-bold mb-2">

                My Profile

            </h3>

            <p class="mb-0">

                Manage your personal information used
                for the DUET admission application.

            </p>

        </div>

    </div>



    <!-- =================================================
         FLASH MESSAGE
    ================================================== -->

    <?php if ($flash): ?>

        <div
            class="alert alert-<?= e($flash['type']); ?> mb-4"
            role="alert"
        >

            <?= e($flash['message']); ?>

        </div>

    <?php endif; ?>



    <div class="row g-4">


        <!-- =================================================
             PROFILE INFORMATION
        ================================================== -->

        <div class="col-lg-8">

            <div class="custom-card">

                <h5 class="section-title mb-4">

                    <i class="bi bi-person-circle me-2"></i>

                    Personal Information

                </h5>


                <form
                    action="<?= APP_URL ?>/actions/profile_action.php"
                    method="POST"
                >


                    <div class="row g-4">


                        <!-- Full Name -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Full Name

                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                value="<?= e($profile['full_name']); ?>"
                                required
                            >

                        </div>



                        <!-- Father's Name -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Father's Name

                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                name="father_name"
                                class="form-control"
                                value="<?= e($profile['father_name']); ?>"
                                required
                            >

                        </div>



                        <!-- Mother's Name -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Mother's Name

                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                name="mother_name"
                                class="form-control"
                                value="<?= e($profile['mother_name']); ?>"
                                required
                            >

                        </div>



                        <!-- Mobile -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Mobile Number

                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="tel"
                                name="mobile"
                                class="form-control"
                                value="<?= e($profile['mobile']); ?>"
                                placeholder="01XXXXXXXXX"
                                pattern="01[3-9][0-9]{8}"
                                maxlength="11"
                                required
                            >

                        </div>



                        <!-- Email -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Email Address

                            </label>

                            <input
                                type="email"
                                class="form-control"
                                value="<?= e($profile['email']); ?>"
                                readonly
                            >

                            <small class="text-muted">

                                Email cannot be changed here.

                            </small>

                        </div>



                        <!-- Date of Birth -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Date of Birth

                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="date"
                                name="date_of_birth"
                                class="form-control"
                                value="<?= e($profile['date_of_birth']); ?>"
                                required
                            >

                        </div>



                        <!-- Gender -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Gender

                                <span class="text-danger">*</span>

                            </label>

                            <select
                                name="gender"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Gender
                                </option>

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



                        <!-- NID / Birth Registration -->

                        <div class="col-md-6">

                            <label class="form-label">

                                NID / Birth Registration Number

                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                name="identity_number"
                                class="form-control"
                                value="<?= e($profile['identity_number']); ?>"
                                required
                            >

                        </div>


                    </div>


                    <!-- Save Button -->

                    <div class="d-flex justify-content-end mt-4">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-save me-2"></i>

                            Save Changes

                        </button>

                    </div>


                </form>

            </div>

        </div>



        <!-- =================================================
             ACCOUNT INFORMATION
        ================================================== -->

        <div class="col-lg-4">

            <div class="custom-card">

                <h5 class="section-title mb-4">

                    <i class="bi bi-shield-check me-2"></i>

                    Account Information

                </h5>


                <div class="mb-3">

                    <div class="text-muted small">
                        Account Email
                    </div>

                    <div class="fw-semibold">
                        <?= e($profile['email']); ?>
                    </div>

                </div>


                <div class="mb-3">

                    <div class="text-muted small">
                        Mobile Number
                    </div>

                    <div class="fw-semibold">
                        <?= e($profile['mobile']); ?>
                    </div>

                </div>


                <div class="border-top pt-3 mt-3">

                    <a
                        href="<?= APP_URL ?>/settings.php"
                        class="btn btn-outline-primary w-100"
                    >

                        <i class="bi bi-gear me-2"></i>

                        Account Settings

                    </a>

                </div>

            </div>


            <!-- Information -->

            <div class="custom-card mt-4">

                <h6 class="fw-bold">

                    <i class="bi bi-info-circle me-2"></i>

                    Profile Information

                </h6>

                <p class="text-muted small mb-0">

                    Keep your personal information accurate.
                    These details will be used automatically
                    when you apply for admission.

                </p>

            </div>

        </div>


    </div>


</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>