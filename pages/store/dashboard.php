<?php

/*
|--------------------------------------------------------------------------
| Store Dashboard
|--------------------------------------------------------------------------
| Product Variance Computation System
|--------------------------------------------------------------------------
*/

require_once '../../includes/config.php';
require_once '../../includes/connection.php';
require_once '../../includes/session.php';
require_once '../../includes/no_cache.php';
require_once '../../includes/functions.php';
require_once '../../includes/flash.php';
require_once '../../includes/auth.php';

requireLogin();
requireRole('store');

/*
|--------------------------------------------------------------------------
| Logged-in User
|--------------------------------------------------------------------------
*/

$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT
        u.user_id,
        u.full_name,
        u.location_id,
        l.location_name
    FROM users u
    INNER JOIN locations l
        ON l.location_id = u.location_id
    WHERE u.user_id = ?
    LIMIT 1
");
if(!$stmt){
    die($conn->error);
}
$stmt->bind_param("i",$userId);
$stmt->execute();
$users = $stmt
    ->get_result()
    ->fetch_assoc();
$stmt->close();
$locationId   = (int)$users['location_id'];
$locationName = $users['location_name'];

/*
|--------------------------------------------------------------------------
| Current Business Date
|--------------------------------------------------------------------------
*/

$businessDate = date('Y-m-d');

/*
|--------------------------------------------------------------------------
| Today's Product Inventory Header
|--------------------------------------------------------------------------
*/

