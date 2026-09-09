<?php

/*
|--------------------------------------------------------------------------
| COMMON HEADER
|--------------------------------------------------------------------------
| Used by all applicant pages.
|
| IMPORTANT:
| - Existing PHP functionality is preserved.
| - Existing page structure is preserved.
| - Only visual design has been improved.
|--------------------------------------------------------------------------
*/


// --------------------------------------------------
// Load Application Configuration
// --------------------------------------------------

require_once __DIR__ . '/../config/config.php';


// --------------------------------------------------
// Default Page Title
// --------------------------------------------------

$pageTitle = $pageTitle ?? 'Smart DUET Admission';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <!-- =================================================
         PAGE TITLE
    ================================================== -->

    <title>

        <?= htmlspecialchars($pageTitle); ?>

        |

        Smart DUET Admission

    </title>



    <!-- =================================================
         BOOTSTRAP
    ================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >



    <!-- =================================================
         BOOTSTRAP ICONS
    ================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >



    <!-- =================================================
         GOOGLE FONT
    ================================================== -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >



    <!-- =================================================
         EXISTING CSS
    ================================================== -->

    <link
        rel="stylesheet"
        href="<?= APP_URL ?>/css/style.css"
    >



    <!-- =================================================
         DUET FAVICON
    ================================================== -->

    <link
        rel="icon"
        href="<?= APP_URL ?>/assets/images/duet-logo.png"
    >



    <!-- =================================================
         HEADER MATERIAL UI
    ================================================== -->

    <style>

        /* =================================================
           GLOBAL FONT
        ================================================= */

        body {

            font-family:
                "Inter",
                "Segoe UI",
                "Noto Sans",
                Arial,
                sans-serif;

        }


        /* =================================================
           TOP HEADER
        ================================================= */

        .top-header {

            min-height: 78px;

            padding:
                0 28px;

            background:
                rgba(255, 255, 255, 0.97);

            border-bottom:
                1px solid #e8edf4;

            box-shadow:
                0 3px 18px
                rgba(15, 23, 42, 0.055);

            display: flex;

            align-items: center;

            justify-content: space-between;

            position: sticky;

            top: 0;

            z-index: 1000;

            backdrop-filter:
                blur(12px);

        }


        /* =================================================
           HEADER LEFT
        ================================================== */

        .header-left {

            display: flex;

            align-items: center;

            gap: 16px;

            min-width: 0;

        }


        /* =================================================
           MOBILE MENU BUTTON
        ================================================== */

        .header-left
        .btn-light {

            width: 44px;

            height: 44px;

            padding: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background: #f5f8fc;

            border:
                1px solid #e3eaf2 !important;

            color: #0d6efd;

            font-size: 21px;

            box-shadow:
                0 3px 9px
                rgba(15, 23, 42, 0.05);

            transition:
                all .22s ease;

        }


        .header-left
        .btn-light:hover {

            background: #eaf2ff;

            border-color: #cfe0ff !important;

            color: #0b5ed7;

            transform:
                translateY(-2px);

        }


        /* =================================================
           HEADER PAGE TITLE
        ================================================== */

        .page-title {

            margin: 0;

            font-family:
                "Inter",
                "Segoe UI",
                sans-serif;

            font-size: 21px !important;

            line-height: 1.25;

            font-weight: 800 !important;

            letter-spacing: -0.45px;

            color: #172033;

        }


        /* =================================================
           HEADER SUBTITLE
        ================================================== */

        .header-left small {

            display: block;

            margin-top: 4px;

            font-size: 11.5px;

            line-height: 1.4;

            font-weight: 500;

            color: #7b8798;

        }


        /* =================================================
           HEADER RIGHT
        ================================================== */

        .header-right {

            display: flex;

            align-items: center;

            gap: 12px;

        }


        /* =================================================
           PROFILE BUTTON
        ================================================== */

        .profile-dropdown > button {

            width: 48px;

            height: 48px;

            padding: 3px !important;

            border-radius: 50% !important;

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #5966e9
                );

            border: 0 !important;

            box-shadow:
                0 5px 15px
                rgba(13, 110, 253, .20);

            transition:
                transform .25s ease,
                box-shadow .25s ease;

        }


        .profile-dropdown > button:hover {

            transform:
                translateY(-2px)
                scale(1.04);

            box-shadow:
                0 8px 21px
                rgba(13, 110, 253, .28);

        }


        /* Remove Bootstrap arrow */

        .profile-dropdown > button::after {

            display: none;

        }


        /* =================================================
           PROFILE IMAGE
        ================================================== */

        .profile-dropdown img {

            width: 42px;

            height: 42px;

            object-fit: cover;

            display: block;

            border-radius: 50%;

            border:
                2px solid #ffffff;

        }


        /* =================================================
           PROFILE DROPDOWN
        ================================================== */

        .profile-dropdown
        .dropdown-menu {

            min-width: 205px;

            margin-top: 12px !important;

            padding: 8px;

            border:
                1px solid #e7ecf3;

            border-radius: 15px;

            background:
                rgba(255,255,255,.98);

            box-shadow:
                0 14px 35px
                rgba(15, 23, 42, .13);

            animation:
                profileDropdown .18s ease;

        }


        @keyframes profileDropdown {

            from {

                opacity: 0;

                transform:
                    translateY(-7px);

            }

            to {

                opacity: 1;

                transform:
                    translateY(0);

            }

        }


        /* =================================================
           DROPDOWN ITEM
        ================================================== */

        .profile-dropdown
        .dropdown-item {

            padding:
                10px 12px;

            border-radius: 10px;

            font-size: 13px;

            font-weight: 550;

            color: #475569;

            transition:
                all .18s ease;

        }


        .profile-dropdown
        .dropdown-item i {

            display: inline-flex;

            width: 20px;

            color: #0d6efd;

        }


        .profile-dropdown
        .dropdown-item:hover {

            background:
                linear-gradient(
                    135deg,
                    #edf5ff,
                    #f5f1ff
                );

            color: #0d6efd;

            transform:
                translateX(2px);

        }


        .profile-dropdown
        .dropdown-item.text-danger {

            color: #dc3545 !important;

        }


        .profile-dropdown
        .dropdown-item.text-danger:hover {

            background: #fff1f2;

            color: #dc3545 !important;

        }


        .profile-dropdown
        .dropdown-item.text-danger i {

            color: #dc3545;

        }


        /* =================================================
           MAIN CONTENT
        ================================================== */

        .main-content {

            background:
                #f7f9fc;

        }


        /* =================================================
           SMOOTH PAGE CONTENT
        ================================================== */

        .main-content > * {

            animation:
                pageContentFade .35s ease;

        }


        @keyframes pageContentFade {

            from {

                opacity: .65;

                transform:
                    translateY(3px);

            }

            to {

                opacity: 1;

                transform:
                    translateY(0);

            }

        }


        /* =================================================
           MOBILE
        ================================================== */

        @media (max-width: 768px) {

            .top-header {

                min-height: 66px;

                padding:
                    0 15px;

            }


            .page-title {

                font-size: 17px !important;

                letter-spacing: -.25px;

            }


            .header-left small {

                font-size: 9.5px;

                margin-top: 2px;

            }


            .header-left {

                gap: 10px;

            }


            .header-left
            .btn-light {

                width: 40px;

                height: 40px;

                font-size: 19px;

            }


            .profile-dropdown > button {

                width: 43px;

                height: 43px;

            }


            .profile-dropdown img {

                width: 37px;

                height: 37px;

            }

        }


        @media (max-width: 430px) {

            .header-left small {

                display: none;

            }


            .page-title {

                font-size: 16px !important;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     APP WRAPPER
====================================================== -->

<div class="app-wrapper">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <?php

    require_once __DIR__ . '/sidebar.php';

    ?>



    <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="main-content">


        <!-- =================================================
             TOP HEADER
        ================================================== -->

        <header class="top-header">


            <!-- =================================================
                 HEADER LEFT
            ================================================== -->

            <div class="header-left">


                <!-- Mobile Menu -->

                <button
                    type="button"
                    class="btn btn-light d-lg-none"
                    onclick="toggleSidebar()"
                    aria-label="Open Menu"
                >

                    <i class="bi bi-list"></i>

                </button>


                <!-- Page Information -->

                <div>

                    <h5 class="page-title">

                        <?= htmlspecialchars($pageTitle); ?>

                    </h5>


                    <small>

                        Smart DUET Admission Management System

                    </small>

                </div>


            </div>



            <!-- =================================================
                 HEADER RIGHT
            ================================================== -->

            <div class="header-right">


                <div class="dropdown profile-dropdown">


                    <!-- Profile Button -->

                    <button
                        class="btn p-0 border-0 dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Profile Menu"
                    >

                        <img
                            src="<?= APP_URL ?>/assets/images/profile.jpg"
                            alt="Profile"
                        >

                    </button>



                    <!-- =================================================
                         PROFILE DROPDOWN
                    ================================================== -->

                    <ul
                        class="dropdown-menu
                               dropdown-menu-end"
                    >


                        <!-- Profile -->

                        <li>

                            <a
                                class="dropdown-item"
                                href="<?= APP_URL ?>/profile.php"
                            >

                                <i
                                    class="bi
                                           bi-person-circle
                                           me-2"
                                ></i>

                                Profile

                            </a>

                        </li>


                        <!-- Settings -->

                        <li>

                            <a
                                class="dropdown-item"
                                href="<?= APP_URL ?>/settings.php"
                            >

                                <i
                                    class="bi
                                           bi-gear
                                           me-2"
                                ></i>

                                Settings

                            </a>

                        </li>


                        <li>

                            <hr class="dropdown-divider">

                        </li>


                        <!-- Logout -->

                        <li>

                            <a
                                class="dropdown-item text-danger"
                                href="<?= APP_URL ?>/logout.php"
                            >

                                <i
                                    class="bi
                                           bi-box-arrow-right
                                           me-2"
                                ></i>

                                Logout

                            </a>

                        </li>


                    </ul>


                </div>


            </div>


        </header>