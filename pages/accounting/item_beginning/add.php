<?php

/*
|--------------------------------------------------------------------------
| Add Beginning Inventory
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
require_once '../../../includes/store_permission.php';

requireLogin();
requireRole(['accounting', 'super_admin']);

$assignedStores = getAccessibleStoreIds($conn);

/*
|--------------------------------------------------------------------------
| Load Products
|--------------------------------------------------------------------------
*/

$products = $conn->query("
    SELECT
        product_id,
        product_code,
        product_name,
        category,
        expiry_required
    FROM products
    WHERE status = 'active'
    ORDER BY product_name ASC
");

/*
|--------------------------------------------------------------------------
| Load Locations
|--------------------------------------------------------------------------
*/

$locations = $conn->query("
    SELECT
        location_id,
        location_name
    FROM locations
    WHERE status = 'active'
    " . buildStoreWhereClause($assignedStores) . "
    ORDER BY location_name ASC
");

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Add Beginning Inventory";
$breadcrumbs = [
    [
        'title' => 'Beginning Inventory',
        'link'  => 'index.php'
    ],
    [
        'title' => 'Add Beginning Inventory'
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
            <form method="POST" action="save.php">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Add Beginning Inventory</h5>
                    <small class="text-muted">Encode beginning inventory for a store.</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Inventory Date<span class="text-danger">*</span></label>
                            <input type="date" name="inventory_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Product<span class="text-danger">*</span></label>
                            <select name="product_id" id="product_id" class="form-control" required>
                                <option value="">-- Select Product --</option>
                                <?php while ($product = $products->fetch_assoc()) : ?>
                                <option value="<?= $product['product_id']; ?>" data-expiry="<?= $product['expiry_required']; ?>">
                                    <?= htmlspecialchars($product['product_code']); ?> - <?= htmlspecialchars($product['product_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Store / Location<span class="text-danger">*</span></label>
                            <select name="location_id" class="form-control" required>
                                <option value="">-- Select Location --</option>
                                <?php while ($location = $locations->fetch_assoc()) : ?>
                                <option value="<?= $location['location_id']; ?>">
                                    <?= htmlspecialchars($location['location_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Beginning Quantity<span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control text-end" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-3 mb-3" id="expiry_container">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="1"></textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i>&nbsp;Save Beginning Inventory</button>
                        <button type="reset" class="btn btn-warning"><i class="bi bi-arrow-clockwise"></i>&nbsp;Reset</button>
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle-fill"></i>&nbsp;Back to List</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
    include '../../../includes/layout/footer.php';
?>
