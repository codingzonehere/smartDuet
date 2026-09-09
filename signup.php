<?php
/**
 * Smart DUET Admission Management System
 *
 * File: signup.php
 *
 * Purpose:
 * Applicant account registration page.
 *
 * NOTE:
 * Only UI/design has been improved.
 * Existing PHP logic and form action remain unchanged.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';


// --------------------------------------------------
// If user is already logged in
// --------------------------------------------------

if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
}


// --------------------------------------------------
// Get Flash Message
// --------------------------------------------------

$flash = getFlashMessage();


// --------------------------------------------------
// Page Title
// --------------------------------------------------

$pageTitle = "Create Account";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Account | Smart DUET Admission</title>


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


    <!-- Google Font -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="<?= APP_URL ?>/css/style.css"
    >


    <link
        rel="icon"
        type="image/x-icon"
        href="<?= APP_URL ?>/assets/images/duet-logo.png"
    >


    <style>

        /* =====================================================
           SIGNUP PAGE DESIGN
        ===================================================== */

        * {
            box-sizing: border-box;
        }


        body.auth-page {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;

            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(59,130,246,.12),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 90% 90%,
                    rgba(124,58,237,.12),
                    transparent 28%
                ),
                #f5f7fb;
        }


        .auth-wrapper {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px 18px;
        }


        .auth-container {
            width: 100%;
            max-width: 1080px;

            overflow: hidden;

            border-radius: 28px;

            background: #ffffff;

            box-shadow:
                0 25px 70px rgba(15,23,42,.13),
                0 5px 20px rgba(15,23,42,.05);
        }


        /* =====================================================
           LEFT SIDE
        ===================================================== */

        .auth-left {
            min-height: 700px;
            height: 100%;

            position: relative;
            overflow: hidden;

            padding: 58px 55px;

            color: #ffffff;

            background:
                linear-gradient(
                    145deg,
                    #0b5ed7 0%,
                    #174ea6 48%,
                    #4f46e5 100%
                );
        }


        .auth-left::before {
            content: "";

            position: absolute;

            width: 310px;
            height: 310px;

            border-radius: 50%;

            background: rgba(255,255,255,.08);

            top: -130px;
            right: -120px;
        }


        .auth-left::after {
            content: "";

            position: absolute;

            width: 230px;
            height: 230px;

            border-radius: 50%;

            background: rgba(255,255,255,.06);

            bottom: -100px;
            left: -90px;
        }


        .brand-content {
            position: relative;
            z-index: 2;
        }


        .duet-logo {
            width: 82px;
            height: 82px;

            object-fit: contain;

            padding: 10px;

            background: rgba(255,255,255,.14);

            border: 1px solid rgba(255,255,255,.25);

            border-radius: 22px;

            backdrop-filter: blur(10px);

            margin-bottom: 26px;
        }


        .brand-title {
            font-size: 38px;
            font-weight: 800;

            letter-spacing: -1.2px;

            margin-bottom: 8px;
        }


        .brand-subtitle {
            font-size: 17px;
            font-weight: 500;

            opacity: .88;

            line-height: 1.6;
        }


        .brand-description {
            max-width: 440px;

            margin-top: 30px;

            font-size: 14px;

            line-height: 1.8;

            color: rgba(255,255,255,.82);
        }


        .feature-list {
            margin-top: 32px;

            display: flex;
            flex-direction: column;

            gap: 14px;
        }


        .feature-item {
            display: flex;
            align-items: center;

            gap: 13px;

            font-size: 14px;
            font-weight: 500;
        }


        .feature-icon {
            width: 31px;
            height: 31px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: rgba(255,255,255,.14);

            border: 1px solid rgba(255,255,255,.14);
        }


        /* =====================================================
           RIGHT SIDE
        ===================================================== */

        .auth-right {
            min-height: 700px;
            height: 100%;

            display: flex;
            flex-direction: column;
            justify-content: center;

            padding: 50px 65px;
        }


        .signup-icon {
            width: 58px;
            height: 58px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 17px;

            margin-bottom: 19px;

            color: #ffffff;

            font-size: 24px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #7c3aed
                );

            box-shadow:
                0 10px 25px rgba(37,99,235,.22);
        }


        .auth-title {
            color: #111827;

            font-size: 29px;

            font-weight: 800;

            letter-spacing: -.7px;
        }


        .auth-subtitle {
            color: #6b7280;

            font-size: 14px;

            line-height: 1.6;
        }


        /* =====================================================
           FLASH MESSAGE
        ===================================================== */

        .custom-alert {
            border: 0;

            border-radius: 13px;

            font-size: 13px;

            font-weight: 500;

            padding: 13px 15px;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-label {
            color: #374151;

            font-size: 13px;

            font-weight: 600;

            margin-bottom: 8px;
        }


        .modern-input-group {
            position: relative;

            display: flex;
            align-items: center;
        }


        .modern-input-icon {
            position: absolute;

            left: 15px;

            z-index: 5;

            color: #8b95a7;

            font-size: 17px;

            pointer-events: none;
        }


        .modern-input {
            width: 100%;

            height: 51px;

            border: 1px solid #e2e6ee;

            border-radius: 13px !important;

            background: #f9fafc;

            color: #1f2937;

            font-size: 14px;

            padding: 0 45px 0 44px;

            outline: none;

            transition: all .25s ease;
        }


        .modern-input::placeholder {
            color: #a1a9b8;
        }


        .modern-input:hover {
            border-color: #cbd5e1;

            background: #ffffff;
        }


        .modern-input:focus {
            border-color: #4f7cff;

            background: #ffffff;

            box-shadow:
                0 0 0 4px rgba(79,124,255,.10);
        }


        .field-help {
            display: block;

            margin-top: 6px;

            color: #8a93a3;

            font-size: 11px;
        }


        /* =====================================================
           PASSWORD SHOW / HIDE
        ===================================================== */

        .password-toggle {
            position: absolute;

            right: 13px;
            top: 50%;

            transform: translateY(-50%);

            z-index: 6;

            width: 34px;
            height: 34px;

            border: none;

            background: transparent;

            color: #8b95a7;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 9px;

            cursor: pointer;

            transition: all .2s ease;
        }


        .password-toggle:hover {
            color: #2563eb;

            background: #eef4ff;
        }


        /* =====================================================
           CREATE ACCOUNT BUTTON
        ===================================================== */

        .signup-button {
            height: 53px;

            border: none;

            border-radius: 13px;

            color: #ffffff;

            font-size: 14px;

            font-weight: 700;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #4f46e5
                );

            box-shadow:
                0 10px 24px rgba(37,99,235,.20);

            transition: all .25s ease;
        }


        .signup-button:hover {
            color: #ffffff;

            transform: translateY(-2px);

            box-shadow:
                0 14px 28px rgba(37,99,235,.28);
        }


        .signup-button:active {
            transform: translateY(0);
        }


        /* =====================================================
           LOGIN LINK
        ===================================================== */

        .auth-bottom {
            margin-top: 22px;

            padding-top: 20px;

            border-top: 1px solid #edf0f5;

            text-align: center;

            font-size: 13px;
        }


        .auth-bottom span {
            color: #8a93a3;
        }


        .auth-bottom a {
            color: #2563eb;

            text-decoration: none;

            font-weight: 700;

            margin-left: 4px;
        }


        .auth-bottom a:hover {
            color: #4f46e5;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 991px) {

            .auth-left {
                min-height: auto;

                padding: 45px 40px;
            }

            .auth-right {
                min-height: auto;

                padding: 45px 40px;
            }

            .brand-title {
                font-size: 32px;
            }

        }


        @media (max-width: 575px) {

            .auth-wrapper {
                padding: 15px;
            }

            .auth-container {
                border-radius: 20px;
            }

            .auth-left {
                padding: 38px 28px;
            }

            .auth-right {
                padding: 35px 25px;
            }

            .duet-logo {
                width: 68px;
                height: 68px;

                border-radius: 17px;
            }

            .brand-title {
                font-size: 27px;
            }

            .brand-subtitle {
                font-size: 14px;
            }

            .brand-description {
                margin-top: 20px;
            }

            .auth-title {
                font-size: 25px;
            }

        }

    </style>

