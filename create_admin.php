<?php

// ======================================================
// Smart DUET Admission Management System
// Create Admin Account
// ======================================================

// Database + session
require_once __DIR__ . '/config/config.php';

// e() function
require_once __DIR__ . '/includes/functions.php';


// ------------------------------------------------------
// Variables
// ------------------------------------------------------
$message = '';
$messageType = '';

$fullName = '';
$email = '';
$mobile = '';


// ------------------------------------------------------
// Form Submit
// ------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    // --------------------------------------------------
    // Validation
    // --------------------------------------------------

    if ($fullName === '') {

        $message = 'Please enter admin full name.';
        $messageType = 'danger';

    } elseif ($email === '') {

        $message = 'Please enter email address.';
        $messageType = 'danger';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = 'Please enter a valid email address.';
        $messageType = 'danger';

    } elseif ($mobile === '') {

        $message = 'Please enter mobile number.';
        $messageType = 'danger';

    } elseif ($password === '') {

        $message = 'Please enter password.';
        $messageType = 'danger';

    } elseif (strlen($password) < 6) {

        $message = 'Password must be at least 6 characters.';
        $messageType = 'danger';

    } elseif ($password !== $confirmPassword) {

        $message = 'Passwords do not match.';
        $messageType = 'danger';

    } else {


        // --------------------------------------------------
        // Check Email
        // --------------------------------------------------

        $stmt = $conn->prepare(
            "SELECT id FROM admins WHERE email = ? LIMIT 1"
        );

        if (!$stmt) {

            $message = 'Database error: ' . $conn->error;
            $messageType = 'danger';

        } else {

            $stmt->bind_param('s', $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $message = 'This email is already registered.';
                $messageType = 'danger';

                $stmt->close();

            } else {

                $stmt->close();


                // --------------------------------------------------
                // Check Mobile
                // --------------------------------------------------

                $stmt = $conn->prepare(
                    "SELECT id FROM admins WHERE mobile = ? LIMIT 1"
                );

                if (!$stmt) {

                    $message = 'Database error: ' . $conn->error;
                    $messageType = 'danger';

                } else {

                    $stmt->bind_param('s', $mobile);
                    $stmt->execute();

                    $result = $stmt->get_result();


                    if ($result->num_rows > 0) {

                        $message = 'This mobile number is already registered.';
                        $messageType = 'danger';

                        $stmt->close();

                    } else {

                        $stmt->close();


                        // --------------------------------------------------
                        // Hash Password
                        // --------------------------------------------------

                        $hashedPassword = password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );


                        // --------------------------------------------------
                        // Insert Admin
                        // --------------------------------------------------

                        $stmt = $conn->prepare(
                            "INSERT INTO admins
                            (full_name, email, mobile, password, status)
                            VALUES (?, ?, ?, ?, 'active')"
                        );


                        if (!$stmt) {

                            $message = 'Database error: ' . $conn->error;
                            $messageType = 'danger';

                        } else {

                            $stmt->bind_param(
                                'ssss',
                                $fullName,
                                $email,
                                $mobile,
                                $hashedPassword
                            );


                            if ($stmt->execute()) {

                                $message = 'Admin account created successfully!';

                                $messageType = 'success';


                                // Clear form after successful creation
                                $fullName = '';
                                $email = '';
                                $mobile = '';

                            } else {

                                $message = 'Failed to create admin account: ' . $stmt->error;

                                $messageType = 'danger';
                            }


                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Admin - Smart DUET</title>


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


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            min-height: 100vh;

            background: #f5f7f9;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px 15px;

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        .admin-container {
            width: 100%;
            max-width: 500px;
        }


        .admin-card {

            background: #ffffff;

            border-radius: 16px;

            padding: 32px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.08);
        }


        /* Shield icon */

        .admin-icon {

            width: 58px;
            height: 58px;

            margin: 0 auto 15px;

            border-radius: 50%;

            background: #e9f7ef;

            color: #198754;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 27px;
        }


        /* Title */

        .title {

            text-align: center;

            font-size: 27px;

            font-weight: 700;

            color: #172b24;

            margin-bottom: 5px;
        }


        .subtitle {

            text-align: center;

            color: #6c757d;

            font-size: 14px;

            margin-bottom: 28px;
        }


        /* Labels */

        .form-label {

            display: block;

            font-weight: 600;

            font-size: 14px;

            color: #212529;

            margin-bottom: 7px;
        }


        /* Inputs */

        .form-control {

            width: 100%;

            min-height: 45px;

            border-radius: 8px;

            border: 1px solid #ced4da;

            padding: 10px 12px;

            font-size: 14px;
        }


        .form-control:focus {

            border-color: #198754;

            box-shadow:
                0 0 0 0.2rem rgba(25, 135, 84, 0.12);
        }


        /* Button */

        .btn-admin {

            width: 100%;

            min-height: 46px;

            border: none;

            border-radius: 8px;

            background: #198754;

            color: #ffffff;

            font-weight: 600;

            font-size: 15px;
        }


        .btn-admin:hover {

            background: #146c43;

            color: #ffffff;
        }


        /* Login link */

        .login-link {

            text-align: center;

            margin-top: 20px;

            font-size: 14px;
        }


        .login-link a {

            color: #198754;

            text-decoration: none;

            font-weight: 600;
        }


        .login-link a:hover {

            text-decoration: underline;
        }


        /* Security warning */

        .warning {

            margin-top: 20px;

            padding: 12px;

            border-radius: 8px;

            background: #fff3cd;

            border: 1px solid #ffe69c;

            color: #664d03;

            font-size: 13px;

            line-height: 1.5;
        }


        /* Mobile */

        @media (max-width: 576px) {

            .admin-card {

                padding: 24px 20px;
            }

            .title {

                font-size: 23px;
            }
        }

    </style>

</head>


<body>


<div class="admin-container">


    <div class="admin-card">


        <!-- Admin Icon -->

        <div class="admin-icon">

            <i class="bi bi-shield-lock-fill"></i>

        </div>


        <!-- Heading -->

        <h2 class="title">
            Create Admin Account
        </h2>


        <p class="subtitle">
            Smart DUET Admission Management System
        </p>


        <!-- Success / Error Message -->

        <?php if ($message !== ''): ?>

            <div
                class="alert alert-<?php echo e($messageType); ?>"
            >

                <?php echo e($message); ?>

            </div>

        <?php endif; ?>


        <!-- ================================
             ADMIN FORM
        ================================= -->

        <form
            method="POST"
            action=""
        >


            <!-- Full Name -->

            <div class="mb-3">

                <label class="form-label">
                    Full Name
                </label>

                <input
                    type="text"
                    name="full_name"
                    class="form-control"
                    placeholder="Enter admin full name"
                    value="<?php echo e($fullName); ?>"
                    required
                >

            </div>


            <!-- Email -->

            <div class="mb-3">

                <label class="form-label">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="admin@example.com"
                    value="<?php echo e($email); ?>"
                    required
                >

            </div>


            <!-- Mobile -->

            <div class="mb-3">

                <label class="form-label">
                    Mobile Number
                </label>

                <input
                    type="text"
                    name="mobile"
                    class="form-control"
                    placeholder="01XXXXXXXXX"
                    value="<?php echo e($mobile); ?>"
                    required
                >

            </div>


            <!-- Password -->

            <div class="mb-3">

                <label class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Minimum 6 characters"
                    required
                >

            </div>


            <!-- Confirm Password -->

            <div class="mb-4">

                <label class="form-label">
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    class="form-control"
                    placeholder="Confirm password"
                    required
                >

            </div>


            <!-- Submit Button -->

            <button
                type="submit"
                class="btn btn-admin"
            >

                <i class="bi bi-person-plus-fill me-1"></i>

                Create Admin

            </button>


        </form>


        <!-- Admin Login -->

        <div class="login-link">

            <a
                href="<?php echo APP_URL; ?>/admin/login.php"
            >

                <i class="bi bi-box-arrow-in-right"></i>

                Go to Admin Login

            </a>

        </div>


        <!-- Security Notice -->

        <div class="warning">

            <i class="bi bi-exclamation-triangle-fill me-1"></i>

            <strong>Security Notice:</strong>

            After creating the admin account,
            delete

            <strong>create_admin.php</strong>

            from the project.

        </div>


    </div>

</div>


</body>

</html>