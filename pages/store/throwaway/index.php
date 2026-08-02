<?php

/*
|--------------------------------------------------------------------------
| Product Throw Away
|--------------------------------------------------------------------------
| Store Module
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
| Logged-in User
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
    WHERE
        u.user_id = ?
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

$locationId = (int) $user['location_id'];
$locationName = $user['location_name'];

/*
|--------------------------------------------------------------------------
| Get Active Inventory Header
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        inventory_header_id,
        business_date,
        business_status
    FROM product_inventory_header
    WHERE
        location_id = ?
    ORDER BY
        business_date DESC
    LIMIT 1
");

if (!$stmt) {
    die($conn->error);
}

$stmt->bind_param(
    "i",
    $locationId
);

$stmt->execute();

$inventory = $stmt
    ->get_result()
    ->fetch_assoc();

$stmt->close();

if (!$inventory) {
    setFlash(
        'warning',
        'Please create your Product Inventory first before encoding Throw Away.'
    );
    header(
        'Location:../product_inventory/index.php'
    );
    exit;
}
$inventoryHeaderId = (int) $inventory['inventory_header_id'];

/*
|--------------------------------------------------------------------------
| Load Throw Away Records
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        t.throwaway_id,
        t.product_id,
        p.product_code,
        p.product_name,
        t.store_qty,
        t.accounting_qty,
        t.status,
        t.remarks
    FROM product_throwaway t
    INNER JOIN products p
        ON p.product_id = t.product_id
    WHERE
        t.inventory_header_id = ?
    ORDER BY
        p.product_name ASC
");

if (!$stmt) {
    die($conn->error);
}

$stmt->bind_param(
    "i",
    $inventoryHeaderId
);
$stmt->execute();
$throwaways = $stmt->get_result();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = "Product Throw Away";
$breadcrumbs = [
    [
        'title' => 'Product Throw Away'
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
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Product Throw Away</h5>
                    <small class="text-muted"><?= htmlspecialchars($locationName); ?></small>
                </div>
                <div>
                    <a href="history.php" class="btn btn-outline-secondary"><i class="bi bi-clock-history"></i>&nbsp;History</a>
                    <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i>&nbsp;Add Throw Away</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="throwAwayTable">
                        <thead>
                            <tr>
                                <th width="60">#</th>
                                <th>Product</th>
                                <th class="text-end">Store Qty</th>
                                <th class="text-end">Accounting Qty</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $no = 1;
                                while ($row = $throwaways->fetch_assoc()) :
                                    switch ($row['status']) {
                                        case 'pending':
                                            $badge = 'warning';
                                            break;
                                        case 'verified':
                                            $badge = 'success';
                                            break;
                                        case 'rejected':
                                            $badge = 'danger';
                                            break;
                                        default:
                                            $badge = 'secondary';
                                            break;
                                    }
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['product_name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($row['product_code']); ?></small>
                                </td>
                                <td class="text-end">
                                    <?= number_format(
                                        $row['store_qty'],
                                        2
                                    ); ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($row['accounting_qty'] !== null) : ?>
                                        <?= number_format(
                                            $row['accounting_qty'],
                                            2
                                        ); ?>
                                    <?php else : ?>
                                        <span class="text-muted">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-<?= $badge; ?>"><?= ucfirst($row['status']); ?></span></td>
                                <td><?= htmlspecialchars($row['remarks']); ?></td>
                                <td class="text-center">
                                    <?php if ($inventory['business_status'] == 'draft') : ?>
                                        <a href="edit.php?id=<?= $row['throwaway_id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i>&nbsp;Edit</a>
                                        <a href="delete.php?id=<?= $row['throwaway_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this throw away record?');"><i class="bi bi-trash-fill"></i>&nbsp;Delete</a>
                                    <?php else : ?>
                                        <button class="btn btn-sm btn-secondary" disabled>Locked</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($no == 1) : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No throw away records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>