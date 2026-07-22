<?php

/*
|--------------------------------------------------------------------------
| Administrator Menu
|--------------------------------------------------------------------------
*/

?>

<!-- ===========================
Dashboard
=========================== -->

<li class="<?= isMenuActive('dashboard.php'); ?>">

    <a href="<?= BASE_URL ?>/pages/admin/dashboard.php">

        <i class="bi bi-speedometer2"></i>

        <span>Dashboard</span>

    </a>

</li>

<!-- ===========================
Administration
=========================== -->

<li class="sidebar-divider">

    ADMINISTRATION

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/admin/users/">

        <i class="bi bi-people-fill"></i>

        <span>Users</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/admin/locations/">

        <i class="bi bi-geo-alt-fill"></i>

        <span>Locations</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/admin/products/">

        <i class="bi bi-box-seam"></i>

        <span>Products</span>

    </a>

</li>

<!-- ===========================
Operations
=========================== -->

<li class="sidebar-divider">

    OPERATIONS

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/admin/accounting/">

        <i class="bi bi-calculator-fill"></i>

        <span>Accounting</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/admin/reports/">

        <i class="bi bi-bar-chart-fill"></i>

        <span>Reports</span>

    </a>

</li>

<!-- ===========================
System
=========================== -->

<li class="sidebar-divider">

    SYSTEM

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/admin/settings/">

        <i class="bi bi-gear-fill"></i>

        <span>Settings</span>

    </a>

</li>

<!-- ===========================
Account
=========================== -->

<li class="sidebar-divider">

    ACCOUNT

</li>

<li>

    <a href="#">

        <i class="bi bi-person-circle"></i>

        <span>My Profile</span>

    </a>

</li>

<li>

    <a href="#">

        <i class="bi bi-key-fill"></i>

        <span>Change Password</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/includes/logout.php">

        <i class="bi bi-box-arrow-right text-danger"></i>

        <span>Logout</span>

    </a>

</li>