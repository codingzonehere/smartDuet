<?php

/*
|--------------------------------------------------------------------------
| COMMON FOOTER
|--------------------------------------------------------------------------
| Closes main-content and loads common JavaScript.
|
| IMPORTANT:
| Existing functionality is preserved.
|--------------------------------------------------------------------------
*/

?>


<!-- =====================================================
     FOOTER
====================================================== -->

<footer class="smart-duet-footer">


    <div class="footer-main">


        <!-- =================================================
             BRAND
        ================================================== -->

        <div class="footer-brand">


            <img
                src="<?= APP_URL ?>/assets/images/duet-logo.png"
                alt="DUET Logo"
            >


            <div>

                <div class="footer-title">

                    Smart DUET

                </div>


                <div class="footer-subtitle">

                    Admission Management System

                </div>

            </div>


        </div>



        <!-- =================================================
             UNIVERSITY
        ================================================== -->

        <div class="footer-university">

            <i class="bi bi-mortarboard-fill"></i>

            <span>

                Dhaka University of Engineering & Technology,
                Gazipur

            </span>

        </div>


    </div>



    <!-- =================================================
         FOOTER BOTTOM
    ================================================== -->

    <div class="footer-bottom">


        <span>

            © <?= date('Y'); ?> Smart DUET

        </span>


        <span>

            All Rights Reserved

        </span>


    </div>


</footer>



<!-- =====================================================
     FOOTER STYLE
====================================================== -->

<style>

/* =========================================================
   SMART DUET FOOTER
========================================================= */

.smart-duet-footer {

    margin-top: 25px;

    padding:
        16px 26px 10px;

    background:
        #ffffff;

    border-top:
        1px solid #e8edf4;

    box-shadow:
        0 -3px 14px
        rgba(15,23,42,.025);

    font-family:
        "Inter",
        "Segoe UI",
        "Noto Sans",
        Arial,
        sans-serif;

}


/* =========================================================
   FOOTER MAIN
========================================================= */

.footer-main {

    max-width: 1250px;

    margin: 0 auto;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

}


/* =========================================================
   BRAND
========================================================= */

.footer-brand {

    display: flex;

    align-items: center;

    gap: 9px;

}


.footer-brand img {

    width: 30px;

    height: 30px;

    object-fit: contain;

    padding: 2px;

    border-radius: 8px;

    background: #f1f6ff;

}


.footer-title {

    font-size: 11.5px;

    line-height: 1.2;

    font-weight: 750;

    color: #334155;

}


.footer-subtitle {

    margin-top: 2px;

    font-size: 9px;

    color: #94a3b8;

}


/* =========================================================
   UNIVERSITY
========================================================= */

.footer-university {

    display: flex;

    align-items: center;

    gap: 6px;

    font-size: 9.5px;

    color: #7b8798;

    text-align: right;

}


.footer-university i {

    color: #0d6efd;

    font-size: 11px;

}


/* =========================================================
   FOOTER BOTTOM
========================================================= */

.footer-bottom {

    max-width: 1250px;

    margin:
        10px auto 0;

    padding-top: 8px;

    border-top:
        1px solid #f0f2f5;

    display: flex;

    align-items: center;

    justify-content: space-between;

    font-size: 8.5px;

    color: #a0a9b5;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .smart-duet-footer {

        padding:
            14px 16px 9px;

    }


    .footer-main {

        flex-direction: column;

        align-items: flex-start;

        gap: 9px;

    }


    .footer-university {

        text-align: left;

    }


    .footer-bottom {

        margin-top: 8px;

        font-size: 8px;

    }

}


@media (max-width: 430px) {

    .footer-university {

        font-size: 8.5px;

    }


    .footer-bottom {

        flex-direction: column;

        align-items: flex-start;

        gap: 3px;

    }

}

</style>



<!-- =====================================================
     BOOTSTRAP JAVASCRIPT
====================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>



<!-- =====================================================
     OUR JAVASCRIPT
====================================================== -->

<script
    src="<?= APP_URL ?>/js/script.js">
</script>


</body>

</html>