<?php

/*
|--------------------------------------------------------------------------
| SIDEBAR
|--------------------------------------------------------------------------
| Common sidebar for all applicant pages.
|
| IMPORTANT:
| - Existing menu links are preserved.
| - Existing active page logic is preserved.
| - Only UI / visual effects have been improved.
|--------------------------------------------------------------------------
*/


// --------------------------------------------------
// Current Page
// --------------------------------------------------

$currentPage =
    basename($_SERVER['PHP_SELF']);


// --------------------------------------------------
// Active Page Function
// --------------------------------------------------

function isActivePage($pageName)
{
    global $currentPage;

    return
        $currentPage === $pageName
        ? 'active'
        : '';
}

?>


<style>

/* =========================================================
   SMART DUET SIDEBAR
========================================================= */


/* =========================================================
   SIDEBAR BASE
========================================================= */

.sidebar {

    background:
        linear-gradient(
            180deg,
            #081b33 0%,
            #0d2748 48%,
            #0a203c 100%
        ) !important;

    border-right:
        1px solid rgba(255,255,255,.055);

    box-shadow:
        6px 0 28px
        rgba(15,23,42,.13);

}


/* =========================================================
   SIDEBAR LOGO
========================================================= */

.sidebar-logo {

    min-height: 82px;

    padding:
        17px 17px;

    display: flex;

    align-items: center;

    gap: 12px;

    position: relative;

}


.sidebar-logo::after {

    content: "";

    position: absolute;

    left: 16px;

    right: 16px;

    bottom: 0;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.16),
            transparent
        );

}


.sidebar-logo img {

    width: 48px;

    height: 48px;

    object-fit: contain;

    padding: 5px;

    border-radius: 14px;

    background: #ffffff;

    box-shadow:
        0 5px 16px
        rgba(0,0,0,.22);

    transition:
        transform .3s ease,
        box-shadow .3s ease;

}


.sidebar-logo:hover img {

    transform:
        scale(1.05)
        rotate(-3deg);

    box-shadow:
        0 8px 20px
        rgba(0,0,0,.28);

}


.sidebar-logo span {

    color: #ffffff;

    font-family:
        "Inter",
        "Segoe UI",
        sans-serif;

    font-size: 15px;

    line-height: 1.4;

    font-weight: 750;

    letter-spacing: -.2px;

}


/* =========================================================
   MENU AREA
========================================================= */

.sidebar-menu {

    padding:
        9px 0 22px;

}


/* =========================================================
   MENU TITLE
========================================================= */

.sidebar .menu-title {

    padding:
        17px 18px 7px;

    color:
        rgba(255,255,255,.40);

    font-family:
        "Inter",
        "Segoe UI",
        sans-serif;

    font-size: 9.5px;

    font-weight: 750;

    text-transform: uppercase;

    letter-spacing: 1.05px;

}


/* =========================================================
   SIDEBAR LINKS
========================================================= */

.sidebar-menu > a {

    position: relative;

    display: flex;

    align-items: center;

    gap: 12px;

    margin:
        4px 10px;

    padding:
        11px 13px;

    min-height: 44px;

    border-radius: 12px;

    color:
        rgba(255,255,255,.68);

    font-family:
        "Inter",
        "Segoe UI",
        sans-serif;

    font-size: 12.5px;

    font-weight: 550;

    text-decoration: none;

    overflow: hidden;

    transition:
        background .22s ease,
        color .22s ease,
        transform .22s ease,
        box-shadow .22s ease;

}


/* =========================================================
   HOVER LIGHT EFFECT
========================================================= */

.sidebar-menu > a::before {

    content: "";

    position: absolute;

    left: -120%;

    top: 0;

    width: 100%;

    height: 100%;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.075),
            transparent
        );

    transition:
        left .5s ease;

}


.sidebar-menu > a:hover::before {

    left: 120%;

}


/* =========================================================
   SIDEBAR ICON
========================================================= */

.sidebar-menu > a i {

    width: 23px;

    min-width: 23px;

    height: 23px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 16px;

    color:
        rgba(255,255,255,.55);

    transition:
        color .22s ease,
        transform .22s ease;

}


/* =========================================================
   HOVER
========================================================= */

.sidebar-menu > a:hover {

    color: #ffffff;

    background:
        rgba(255,255,255,.075);

    transform:
        translateX(3px);

}


.sidebar-menu > a:hover i {

    color: #69a8ff;

    transform:
        scale(1.12);

}


