<?php

/*
|--------------------------------------------------------------------------
| View Beginning Inventory
|--------------------------------------------------------------------------
| Accounting - View Beginning Inventory
|--------------------------------------------------------------------------
*/

require_once '../../../includes/config.php';
require_once '../../../includes/connection.php';
require_once '../../../includes/session.php';
require_once '../../../includes/no_cache.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/flash.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/store_permission.php';

requireLogin();
requireRole(['accounting', 'super_admin']);

/*
|--------------------------------------------------------------------------
| Validate Beginning Inventory ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlash(
        'danger',
        'Invalid beginning inventory selected.'
    );
    header('Location: index.php');
    exit;
}
$beginning_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Beginning Inventory
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        b.*,
        p.product_code,
        p.product_name,
        p.category,
        p.expiry_required,
        l.location_name,
        u.full_name AS encoded_by_name
    FROM beginning_inventory b
    INNER JOIN products p
        ON p.product_id = b.product_id
    INNER JOIN locations l
        ON l.location_id = b.location_id
    LEFT JOIN users u
        ON u.user_id = b.encoded_by
    WHERE b.beginning_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $beginning_id
);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    setFlash(
        'danger',
        'Beginning inventory record not found.'
    );
    header('Location: index.php');
    exit;
}

$inventory = $result->fetch_assoc();
$stmt->close();
enforceStorePermission($conn, (int) $inventory['location_id']);

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "View Beginning Inventory";
$breadcrumbs = [
    [
        'title' => 'Beginning Inventory',
        'link'  => 'index.php'
    ],
    [
        'title' => 'View Beginning Inventory'
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
                    <h5 class="mb-0">Beginning Inventory Information</h5>
                    <small class="text-muted">View beginning inventory details.</small>
                </div>
                <div>
                    <a href="edit.php?id=<?= $inventory['beginning_id']; ?>" class="btn btn-warning"><i class="bi bi-pencil-fill"></i>&nbsp;Edit</a>
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Back</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="220">Inventory Date</th>
                        <td><?= date('F d, Y', strtotime($inventory['inventory_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>Product Code</th>
                        <td><?= htmlspecialchars($inventory['product_code']); ?></td>
                    </tr>
                    <tr>
                        <th>Product Name</th>
                        <td><?= htmlspecialchars($inventory['product_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td><?= htmlspecialchars($inventory['category']); ?></td>
                    </tr>
                    <tr>
                        <th>Store / Location</th>
                        <td><?= htmlspecialchars($inventory['location_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Beginning Quantity</th>
                        <td><?= number_format($inventory['quantity'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Expiry Date</th>
                        <td>
                            <?php if (!empty($inventory['expiry_date'])) : ?>
                                <?= date('F d, Y', strtotime($inventory['expiry_date'])); ?>
                            <?php else : ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Remarks</th>
                        <td>
                            <?= !empty($inventory['remarks'])
                                ? nl2br(htmlspecialchars($inventory['remarks']))
                                : '<span class="text-muted">None</span>'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Encoded By</th>
                        <td><?= htmlspecialchars($inventory['encoded_by_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Date Created</th>
                        <td><?= date('F d, Y h:i A', strtotime($inventory['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
    include '../../../includes/layout/footer.php';
?>