</head>


<body class="auth-page">


<div class="auth-wrapper">

    <div class="auth-container">

        <div class="row g-0">


            <!-- =================================================
                 LEFT SIDE
            ================================================== -->

            <div class="col-lg-6">

                <div class="auth-left">

                    <div class="brand-content">

                        <img
                            src="<?= APP_URL ?>/assets/images/duet-logo.png"
                            alt="DUET Logo"
                            class="duet-logo"
                        >


                        <div class="brand-title">
                            Smart DUET
                        </div>


                        <div class="brand-subtitle">
                            Admission Management System
                        </div>


                        <p class="brand-description">

                            Create your account and manage your
                            DUET undergraduate admission journey
                            through one simple and secure platform.

                        </p>


                        <div class="feature-list">


                            <div class="feature-item">

                                <span class="feature-icon">
                                    <i class="bi bi-shield-check"></i>
                                </span>

                                <span>
                                    Check admission eligibility
                                </span>

                            </div>


                            <div class="feature-item">

                                <span class="feature-icon">
                                    <i class="bi bi-file-earmark-plus"></i>
                                </span>

                                <span>
                                    Submit admission application
                                </span>

                            </div>


                            <div class="feature-item">

                                <span class="feature-icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </span>

                                <span>
                                    Track application status
                                </span>

                            </div>


                            <div class="feature-item">

                                <span class="feature-icon">
                                    <i class="bi bi-person-vcard"></i>
                                </span>

                                <span>
                                    View admit card and result
                                </span>

                            </div>


                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 RIGHT SIDE
            ================================================== -->

            <div class="col-lg-6">

                <div class="auth-right">


                    <div class="signup-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>


                    <h2 class="auth-title mb-2">
                        Create Account
                    </h2>


                    <p class="auth-subtitle mb-4">
                        Register to start your DUET admission journey.
                    </p>



                    <!-- =================================================
                         FLASH MESSAGE
                    ================================================== -->

                    <?php if ($flash): ?>

                        <div
                            class="alert alert-<?= e($flash['type']); ?> custom-alert mb-4"
                            role="alert"
                        >

                            <i class="bi bi-info-circle me-2"></i>

                            <?= e($flash['message']); ?>

                        </div>

                    <?php endif; ?>



                    <!-- =================================================
                         REGISTRATION FORM
                    ================================================== -->

                    <form
                        action="<?= APP_URL ?>/actions/signup_action.php"
                        method="POST"
                    >


                        <!-- Email -->

                        <div class="mb-3">

                            <label
                                for="email"
                                class="form-label"
                            >
                                Email Address
                            </label>


                            <div class="modern-input-group">

                                <i
                                    class="bi bi-envelope modern-input-icon"
                                ></i>


                                <input
                                    type="email"
                                    class="modern-input"
                                    id="email"
                                    name="email"
                                    placeholder="Enter your email"
                                    autocomplete="email"
                                    required
                                >

                            </div>

                        </div>



                        <!-- Mobile -->

                        <div class="mb-3">

                            <label
                                for="mobile"
                                class="form-label"
                            >
                                Mobile Number
                            </label>


                            <div class="modern-input-group">

                                <i
                                    class="bi bi-phone modern-input-icon"
                                ></i>


                                <input
                                    type="tel"
                                    class="modern-input"
                                    id="mobile"
                                    name="mobile"
                                    placeholder="01XXXXXXXXX"
                                    pattern="01[3-9][0-9]{8}"
                                    maxlength="11"
                                    autocomplete="tel"
                                    required
                                >

                            </div>


                            <small class="field-help">
                                Enter a valid Bangladeshi mobile number.
                            </small>

                        </div>



                        <!-- Password -->

                        <div class="mb-3">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Password
                            </label>


                            <div class="modern-input-group">

                                <i
                                    class="bi bi-lock modern-input-icon"
                                ></i>


                                <input
                                    type="password"
                                    class="modern-input"
                                    id="password"
                                    name="password"
                                    placeholder="Create a password"
                                    minlength="6"
                                    autocomplete="new-password"
                                    required
                                >


                                <!-- Show / Hide Password -->

                                <button
                                    type="button"
                                    class="password-toggle"
                                    onclick="togglePassword('password', 'passwordIcon')"
                                    aria-label="Show password"
                                >

                                    <i
                                        class="bi bi-eye"
                                        id="passwordIcon"
                                    ></i>

                                </button>

                            </div>

                        </div>



                        <!-- Confirm Password -->

                        <div class="mb-4">

                            <label
                                for="confirm_password"
                                class="form-label"
                            >
                                Confirm Password
                            </label>


                            <div class="modern-input-group">

                                <i
                                    class="bi bi-shield-lock modern-input-icon"
                                ></i>


                                <input
                                    type="password"
                                    class="modern-input"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="Confirm your password"
                                    minlength="6"
                                    autocomplete="new-password"
                                    required
                                >


                                <!-- Show / Hide Confirm Password -->

                                <button
                                    type="button"
                                    class="password-toggle"
                                    onclick="togglePassword('confirm_password', 'confirmPasswordIcon')"
                                    aria-label="Show confirm password"
                                >

                                    <i
                                        class="bi bi-eye"
                                        id="confirmPasswordIcon"
                                    ></i>

                                </button>

                            </div>

                        </div>



                        <!-- Submit -->

                        <button
                            type="submit"
                            class="btn signup-button w-100"
                        >

                            <i class="bi bi-person-plus me-2"></i>

                            Create Account

                        </button>


                    </form>



                    <!-- Login Link -->

                    <div class="auth-bottom">

                        <span>
                            Already have an account?
                        </span>

                        <a
                            href="<?= APP_URL ?>/login.php"
                        >
                            Login
                        </a>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>



<!-- =====================================================
     PASSWORD TOGGLE SCRIPT
====================================================== -->

<script>

function togglePassword(inputId, iconId) {

    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (!input || !icon) {
        return;
    }


    if (input.type === "password") {

        input.type = "text";

        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");

    } else {

        input.type = "password";

        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");

    }

}

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>