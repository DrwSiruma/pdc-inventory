<?php

/*
|--------------------------------------------------------------------------
| Topbar
|--------------------------------------------------------------------------
| Shared Top Navigation
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../auth.php';

$user = currentUser();

?>

<header class="topbar">

    <!-- Left Side -->
    <div class="topbar-left">

        <button
            id="sidebarToggle"
            class="toggle-btn"
            type="button">

            <i class="bi bi-list"></i>

        </button>

        <div class="topbar-title">

            <h4><?= htmlspecialchars($pageTitle); ?></h4>

            <small>

                Welcome,

                <strong><?= htmlspecialchars($user['fullname']); ?></strong>

            </small>

        </div>

    </div>

    <!-- Right Side -->
    <div class="topbar-right">

        <!-- Date & Time -->
        <div class="system-clock">

            <div id="currentDate"></div>

            <small id="currentTime" class="text-muted"></small>

        </div>

        <!-- Notification -->
        <button
            class="btn btn-light position-relative me-2"
            type="button"
            title="Notifications">

            <i class="bi bi-bell"></i>

            <span class="badge badge-danger position-absolute"
                  style="top:5px;right:5px;font-size:10px;">

                0

            </span>

        </button>

        <!-- User -->
        <div class="dropdown">

            <button
                class="btn btn-light border dropdown-toggle"
                type="button"
                data-toggle="dropdown">

                <i class="bi bi-person-circle mr-2"></i>

                <?= htmlspecialchars($user['fullname']); ?>

            </button>

            <div class="dropdown-menu dropdown-menu-right shadow">

                <h6 class="dropdown-header">

                    <?= ucwords(str_replace('_',' ',$user['role'])); ?>

                </h6>

                <div class="dropdown-divider"></div>

                <a class="dropdown-item" href="<?= BASE_URL ?>/pages/account/profile.php">

                    <i class="bi bi-person mr-2"></i>&nbsp;My Profile

                </a>

                <a class="dropdown-item" href="<?= BASE_URL ?>/pages/account/change_password.php">

                    <i class="bi bi-key me-2"></i>&nbsp;Change Password

                </a>

                <a class="dropdown-item" href="#">

                    <i class="bi bi-gear mr-2"></i>

                    Preferences

                </a>

                <div class="dropdown-divider"></div>

                <a
                    class="dropdown-item text-danger"
                    href="<?= BASE_URL ?>/includes/logout.php">

                    <i class="bi bi-box-arrow-right mr-2"></i>

                    Logout

                </a>

            </div>

        </div>

    </div>

</header>