$inventoryHeader = null;
$stmt = $conn->prepare("
    SELECT
        inventory_header_id,
        business_status,
        submitted_at,
        verified_at,
        generated_at
    FROM product_inventory_header
    WHERE
        location_id = ?
    AND business_date = ?
    LIMIT 1
");
if(!$stmt){
    die($conn->error);
}
$stmt->bind_param(
    "is",
    $locationId,
    $businessDate
);
$stmt->execute();
$inventoryHeader = $stmt
    ->get_result()
    ->fetch_assoc();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Business Status
|--------------------------------------------------------------------------
*/

if($inventoryHeader){
    $businessStatus = $inventoryHeader['business_status'];
    $inventoryHeaderId = (int)$inventoryHeader['inventory_header_id'];
}else{
    $businessStatus = 'draft';
    $inventoryHeaderId = 0;
}

/*
|--------------------------------------------------------------------------
| Total Inventory Items
|--------------------------------------------------------------------------
*/

$totalInventoryItems = 0;
if($inventoryHeaderId > 0){
    $stmt = $conn->prepare("
        SELECT COUNT(*) total
        FROM product_inventory_details
        WHERE inventory_header_id = ?
    ");
    if(!$stmt){
        die($conn->error);
    }
    $stmt->bind_param(
        "i",
        $inventoryHeaderId
    );
    $stmt->execute();
    $totalInventoryItems = $stmt
        ->get_result()
        ->fetch_assoc()['total'];
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Total Throw Away Entries
|--------------------------------------------------------------------------
*/

$totalThrowAway = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) total
    FROM product_throwaway
    WHERE
        location_id = ?
    AND business_date = ?
");
if($stmt){
    $stmt->bind_param(
        "is",
        $locationId,
        $businessDate
    );
    $stmt->execute();
    $totalThrowAway = $stmt
        ->get_result()
        ->fetch_assoc()['total'];
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Total Deliveries
|--------------------------------------------------------------------------
|
| Uses product_delivery_logs
|
*/
$totalDeliveries = 0;
if($inventoryHeaderId > 0){
    $stmt = $conn->prepare("
        SELECT COUNT(*) total
        FROM product_delivery_logs
        WHERE inventory_header_id = ?
    ");
    if($stmt){
        $stmt->bind_param(
            "i",
            $inventoryHeaderId
        );
        $stmt->execute();
        $totalDeliveries = $stmt
            ->get_result()
            ->fetch_assoc()['total'];
        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Store Dashboard";
$breadcrumbs = [
    [
        'title' => 'Dashboard'
    ]
];

include '../../includes/layout/header.php';
include '../../includes/layout/sidebar.php';
include '../../includes/layout/topbar.php';
include '../../includes/layout/breadcrumb.php';
?>
<div class="main-content">
    <div class="container-fluid">
        <?php showFlash(); ?>
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="fw-bold mb-1"><?= htmlspecialchars($locationName); ?></h3>
                                <p class="text-muted mb-0">
                                    Business Date :
                                    <strong><?= date('F d, Y', strtotime($businessDate)); ?></strong>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <?php
                                    switch($businessStatus){
                                        case 'submitted':
                                            $badge='primary';
                                        break;
                                        case 'verified':
                                            $badge='success';
                                        break;
                                        case 'generated':
                                            $badge='info';
                                        break;
                                        case 'locked':
                                            $badge='dark';
                                        break;
                                        default:
                                            $badge='warning';
                                        break;
                                    }
                                ?>
                                <span class="badge bg-<?= $badge ?> fs-6 px-3 py-2"><?= ucfirst($businessStatus); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Deliveries -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Deliveries</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= number_format($totalDeliveries); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-truck fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Product Inventory -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Inventory Items</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= number_format($totalInventoryItems); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-box-seam fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Throw Away -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Throw Away</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= number_format($totalThrowAway); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-trash-fill fs-1 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Business Status -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">Business Status</div>
                                <div class="h5 mb-0 fw-bold text-dark"><?= ucfirst($businessStatus); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clipboard-check fs-1 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="product_inventory/index.php" class="btn btn-success w-100 py-3">
                                    <i class="bi bi-basket-fill d-block fs-2 mb-2"></i>&nbsp;Product Inventory
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="throwaway/index.php" class="btn btn-danger w-100 py-3">
                                    <i class="bi bi-trash-fill d-block fs-2 mb-2"></i>&nbsp;Product Throw Away
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="business_day/index.php" class="btn btn-primary w-100 py-3">
                                    <i class="bi bi-calendar-check d-block fs-2 mb-2"></i>&nbsp;Business Day
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="history/index.php" class="btn btn-dark w-100 py-3">
                                    <i class="bi bi-clock-history d-block fs-2 mb-2"></i>&nbsp;History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Business Day Progress -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Business Day Progress</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="70%">Process</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Product Inventory</td>
                                    <td>
                                        <?php if($inventoryHeaderId > 0): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Product Throw Away</td>
                                    <td>
                                        <?php if($totalThrowAway > 0): ?>
                                            <span class="badge bg-success">Recorded</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Entry</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Business Day Status</td>
                                    <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($businessStatus); ?></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Recent Information -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Store Reminder</h5>
                    </div>
                    <div class="card-body">
                        <?php if($businessStatus == 'draft'): ?>
                            <div class="alert alert-warning mb-0">
                                <strong>Today's Business Day is still open.</strong>
                                <hr>
                                Complete all Product Inventory entries before submitting to Accounting.
                            </div>
                        <?php elseif($businessStatus == 'submitted'): ?>
                            <div class="alert alert-primary mb-0">
                                Your Business Day has already been submitted and is waiting for Accounting verification.
                            </div>
                        <?php elseif($businessStatus == 'verified'): ?>
                            <div class="alert alert-success mb-0">
                                Accounting has verified today's Business Day.
                            </div>
                        <?php elseif($businessStatus == 'generated'): ?>
                            <div class="alert alert-info mb-0">
                                Product Variance has already been generated by Accounting.
                            </div>
                        <?php elseif($businessStatus == 'locked'): ?>
                            <div class="alert alert-dark mb-0">
                                This Business Day has already been locked.
                                No further modifications are allowed.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Store Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <th width="40%">Store</th>
                                <td><?= htmlspecialchars($locationName); ?></td>
                            </tr>
                            <tr>
                                <th>User</th>
                                <td><?= htmlspecialchars($users['full_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Business Date</th>
                                <td><?= date('F d, Y', strtotime($businessDate)); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($businessStatus); ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    include '../../includes/layout/footer.php';
?>