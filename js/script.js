/*
|--------------------------------------------------------------------------
| SMART DUET COMMON JAVASCRIPT
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| TOGGLE SIDEBAR
|--------------------------------------------------------------------------
| Used on mobile devices.
|--------------------------------------------------------------------------
*/

function toggleSidebar() {

    const sidebar = document.getElementById("sidebar");

    if (!sidebar) {
        return;
    }

    sidebar.classList.toggle("show");

}


/*
|--------------------------------------------------------------------------
| MOBILE SIDEBAR
|--------------------------------------------------------------------------
| Close sidebar when user clicks outside it.
|--------------------------------------------------------------------------
*/

document.addEventListener("click", function (event) {

    const sidebar =
        document.getElementById("sidebar");

    if (!sidebar) {
        return;
    }


    // Only run this on mobile/tablet
    if (window.innerWidth < 992) {

        const clickedInsideSidebar =
            sidebar.contains(event.target);

        const clickedMenuButton =
            event.target.closest(
                "[onclick='toggleSidebar()']"
            );


        // Close sidebar if clicked outside
        if (
            !clickedInsideSidebar &&
            !clickedMenuButton
        ) {

            sidebar.classList.remove("show");

        }

    }

});