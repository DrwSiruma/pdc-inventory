<?php

/*
|--------------------------------------------------------------------------
| Submit Business Day
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
$locationId   = (int) $user['location_id'];
$locationName = $user['location_name'];

/*
|--------------------------------------------------------------------------
| Load Current Business Day
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
        'No Product Inventory found.'
    );
    header(
        'Location:../product_inventory/index.php'
    );
    exit;
}
$inventoryHeaderId = (int) $inventory['inventory_header_id'];

/*
|--------------------------------------------------------------------------
| Delivery Validation Count
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM product_delivery_header
    WHERE
        location_id = ?
    AND
        business_date = ?
");
$stmt->bind_param(
    "is",
    $locationId,
    $inventory['business_date']
);
$stmt->execute();
$deliveryCount = $stmt
    ->get_result()
    ->fetch_assoc()['total'];
$stmt->close();

/*
|--------------------------------------------------------------------------
| Throw Away Count
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM product_throwaway
    WHERE
        inventory_header_id = ?
");
$stmt->bind_param(
    "i",
    $inventoryHeaderId
);
$stmt->execute();
$throwAwayCount = $stmt
    ->get_result()
    ->fetch_assoc()['total'];
$stmt->close();

/*
|--------------------------------------------------------------------------
| Inventory Detail Count
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM product_inventory_details
    WHERE
        inventory_header_id = ?
");
$stmt->bind_param(
    "i",
    $inventoryHeaderId
);
$stmt->execute();
$inventoryCount = $stmt
    ->get_result()
    ->fetch_assoc()['total'];
$stmt->close();

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = 'Submit Business Day';
$breadcrumbs = [
    [
        'title' => 'Submit Business Day'
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
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    Submit Business Day
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Business Date</label>
                        <input type="text" class="form-control" value="<?= date('F d, Y', strtotime($inventory['business_date'])); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Store</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($locationName); ?>" readonly>
                    </div>
                </div>
                <hr>
                <h6 class="mb-3">Business Day Checklist</h6>
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="70%">Module</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Delivery Validation</td>
                            <td class="text-center">
                                <?php if ($deliveryCount > 0) : ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php else : ?>
                                    <span class="badge bg-danger">No Record</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Product Throw Away</td>
                            <td class="text-center">
                                <?php if ($throwAwayCount > 0) : ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php else : ?>
                                    <span class="badge bg-warning">No Entry</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Product Inventory</td>
                            <td class="text-center">
                                <?php if ($inventoryCount > 0) : ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php else : ?>
                                    <span class="badge bg-danger">No Inventory</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Business Day Status</td>
                            <td class="text-center">
                                <?php
                                    switch ($inventory['business_status']) {
                                        case 'draft':
                                            $statusClass = 'secondary';
                                            break;
                                        case 'submitted':
                                            $statusClass = 'primary';
                                            break;
                                        case 'verified':
                                            $statusClass = 'success';
                                            break;
                                        default:
                                            $statusClass = 'dark';
                                            break;
                                    }
                                ?>
                                <span class="badge bg-<?= $statusClass; ?>"><?= ucfirst($inventory['business_status']); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <div class="alert alert-warning">
                    <strong>Reminder:</strong>
                    Once you submit this Business Day, you will no longer be able to modify
                    Delivery Validation, Product Throw Away, or Product Inventory. The records
                    will be forwarded to the Accounting Department for verification.
                </div>
                <form action="submit.php" method="POST" onsubmit="return confirm('Are you sure you want to submit this Business Day to Accounting? After submission, you can no longer modify the records.');">
                    <input type="hidden" name="inventory_header_id" value="<?= $inventoryHeaderId; ?>">
                    <div class="d-flex justify-content-between mt-4">
                        <a href="../dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i>&nbsp;Back</a>
                        <?php if ($inventory['business_status'] == 'draft') : ?>
                            <?php if ($deliveryCount > 0 && $inventoryCount > 0) : ?>
                                <button type="submit" class="btn btn-success"><i class="bi bi-send-check-fill"></i>&nbsp;Submit Business Day</button>
                            <?php else : ?>
                                <button type="button" class="btn btn-success" disabled>Complete All Required Modules First</button>
                            <?php endif; ?>
                        <?php else : ?>
                            <button type="button" class="btn btn-secondary" disabled>Business Day Already Submitted</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../includes/layout/footer.php'; ?>