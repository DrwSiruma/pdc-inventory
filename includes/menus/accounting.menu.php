<?php

/*
|--------------------------------------------------------------------------
| Accounting Menu
|--------------------------------------------------------------------------
*/

?>

<!-- ===========================
Dashboard
=========================== -->

<li class="<?= isMenuActive('dashboard.php'); ?>">

    <a href="<?= BASE_URL ?>/pages/accounting/dashboard.php">

        <i class="bi bi-speedometer2"></i>

        <span>Dashboard</span>

    </a>

</li>

<!-- ===========================
ITEM INVENTORY
=========================== -->

<li class="sidebar-divider">

    ITEM INVENTORY

</li>

<li class="<?= isMenuActive('index.php', 'item_beginning'); ?>">

    <a href="<?= BASE_URL ?>/pages/accounting/item_beginning/">

        <i class="bi bi-box-seam"></i>

        <span>Item Beginning</span>

    </a>

</li>

<!-- ===========================
PRODUCT INVENTORY
=========================== -->

<li class="sidebar-divider">

    PRODUCT INVENTORY

</li>

<li class="<?= isMenuActive('index.php', 'product_inventory'); ?>">

    <a href="<?= BASE_URL ?>/pages/accounting/product_inventory/">

        <i class="bi bi-basket-fill"></i>

        <span>Product Inventory</span>

    </a>

</li>

<li class="<?= isMenuActive('index.php', 'throwaway'); ?>">

    <a href="<?= BASE_URL ?>/pages/accounting/throwaway/">

        <i class="bi bi-trash-fill"></i>

        <span>Throw Away Verification</span>

    </a>

</li>

<li class="<?= isMenuActive('index.php', 'variance'); ?>">

    <a href="<?= BASE_URL ?>/pages/accounting/variance/">

        <i class="bi bi-calculator-fill"></i>

        <span>Product Variance</span>

    </a>

</li>

<!-- ===========================
REPORTS
=========================== -->

<li class="sidebar-divider">

    REPORTS

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/accounting/reports/product_variance.php">

        <i class="bi bi-file-earmark-bar-graph-fill"></i>

        <span>Product Variance</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/accounting/reports/item_inventory.php">

        <i class="bi bi-file-earmark-text-fill"></i>

        <span>Item Inventory</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/accounting/reports/throwaway.php">

        <i class="bi bi-file-earmark-check-fill"></i>

        <span>Throw Away Report</span>

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/accounting/reports/monthly_summary.php">

        <i class="bi bi-calendar3"></i>

        <span>Monthly Summary</span>

    </a>

</li>

<!-- ===========================
ACCOUNT
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