/* =========================================================
   ACTIVE MENU
========================================================= */

.sidebar-menu > a.active {

    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #0d6efd 0%,
            #4f67e8 100%
        );

    box-shadow:
        0 7px 18px
        rgba(13,110,253,.25);

    transform:
        translateX(2px);

}


/* =========================================================
   ACTIVE LEFT INDICATOR
========================================================= */

.sidebar-menu > a.active::after {

    content: "";

    position: absolute;

    left: 0;

    top: 23%;

    width: 4px;

    height: 54%;

    border-radius:
        0 5px 5px 0;

    background: #ffffff;

    box-shadow:
        0 0 8px
        rgba(255,255,255,.35);

}


/* =========================================================
   ACTIVE ICON
========================================================= */

.sidebar-menu > a.active i {

    color: #ffffff;

    transform:
        scale(1.08);

}


/* =========================================================
   SCROLLBAR
========================================================= */

.sidebar::-webkit-scrollbar {

    width: 5px;

}


.sidebar::-webkit-scrollbar-track {

    background: transparent;

}


.sidebar::-webkit-scrollbar-thumb {

    background:
        rgba(255,255,255,.14);

    border-radius: 10px;

}


.sidebar::-webkit-scrollbar-thumb:hover {

    background:
        rgba(255,255,255,.26);

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 991px) {

    .sidebar-menu > a:hover {

        transform: none;

    }


    .sidebar-menu > a.active {

        transform: none;

    }

}

</style>


<!-- =====================================================
     SIDEBAR
====================================================== -->

<aside
    class="sidebar"
    id="sidebar"
>


    <!-- =================================================
         SIDEBAR LOGO
    ================================================== -->

    <div class="sidebar-logo">


        <img
            src="<?= APP_URL ?>/assets/images/duet-logo.png"
            alt="DUET Logo"
        >


        <span>

            Smart DUET
            <br>
            Admission

        </span>


    </div>



    <!-- =================================================
         SIDEBAR MENU
    ================================================== -->

    <div class="sidebar-menu">


        <!-- =================================================
             MAIN MENU
        ================================================== -->

        <div class="menu-title">

            Main Menu

        </div>


        <!-- Dashboard -->

        <a
            href="<?= APP_URL ?>/dashboard.php"
            class="<?= isActivePage('dashboard.php'); ?>"
        >

            <i class="bi bi-speedometer2"></i>

            <span>
                Dashboard
            </span>

        </a>


        <!-- Eligibility -->

        <a
            href="<?= APP_URL ?>/eligibility.php"
            class="<?= isActivePage('eligibility.php'); ?>"
        >

            <i class="bi bi-shield-check"></i>

            <span>
                Eligibility Check
            </span>

        </a>


        <!-- Admission Notice -->

        <a
            href="<?= APP_URL ?>/notices.php"
            class="<?= isActivePage('notices.php'); ?>"
        >

            <i class="bi bi-megaphone"></i>

            <span>
                Admission Notice
            </span>

        </a>


        <!-- Department Information -->

        <a
            href="<?= APP_URL ?>/departments.php"
            class="<?= isActivePage('departments.php'); ?>"
        >

            <i class="bi bi-buildings"></i>

            <span>
                Department Info
            </span>

        </a>



        <!-- =================================================
             ADMISSION
        ================================================== -->

        <div class="menu-title">

            Admission

        </div>


        <!-- Apply -->

        <a
            href="<?= APP_URL ?>/apply.php"
            class="<?= isActivePage('apply.php'); ?>"
        >

            <i class="bi bi-file-earmark-plus"></i>

            <span>
                Apply for Admission
            </span>

        </a>


        <!-- Application Status -->

        <a
            href="<?= APP_URL ?>/application-status.php"
            class="<?= isActivePage('application-status.php'); ?>"
        >

            <i class="bi bi-hourglass-split"></i>

            <span>
                Application Status
            </span>

        </a>


        <!-- Admit Card -->

        <a
            href="<?= APP_URL ?>/admit-card.php"
            class="<?= isActivePage('admit-card.php'); ?>"
        >

            <i class="bi bi-person-vcard"></i>

            <span>
                Admit Card
            </span>

        </a>


        <!-- Result -->

        <a
            href="<?= APP_URL ?>/result.php"
            class="<?= isActivePage('result.php'); ?>"
        >

            <i class="bi bi-trophy"></i>

            <span>
                View Result
            </span>

        </a>


    </div>

</aside>