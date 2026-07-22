/*
|--------------------------------------------------------------------------
| PDC Inventory Management System
| Layout JavaScript
|--------------------------------------------------------------------------
*/

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Sidebar Toggle
    |--------------------------------------------------------------------------
    */

    $("#sidebarToggle").on("click", function () {

        if ($(window).width() <= 991) {

            $("#sidebar").toggleClass("show");

        } else {

            $("#sidebar").toggleClass("collapsed");

            $(".page-wrapper").toggleClass("expanded");

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Close Sidebar on Mobile
    |--------------------------------------------------------------------------
    */

    $(document).on("click", function (e) {

        if ($(window).width() <= 991) {

            if (
                !$(e.target).closest("#sidebar").length &&
                !$(e.target).closest("#sidebarToggle").length
            ) {

                $("#sidebar").removeClass("show");

            }

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Window Resize
    |--------------------------------------------------------------------------
    */

    $(window).on("resize", function () {

        if ($(window).width() > 991) {

            $("#sidebar").removeClass("show");

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Active Sidebar Menu
    |--------------------------------------------------------------------------
    */

    var current = window.location.pathname;

    $(".sidebar-menu a").each(function () {

        var href = $(this).attr("href");

        if (!href) return;

        if (current.indexOf(href) !== -1) {

            $(".sidebar-menu li").removeClass("active");

            $(this).parent().addClass("active");

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Auto Hide Alerts
    |--------------------------------------------------------------------------
    */

    setTimeout(function () {

        $(".alert").fadeTo(500, 0).slideUp(500, function () {

            $(this).remove();

        });

    }, 4000);

});


/*
|--------------------------------------------------------------------------
| Live Clock
|--------------------------------------------------------------------------
*/

function updateClock() {

    const now = new Date();

    document.getElementById("currentDate").innerHTML =
        now.toLocaleDateString("en-PH", {

            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric"

        });

    document.getElementById("currentTime").innerHTML =
        now.toLocaleTimeString("en-PH");

}

updateClock();

setInterval(updateClock, 1000);


/*
|--------------------------------------------------------------------------
| Keyboard Shortcuts
|--------------------------------------------------------------------------
|
| Alt + M = Toggle Sidebar
| Alt + D = Dashboard
| Esc = Close Sidebar (Mobile)
|--------------------------------------------------------------------------
*/

document.addEventListener("keydown", function (e) {

    /*
    |--------------------------------------------------------------------------
    | Alt + M
    |--------------------------------------------------------------------------
    */

    if (e.altKey && e.key.toLowerCase() === "m") {

        e.preventDefault();

        document.getElementById("sidebarToggle").click();

    }

    /*
    |--------------------------------------------------------------------------
    | Alt + D
    |--------------------------------------------------------------------------
    */

    if (e.altKey && e.key.toLowerCase() === "d") {

        e.preventDefault();

        window.location.href =
            window.location.origin +
            "/pdc-inventory/pages/" +
            getDashboard();

    }

    /*
    |--------------------------------------------------------------------------
    | ESC
    |--------------------------------------------------------------------------
    */

    if (e.key === "Escape") {

        $("#sidebar").removeClass("show");

    }

});


/*
|--------------------------------------------------------------------------
| Dashboard Resolver
|--------------------------------------------------------------------------
*/

function getDashboard() {

    let path = window.location.pathname;

    if (path.includes("/admin/"))
        return "admin/dashboard.php";

    if (path.includes("/accounting/"))
        return "accounting/dashboard.php";

    if (path.includes("/warehouse/"))
        return "warehouse/dashboard.php";

    if (path.includes("/store/"))
        return "store/dashboard.php";

    if (path.includes("/spectator/"))
        return "spectator/dashboard.php";

    return "admin/dashboard.php";

}