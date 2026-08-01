<?php

/*
|--------------------------------------------------------------------------
| Store Product Inventory
|--------------------------------------------------------------------------
| Today's Product Inventory
|--------------------------------------------------------------------------
*/

require_once '../../../includes/config.php';
require_once '../../../includes/connection.php';
require_once '../../../includes/session.php';
require_once '../../../includes/no_cache.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/flash.php';
require_once '../../../includes/auth.php';

requireLogin();
requireRole('store');

/*
|--------------------------------------------------------------------------
| Logged-in Store
|--------------------------------------------------------------------------
*/

$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT
        u.location_id,
        l.location_name
    FROM users u
    INNER JOIN locations l
        ON l.location_id = u.location_id
    WHERE u.user_id = ?
    LIMIT 1
");
if (!$stmt) {
    die($conn->error);
}
$stmt->bind_param(
    "i",
    $userId
);
$stmt->execute();
$user = $stmt
    ->get_result()
    ->fetch_assoc();
$stmt->close();
$locationId   = (int) $user['location_id'];
$locationName = $user['location_name'];

/*
|--------------------------------------------------------------------------
| Business Date
|--------------------------------------------------------------------------
*/

$businessDate = date('Y-m-d');

/*
|--------------------------------------------------------------------------
| Check Existing Inventory Header
|--------------------------------------------------------------------------
*/

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
if (!$stmt) {
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
| Statistics
|--------------------------------------------------------------------------
*/

$totalProducts = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM products
    WHERE active = 1
");
if ($stmt) {
    $stmt->execute();
    $stmt->bind_result($totalProducts);
    $stmt->fetch();
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Product Inventory";
$breadcrumbs = [
    [
        'title' => 'Product Inventory'
    ]
];

include '../../../includes/layout/header.php';
include '../../../includes/layout/sidebar.php';
include '../../../includes/layout/topbar.php';
include '../../../includes/layout/breadcrumb.php';

?>

<div class="main-content">
    <div class="container-fluid">
        <?php showFlash(); ?>
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="fw-bold mb-1"><?= htmlspecialchars($locationName); ?></h3>
                                <p class="text-muted mb-0">
                                    Product Inventory
                                    <br>
                                    Business Date
                                    <strong>
                                        <?= date(
                                            'F d, Y',
                                            strtotime($businessDate)
                                        ); ?>
                                    </strong>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <h4 class="fw-bold"><?= number_format($totalProducts); ?></h4>
                                <small class="text-muted">Active Products</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Today's Business Day</h5>
                            <small class="text-muted">Encode today's product inventory.</small>
                        </div>
                        <div>
                            <a href="history.php" class="btn btn-outline-primary"><i class="bi bi-clock-history"></i>&nbsp;Inventory History</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($inventoryHeader): ?>
                            <?php
                                switch ($inventoryHeader['business_status']) {
                                    case 'draft':
                                        $badge = 'secondary';
                                        break;
                                    case 'submitted':
                                        $badge = 'warning';
                                        break;
                                    case 'verified':
                                        $badge = 'info';
                                        break;
                                    case 'generated':
                                        $badge = 'primary';
                                        break;
                                    case 'locked':
                                        $badge = 'dark';
                                        break;
                                    default:
                                        $badge = 'success';
                                        break;
                                }
                            ?>
                            <div class="alert alert-success mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">Inventory already created.</h5>
                                        <small>Continue encoding today's inventory.</small>
                                    </div>
                                    <span class="badge bg-<?= $badge; ?> fs-6"><?= ucfirst($inventoryHeader['business_status']); ?></span>
                                </div>
                            </div>
                            <a href="view.php?id=<?= $inventoryHeader['inventory_header_id']; ?>" class="btn btn-success btn-lg"><i class="bi bi-pencil-square"></i>&nbsp;Continue Encoding</a>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h5 class="mb-2">No inventory for today.</h5>
                                <p class="mb-0">Create today's inventory before encoding.</p>
                            </div>
                            <form action="create.php" method="POST">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-plus-circle-fill"></i>&nbsp;Create Today's Inventory</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Inventory Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="45%">Store</th>
                                <td><?= htmlspecialchars($locationName); ?></td>
                            </tr>
                            <tr>
                                <th>Business Date</th>
                                <td>
                                    <?= date(
                                        'F d, Y',
                                        strtotime($businessDate)
                                    ); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Products</th>
                                <td><?= number_format($totalProducts); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php if ($inventoryHeader): ?>
                                        <span class="badge bg-success"><?= ucfirst($inventoryHeader['business_status']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-white">Not Created</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Inventory Process</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <i class="bi bi-truck fs-1 text-primary"></i>&nbsp;<p class="mt-2 mb-0">Delivery</p>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <i class="bi bi-arrow-right fs-4 text-secondary"></i>
                            </div>
                            <div class="col-md-2">
                                <i class="bi bi-check-circle fs-1 text-success"></i>&nbsp;<p class="mt-2 mb-0">Validation</p>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <i class="bi bi-arrow-right fs-4 text-secondary"></i>
                            </div>
                            <div class="col-md-2">
                                <i class="bi bi-box-seam fs-1 text-info"></i>&nbsp;<p class="mt-2 mb-0">Inventory</p>
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <i class="bi bi-arrow-right fs-4 text-secondary"></i>
                            </div>
                            <div class="col-md-2">
                                <i class="bi bi-send-check fs-1 text-warning"></i>&nbsp;<p class="mt-2 mb-0">Submit</p>
                            </div>
                        </div>
                        <hr>
                        <div class="alert alert-info mb-0">
                            <strong>Reminder:</strong>
                            The beginning inventory is automatically computed
                            from the validated deliveries.
                            You only need to encode the actual physical ending
                            inventory of each product.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="../delivery_validation/index.php" class="btn btn-outline-primary"><i class="bi bi-truck"></i>&nbsp;Delivery Validation</a>
                    <a href="../throw_away/index.php" class="btn btn-outline-danger"><i class="bi bi-trash"></i>&nbsp;Product Throw Away</a>
                    <a href="history.php" class="btn btn-outline-secondary"><i class="bi bi-clock-history"></i>&nbsp;Inventory History</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>