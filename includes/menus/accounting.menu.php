<?php

$currentPage = basename($_SERVER['PHP_SELF']);

?>

<li>

    <a href="<?= BASE_URL ?>/pages/accounting/dashboard.php">

        <i class="bi bi-speedometer2"></i>

        Dashboard

    </a>

</li>

<li class="sidebar-divider">

    ACCOUNTING

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/accounting/daily_entries/">

        <i class="bi bi-journal-check"></i>

        Daily Entries

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/accounting/approvals/">

        <i class="bi bi-check2-square"></i>

        Approvals

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/pages/accounting/reports/">

        <i class="bi bi-file-earmark-bar-graph"></i>

        Reports

    </a>

</li>

<li>

    <a href="<?= BASE_URL ?>/includes/logout.php">

        <i class="bi bi-box-arrow-right"></i>

        Logout

    </a>

</li>