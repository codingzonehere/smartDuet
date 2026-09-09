<?php
/**
 * Smart DUET Admission Management System
 *
 * File: login.php
 *
 * Purpose:
 * Applicant login page.
 *
 * NOTE:
 * Only UI/design has been improved.
 * Existing PHP logic and form action remain unchanged.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';


// --------------------------------------------------
// If already logged in
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

$pageTitle = "Login";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Smart DUET Admission</title>

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
           LOGIN PAGE DESIGN
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        body.auth-page {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at 10% 10%, rgba(59,130,246,.12), transparent 28%),
                radial-gradient(circle at 90% 90%, rgba(124,58,237,.12), transparent 28%),
                #f5f7fb;
        }


        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 18px;
        }


        .auth-container {
            width: 100%;
            max-width: 1080px;
            overflow: hidden;
            border-radius: 28px;
            background: #ffffff;
            box-shadow:
                0 25px 70px rgba(15, 23, 42, .13),
                0 5px 20px rgba(15, 23, 42, .05);
        }


        /* =====================================================
           LEFT SIDE
        ===================================================== */

        .auth-left {
            min-height: 650px;
            height: 100%;
            position: relative;
            overflow: hidden;
            padding: 60px 55px;
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
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            top: -120px;
            right: -110px;
        }


        .auth-left::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            bottom: -90px;
            left: -80px;
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
            margin-bottom: 28px;
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
            min-height: 650px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 55px 65px;
        }


        .login-icon {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 17px;
            margin-bottom: 20px;
            color: #ffffff;
            font-size: 24px;

            background: linear-gradient(
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
            height: 52px;
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
            box-shadow: 0 0 0 4px rgba(79,124,255,.10);
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
           LOGIN BUTTON
        ===================================================== */

        .login-button {
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


        .login-button:hover {
            color: #ffffff;
            transform: translateY(-2px);

            box-shadow:
                0 14px 28px rgba(37,99,235,.28);
        }


        .login-button:active {
            transform: translateY(0);
        }


        /* =====================================================
           SIGNUP LINK
        ===================================================== */

        .auth-bottom {
            margin-top: 25px;
            padding-top: 22px;
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
                            A simple and secure platform to manage
                            your DUET undergraduate admission journey
                            from application to result.
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
                                    <i class="bi bi-file-earmark-text"></i>
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


                    <div class="login-icon">
                        <i class="bi bi-person-lock"></i>
                    </div>


                    <h2 class="auth-title mb-2">
                        Welcome Back
                    </h2>

                    <p class="auth-subtitle mb-4">
                        Login to continue to your Smart DUET account.
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
                         LOGIN FORM
                    ================================================== -->

                    <form
                        action="<?= APP_URL ?>/actions/login_action.php"
                        method="POST"
                    >


                        <!-- Email / Mobile -->

                        <div class="mb-4">

                            <label
                                for="login"
                                class="form-label"
                            >
                                Email or Mobile Number
                            </label>

                            <div class="modern-input-group">

                                <i class="bi bi-person modern-input-icon"></i>

                                <input
                                    type="text"
                                    class="modern-input"
                                    id="login"
                                    name="login"
                                    placeholder="Enter email or mobile number"
                                    autocomplete="username"
                                    required
                                >

                            </div>

                        </div>



                        <!-- Password -->

                        <div class="mb-4">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Password
                            </label>

                            <div class="modern-input-group">

                                <i class="bi bi-lock modern-input-icon"></i>

                                <input
                                    type="password"
                                    class="modern-input"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
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



                        <!-- Login Button -->

                        <button
                            type="submit"
                            class="btn login-button w-100"
                        >

                            <i class="bi bi-box-arrow-in-right me-2"></i>

                            Login

                        </button>


                    </form>



                    <!-- Signup -->

                    <div class="auth-bottom">

                        <span>
                            Don't have an account?
                        </span>

                        <a
                            href="<?= APP_URL ?>/signup.php"
                        >
                            Create Account
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