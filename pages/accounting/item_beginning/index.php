<?php

/*
|--------------------------------------------------------------------------
| Beginning Inventory
|--------------------------------------------------------------------------
| Accounting - Beginning Inventory
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
requireRole('accounting');

/*
|--------------------------------------------------------------------------
| Load Beginning Inventory
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT
        b.*,
        p.product_code,
        p.product_name,
        p.category,
        l.location_name
    FROM beginning_inventory b
    INNER JOIN products p
        ON p.product_id = b.product_id
    INNER JOIN locations l
        ON l.location_id = b.location_id
    ORDER BY
        b.inventory_date DESC,
        p.product_name ASC
");

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Beginning Inventory";
$breadcrumbs = [
    [
        'title' => 'Beginning Inventory'
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
                    <h5 class="mb-0">Beginning Inventory</h5>
                    <small class="text-muted">Manage beginning inventory records.</small>
                </div>
                <div>
                    <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i>&nbsp;Add Beginning Inventory</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="120">Inventory Date</th>
                                <th width="120">Product Code</th>
                                <th>Product Name</th>
                                <th width="150">Category</th>
                                <th width="180">Location</th>
                                <th width="120" class="text-end">Quantity</th>
                                <th width="140">Expiry Date</th>
                                <th width="170" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($row['inventory_date'])); ?></td>
                                <td><?= htmlspecialchars($row['product_code']); ?></td>
                                <td><?= htmlspecialchars($row['product_name']); ?></td>
                                <td><?= htmlspecialchars($row['category']); ?></td>
                                <td><?= htmlspecialchars($row['location_name']); ?></td>
                                <td class="text-end"><?= number_format($row['quantity'], 2); ?></td>
                                <td><?php if (!empty($row['expiry_date'])) : ?>
                                        <?= date('M d, Y', strtotime($row['expiry_date'])); ?>
                                    <?php else : ?>
                                        <span class="text-muted">
                                            N/A
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="view.php?id=<?= $row['beginning_id']; ?>" class="btn btn-sm btn-info text-white" title="View"><i class="bi bi-eye-fill"></i></a>
                                    <a href="edit.php?id=<?= $row['beginning_id']; ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="delete.php?id=<?= $row['beginning_id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this beginning inventory record?');"><i class="bi bi-trash-fill"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
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