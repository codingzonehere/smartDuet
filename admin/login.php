<?php
/**
 * Smart DUET Admission Management System
 * Admin Login
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';


/* If already logged in as admin, go to dashboard */

if (
    isset($_SESSION['user_id']) &&
    ($_SESSION['role'] ?? '') === 'admin'
) {
    header('Location: ' . APP_URL . '/admin/dashboard.php');
    exit;
}


$flash = getFlashMessage();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login | Smart DUET</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
    <link
        rel="icon"
        type="image/x-icon"
        href="<?= APP_URL ?>/assets/images/duet-logo.png"
    >


    <style>

        :root {
            --admin-primary: #198754;
            --admin-dark: #146c43;
            --admin-sidebar: #172b24;
        }

        body {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                linear-gradient(
                    135deg,
                    #e9f7ef,
                    #f5f7f9
                );

            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;

            background: #fff;

            border-radius: 15px;

            padding: 35px;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, .10);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-logo img {
            width: 70px;
            height: 70px;

            object-fit: contain;

            margin-bottom: 10px;
        }

        .login-logo h4 {
            margin: 0;
            font-weight: 700;
            color: var(--admin-dark);
        }

        .login-logo p {
            color: #6c757d;
            margin-top: 5px;
            margin-bottom: 0;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control {
            min-height: 45px;
        }

        .form-control:focus {
            border-color: var(--admin-primary);

            box-shadow:
                0 0 0 .2rem rgba(25, 135, 84, .15);
        }

        .btn-admin {
            background: var(--admin-primary);
            border-color: var(--admin-primary);
            color: #fff;

            min-height: 45px;
        }

        .btn-admin:hover {
            background: var(--admin-dark);
            border-color: var(--admin-dark);
            color: #fff;
        }

        .admin-badge {
            display: inline-block;

            background: #e9f7ef;
            color: var(--admin-dark);

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 13px;

            margin-bottom: 15px;
        }

    </style>

</head>


<body>


<div class="login-card">


    <!-- Logo -->

    <div class="login-logo">

        <?php
        $logoPath = __DIR__ . '/../assets/images/duet-logo.png';

        if (file_exists($logoPath)):
        ?>

            <img
                src="<?= APP_URL ?>/assets/images/duet-logo.png"
                alt="DUET Logo"
            >

        <?php endif; ?>


        <div>

            <span class="admin-badge">

                <i class="bi bi-shield-lock me-1"></i>

                Administrator

            </span>

        </div>


        <h4>
            Smart DUET
        </h4>

        <p>
            Admin Panel Login
        </p>

    </div>



    <!-- Flash Message -->

    <?php if ($flash): ?>

        <div
            class="alert alert-<?= e($flash['type']) ?>"
        >

            <?= e($flash['message']) ?>

        </div>

    <?php endif; ?>



    <!-- Login Form -->

    <form
        method="POST"
        action="<?php echo APP_URL; ?>/admin/actions/login_action.php"
    >


        <!-- CSRF -->

        <?php
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] =
                bin2hex(random_bytes(32));
        }
        ?>

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($_SESSION['csrf_token']) ?>"
        >



        <!-- Email -->

        <div class="mb-3">

            <label class="form-label">
                Admin Email
            </label>

            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="Enter admin email"
                autocomplete="email"
                required
            >

        </div>



        <!-- Password -->

        <div class="mb-4">

            <label class="form-label">
                Password
            </label>

            <div class="position-relative">

                <input
                    type="password"
                    name="password"
                    id="adminPassword"
                    class="form-control pe-5"
                    placeholder="Enter password"
                    autocomplete="current-password"
                    required
                >

                <!-- Show / Hide Password Button -->

                <button
                    type="button"
                    class="btn position-absolute top-50 end-0 translate-middle-y border-0"
                    onclick="toggleAdminPassword()"
                    style="color:#6c757d; z-index:5;"
                    aria-label="Show password"
                >

                    <i
                        class="bi bi-eye"
                        id="adminPasswordIcon"
                    ></i>

                </button>

            </div>

        </div>



        <!-- Login -->

        <button
            type="submit"
            class="btn btn-admin w-100"
        >

            <i class="bi bi-box-arrow-in-right me-1"></i>

            Admin Login

        </button>


    </form>


    <!-- Back -->

    <div class="text-center mt-4">

        <a
            href="<?= APP_URL ?>/login.php"
            class="text-decoration-none text-muted"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Applicant Login

        </a>

    </div>


</div>



<!-- =====================================================
     PASSWORD SHOW / HIDE
====================================================== -->

<script>

function toggleAdminPassword() {

    const password =
        document.getElementById('adminPassword');

    const icon =
        document.getElementById('adminPasswordIcon');


    if (password.type === 'password') {

        password.type = 'text';

        icon.classList.remove('bi-eye');

        icon.classList.add('bi-eye-slash');

    } else {

        password.type = 'password';

        icon.classList.remove('bi-eye-slash');

        icon.classList.add('bi-eye');

    }

}

</script>


</body>

</html>