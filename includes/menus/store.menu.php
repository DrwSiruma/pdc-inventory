<?php

/*
|--------------------------------------------------------------------------
| Store Menu
|--------------------------------------------------------------------------
| Product Variance Computation System
|--------------------------------------------------------------------------
*/

?>

<!-- ===========================
Dashboard
=========================== -->

<li class="<?= isMenuActive('dashboard.php'); ?>">

    <a href="<?= BASE_URL ?>/pages/store/dashboard.php">

        <i class="bi bi-speedometer2"></i>

        <span>Dashboard</span>

    </a>

</li>

<!-- ===========================
Daily Operations
=========================== -->

<li class="sidebar-divider">

    DAILY OPERATIONS

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/store/delivery_validation/">

        <i class="bi bi-truck"></i>

        <span>Delivery Validation</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/store/product_inventory/">

        <i class="bi bi-basket-fill"></i>

        <span>Product Inventory</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/store/throwaway/">

        <i class="bi bi-trash-fill"></i>

        <span>Product Throw Away</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/store/submit_business_day/">

        <i class="bi bi-send-check-fill"></i>

        <span>Submit Business Day</span>

    </a>

</li>

<!-- ===========================
Monitoring
=========================== -->

<li class="sidebar-divider">

    MONITORING

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/store/business_day_status/">

        <i class="bi bi-check2-square"></i>

        <span>Business Day Status</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/store/history/">

        <i class="bi bi-clock-history"></i>

        <span>Submission History</span>

    </a>

</li>

<!-- ===========================
Reports
=========================== -->

<li class="sidebar-divider">

    REPORTS

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/store/reports/">

        <i class="bi bi-file-earmark-bar-graph-fill"></i>

        <span>Reports</span>

    </a>

</li>

<!-- ===========================
Account
=========================== -->

<li class="sidebar-divider">

    ACCOUNT

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/account/profile.php">

        <i class="bi bi-person-circle"></i>

        <span>My Profile</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/account/change_password.php">

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