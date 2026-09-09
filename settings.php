<?php

/*
|--------------------------------------------------------------------------
| SETTINGS
|--------------------------------------------------------------------------
| Applicant Account Settings
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';


// --------------------------------------------------
// Page Title
// --------------------------------------------------

$pageTitle = 'Settings';


// --------------------------------------------------
// Logged-in User ID
// --------------------------------------------------

$userId = getUserId();


// --------------------------------------------------
// Get Current User Information
// --------------------------------------------------

$user = [
    'email'  => '',
    'mobile' => ''
];

$sql = "
    SELECT
        email,
        mobile
    FROM users
    WHERE id = ?
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

    $user = $result->fetch_assoc();

}

$stmt->close();


// --------------------------------------------------
// Flash Message
// --------------------------------------------------

$flash = getFlashMessage();


// --------------------------------------------------
// Common Header + Sidebar
// --------------------------------------------------

require_once __DIR__ . '/includes/header.php';

?>


<!-- =====================================================
     SETTINGS CONTENT
====================================================== -->

<div class="content-area">


    <!-- =================================================
         SETTINGS HEADER
    ================================================== -->

    <div class="welcome-box mb-4">

        <div class="row align-items-center">

            <div class="col-lg-9">

                <h2 class="mb-2">
                    Account Settings
                </h2>

                <p class="mb-0">
                    Manage your account information and
                    keep your Smart DUET account secure.
                </p>

            </div>


            <div class="col-lg-3 text-lg-end d-none d-lg-block">

                <i
                    class="bi bi-gear-wide-connected"
                    style="font-size:75px; opacity:.18;"
                ></i>

            </div>

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

            <i
                class="bi
                <?= $flash['type'] === 'success'
                    ? 'bi-check-circle'
                    : 'bi-exclamation-circle'; ?>
                me-2"
            ></i>

            <?= e($flash['message']); ?>

        </div>

    <?php endif; ?>



    <div class="row g-4">


        <!-- =================================================
             LEFT COLUMN
        ================================================== -->

        <div class="col-lg-8">


            <!-- =================================================
                 ACCOUNT INFORMATION
            ================================================== -->

            <div class="custom-card mb-4">


                <div class="d-flex align-items-center gap-3 mb-4">

                    <div class="stat-icon">

                        <i class="bi bi-person-gear"></i>

                    </div>


                    <div>

                        <h5 class="section-title mb-1">

                            Account Information

                        </h5>

                        <small class="text-muted">

                            Update your account contact information.

                        </small>

                    </div>

                </div>



                <form
                    action="<?= APP_URL ?>/actions/settings_action.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="update_email"
                    >


                    <!-- Email -->

                    <div class="mb-4">

                        <label
                            for="email"
                            class="form-label fw-semibold"
                        >

                            Email Address

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-envelope"></i>

                            </span>


                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?= e($user['email']); ?>"
                                required
                            >

                        </div>


                        <small class="text-muted">

                            This email is used for account login.

                        </small>

                    </div>



                    <!-- Mobile -->

                    <div class="mb-4">

                        <label
                            for="mobile"
                            class="form-label fw-semibold"
                        >

                            Mobile Number

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-phone"></i>

                            </span>


                            <input
                                type="text"
                                id="mobile"
                                class="form-control"
                                value="<?= e($user['mobile']); ?>"
                                readonly
                            >

                        </div>


                        <small class="text-muted">

                            You can update your mobile number
                            from your Profile.

                        </small>

                    </div>



                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-check2-circle me-2"></i>

                        Save Changes

                    </button>


                </form>


            </div>



            <!-- =================================================
                 CHANGE PASSWORD
            ================================================== -->

            <div class="custom-card">


                <div class="d-flex align-items-center gap-3 mb-4">

                    <div class="stat-icon">

                        <i class="bi bi-shield-lock"></i>

                    </div>


                    <div>

                        <h5 class="section-title mb-1">

                            Change Password

                        </h5>

                        <small class="text-muted">

                            Update your password to keep
                            your account secure.

                        </small>

                    </div>

                </div>



                <form
                    action="<?= APP_URL ?>/actions/settings_action.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="change_password"
                    >


                    <!-- Current Password -->

                    <div class="mb-3">

                        <label
                            for="current_password"
                            class="form-label fw-semibold"
                        >

                            Current Password

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-lock"></i>

                            </span>


                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control password-field"
                                placeholder="Enter current password"
                                required
                            >


                            <button
                                type="button"
                                class="btn btn-outline-secondary password-toggle"
                                data-target="current_password"
                                aria-label="Show password"
                            >

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                    </div>



                    <div class="row">


                        <!-- New Password -->

                        <div class="col-md-6 mb-3">

                            <label
                                for="new_password"
                                class="form-label fw-semibold"
                            >

                                New Password

                            </label>


                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-key"></i>

                                </span>


                                <input
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    class="form-control password-field"
                                    placeholder="Enter new password"
                                    minlength="6"
                                    required
                                >


                                <button
                                    type="button"
                                    class="btn btn-outline-secondary password-toggle"
                                    data-target="new_password"
                                    aria-label="Show password"
                                >

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>



                        <!-- Confirm Password -->

                        <div class="col-md-6 mb-3">

                            <label
                                for="confirm_password"
                                class="form-label fw-semibold"
                            >

                                Confirm Password

                            </label>


                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-shield-check"></i>

                                </span>


                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-control password-field"
                                    placeholder="Confirm new password"
                                    minlength="6"
                                    required
                                >


                                <button
                                    type="button"
                                    class="btn btn-outline-secondary password-toggle"
                                    data-target="confirm_password"
                                    aria-label="Show password"
                                >

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>


                    </div>



                    <div class="alert alert-light border small mb-4">

                        <i class="bi bi-info-circle me-2"></i>

                        Password must contain at least
                        <strong>6 characters</strong>.

                    </div>



                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-key me-2"></i>

                        Change Password

                    </button>


                </form>


            </div>


        </div>



        <!-- =================================================
             RIGHT COLUMN
        ================================================== -->

        <div class="col-lg-4">


            <!-- =================================================
                 SECURITY STATUS
            ================================================== -->

            <div class="custom-card mb-4">


                <div class="text-center py-2">

                    <div
                        class="stat-icon mx-auto mb-3"
                        style="width:65px;height:65px;"
                    >

                        <i
                            class="bi bi-shield-check"
                            style="font-size:28px;"
                        ></i>

                    </div>


                    <h5 class="fw-bold mb-2">

                        Account Security

                    </h5>


                    <p class="small text-muted mb-3">

                        Your account is protected with
                        password-based authentication.

                    </p>


                    <span class="badge bg-success px-3 py-2">

                        <i class="bi bi-check-circle me-1"></i>

                        Account Active

                    </span>

                </div>


            </div>



            <!-- =================================================
                 ACCOUNT DETAILS
            ================================================== -->

            <div class="custom-card mb-4">


                <h5 class="section-title mb-4">

                    Account Details

                </h5>


                <div class="mb-3">

                    <div class="text-muted small mb-1">

                        Email

                    </div>

                    <div class="fw-semibold text-break">

                        <?= e($user['email']); ?>

                    </div>

                </div>


                <div class="mb-3">

                    <div class="text-muted small mb-1">

                        Mobile

                    </div>

                    <div class="fw-semibold">

                        <?= e($user['mobile']); ?>

                    </div>

                </div>


                <div class="border-top pt-3 mt-3">

                    <a
                        href="<?= APP_URL ?>/profile.php"
                        class="btn btn-outline-primary w-100"
                    >

                        <i class="bi bi-person-circle me-2"></i>

                        Go to Profile

                    </a>

                </div>


            </div>



            <!-- =================================================
                 ACCOUNT ACTIONS
            ================================================== -->

            <div class="custom-card">


                <h5 class="section-title text-danger">

                    Account Actions

                </h5>


                <p class="small text-muted">

                    Sign out from your Smart DUET account
                    on this device.

                </p>


                <a
                    href="<?= APP_URL ?>/logout.php"
                    class="btn btn-outline-danger w-100"
                >

                    <i class="bi bi-box-arrow-right me-2"></i>

                    Logout from Account

                </a>


            </div>


        </div>


    </div>


</div>



<!-- =====================================================
     PASSWORD SHOW / HIDE SCRIPT
====================================================== -->

<script>

document.addEventListener('DOMContentLoaded', function ()
{
    const toggleButtons =
        document.querySelectorAll('.password-toggle');


    toggleButtons.forEach(function (button)
    {

        button.addEventListener('click', function ()
        {

            const targetId =
                button.getAttribute('data-target');

            const passwordInput =
                document.getElementById(targetId);

            const icon =
                button.querySelector('i');


            if (
                passwordInput.type === 'password'
            ) {

                passwordInput.type = 'text';

                icon.classList.remove(
                    'bi-eye'
                );

                icon.classList.add(
                    'bi-eye-slash'
                );

                button.setAttribute(
                    'aria-label',
                    'Hide password'
                );

            } else {

                passwordInput.type = 'password';

                icon.classList.remove(
                    'bi-eye-slash'
                );

                icon.classList.add(
                    'bi-eye'
                );

                button.setAttribute(
                    'aria-label',
                    'Show password'
                );

            }

        });

    });

});

</script>


<?php

/*
|--------------------------------------------------------------------------
| COMMON FOOTER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/footer.php';

?>