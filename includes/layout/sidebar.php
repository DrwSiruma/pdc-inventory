<?php

/*
|--------------------------------------------------------------------------
| Sidebar
|--------------------------------------------------------------------------
| Loads the appropriate menu according to the logged-in user's role.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../session.php';

if (!isset($_SESSION['role'])) {
    exit('Unauthorized Access');
}

?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">

    <!-- Sidebar Header -->
    <div class="sidebar-header">

        <img src="<?= BASE_URL ?>/assets/img/pdcxdunkin_logo.png"
             class="sidebar-logo"
             alt="PDC Logo">

        <h5><?= SYSTEM_SHORT_NAME; ?></h5>

        <small>

            <?= ucwords(str_replace('_',' ',$_SESSION['role'])); ?>

        </small>

    </div>

    <!-- Sidebar Menu -->
    <ul class="sidebar-menu">

        <?php

        switch($_SESSION['role']){

            case 'super_admin':
                include __DIR__.'/../menus/admin.menu.php';
                break;

            case 'accounting':
                include __DIR__.'/../menus/accounting.menu.php';
                break;

            case 'warehouse':
                include __DIR__.'/../menus/warehouse.menu.php';
                break;

            case 'store':
                include __DIR__.'/../menus/store.menu.php';
                break;

            case 'spectator':
                include __DIR__.'/../menus/spectator.menu.php';
                break;

            default:

                echo '

                <li>

                    <a href="#">

                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>

                        <span>No menu available</span>

                    </a>

                </li>

                ';

        }

        ?>

    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">

        <div class="small text-center text-secondary">

            Version <?= SYSTEM_VERSION; ?>

            <br>

            <?= date('Y'); ?> © PDC IT Department

        </div>

    </div>

</aside>

<!-- Page Wrapper -->
<div class="page-wrapper">