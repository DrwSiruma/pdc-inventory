<?php

/*
|--------------------------------------------------------------------------
| Accounting Dashboard
|--------------------------------------------------------------------------
| Accounting Department Dashboard
|--------------------------------------------------------------------------
*/

require_once '../../includes/config.php';
require_once '../../includes/connection.php';
require_once '../../includes/session.php';
require_once '../../includes/no_cache.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireLogin();
requireRole('accounting');

$pageTitle = "Accounting Dashboard";
$breadcrumbs = [
    [
        'title' => 'Dashboard'
    ]

];

include '../../includes/layout/header.php';
include '../../includes/layout/sidebar.php';
include '../../includes/layout/topbar.php';
include '../../includes/layout/breadcrumb.php';

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$beginning_inventory = $conn->query("
    SELECT COUNT(*)
    FROM beginning_inventory
")->fetch_row()[0];

$pdr_count = 0;
$scir_count = 0;
$parl_count = 0;
$variance_count = 0;
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Beginning Inventory</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= number_format($beginning_inventory); ?></div>
                            </div>
                            <div class="col-auto"><i class="bi bi-box-seam fs-1 text-primary"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">PDR Encoded</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= number_format($pdr_count); ?></div>
                            </div>
                            <div class="col-auto"><i class="bi bi-journal-plus fs-1 text-success"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">SCIR Encoded</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= number_format($scir_count); ?></div>
                            </div>
                            <div class="col-auto"><i class="bi bi-clipboard-data fs-1 text-info"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">PARL Encoded</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= number_format($parl_count); ?></div>
                            </div>
                            <div class="col-auto"><i class="bi bi-card-checklist fs-1 text-warning"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Pending Variance</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= number_format($variance_count); ?></div>
                            </div>
                            <div class="col-auto"><i class="bi bi-calculator fs-1 text-danger"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Recent Activity</h5></div>
                    <div class="card-body">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-clock-history fs-1 d-block mb-3"></i>No recent accounting activity available.
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Quick Access</h5></div>
                    <div class="card-body d-grid gap-2">
                        <a href="beginning_inventory/index.php" class="btn btn-primary"><i class="bi bi-box-seam-fill"></i>&nbsp;Beginning Inventory</a>
                        <a href="pdr/index.php" class="btn btn-success"><i class="bi bi-journal-plus"></i>&nbsp;PDR Encoding</a>
                        <a href="scir/index.php" class="btn btn-info text-white"><i class="bi bi-clipboard-data"></i>&nbsp;SCIR Encoding</a>
                        <a href="parl/index.php" class="btn btn-warning"><i class="bi bi-card-checklist"></i>&nbsp;PARL Encoding</a>
                        <a href="variance/index.php" class="btn btn-danger"><i class="bi bi-calculator"></i>&nbsp;Automatic Variance</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    include '../../includes/layout/footer.php';
?>