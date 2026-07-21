<?php

require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/flash.php';

$pageTitle = "Login";

/*
|--------------------------------------------------------------------------
| Already Logged In
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['user_id'])) {

    switch ($_SESSION['role']) {

        case 'super_admin':
            redirect('/pages/admin/dashboard.php');
            break;

        case 'accounting':
            redirect('/pages/accounting/dashboard.php');
            break;

        case 'warehouse':
            redirect('/pages/warehouse/dashboard.php');
            break;

        case 'store':
            redirect('/pages/store/dashboard.php');
            break;

        case 'spectator':
            redirect('/pages/spectator/dashboard.php');
            break;

        default:
            session_unset();
            session_destroy();
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title><?= SYSTEM_SHORT_NAME ?> | Login</title>

    <link rel="icon"
          href="../assets/img/favicon.png">

    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css"
          rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <link href="../assets/css/login.style.css"
          rel="stylesheet">

</head>

<body>

<div class="login-wrapper">

    <div class="login-card shadow">

        <div class="text-center mb-4">

            <img src="../assets/img/pdcxdunkin_logo.png"
                 class="logo mb-3"
                 onerror="this.style.display='none';">

            <h3 class="system-title">

                <?= SYSTEM_SHORT_NAME ?>

            </h3>

            <p class="system-subtitle">

                Inventory • Accounting • Warehouse

            </p>

        </div>

        <?php showFlash(); ?>

        <form
            action="process.login.php"
            method="POST"
            id="loginForm"
            autocomplete="off">

            <div class="mb-3">

                <label class="form-label">

                    Username

                </label>

                <input
                    type="text"
                    class="form-control"
                    name="username"
                    autofocus
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">

                    Password

                </label>

                <div class="input-group">

                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        id="password"
                        required>

                    <button
                        class="btn btn-outline-secondary"
                        type="button"
                        id="togglePassword">

                        <i class="bi bi-eye"></i>

                    </button>

                </div>

            </div>

            <div class="form-check mb-4">

                <input
                    class="form-check-input"
                    type="checkbox"
                    id="remember"
                    name="remember">

                <label
                    class="form-check-label"
                    for="remember">

                    Remember Me

                </label>

            </div>

            <button
                type="submit"
                class="btn btn-primary w-100"
                id="loginButton">

                <span id="btnText">

                    <i class="bi bi-box-arrow-in-right"></i>

                    Sign In

                </span>

            </button>

        </form>

        <hr>

        <div class="text-center footer-text">

            <small>

                <?= SYSTEM_NAME ?>

                <br>

                Version <?= SYSTEM_VERSION ?>

                <br>

                © <?= date('Y') ?> PDC IT Department

            </small>

        </div>

    </div>

</div>

<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>

const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');

togglePassword.addEventListener('click', function () {

    if (password.type === "password") {

        password.type = "text";

        this.innerHTML = '<i class="bi bi-eye-slash"></i>';

    } else {

        password.type = "password";

        this.innerHTML = '<i class="bi bi-eye"></i>';

    }

});

document.getElementById('loginForm').addEventListener('submit', function () {

    const btn = document.getElementById('loginButton');

    btn.disabled = true;

    btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Signing In...';

});

</script>

</body>

</html>