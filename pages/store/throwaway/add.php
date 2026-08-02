<?php

/*
|--------------------------------------------------------------------------
| Add Product Throw Away
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

/*
|--------------------------------------------------------------------------
| Active Inventory Header
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
        'danger',
        'No active Product Inventory found.'
    );
    header(
        'Location:../product_inventory/index.php'
    );
    exit;
}
if ($inventory['business_status'] != 'draft') {
    setFlash(
        'warning',
        'Business day is already submitted.'
    );
    header(
        'Location:index.php'
    );
    exit;
}
$inventoryHeaderId = (int) $inventory['inventory_header_id'];

/*
|--------------------------------------------------------------------------
| Load Products
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        product_id,
        product_code,
        product_name
    FROM products
    ORDER BY
        product_name ASC
");
if (!$stmt) {
    die($conn->error);
}
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = "Add Throw Away";
$breadcrumbs = [
    [
        'title' => 'Product Throw Away',
        'link'  => 'index.php'
    ],
    [
        'title' => 'Add Throw Away'
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
        <form action="save.php" method="POST">
            <input type="hidden" name="inventory_header_id" value="<?= $inventoryHeaderId; ?>">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Add Product Throw Away</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product<span class="text-danger">*</span></label>
                            <select name="product_id" class="form-control" required>
                                <option value="">-- Select Product --</option>
                                <?php while ($product = $products->fetch_assoc()) : ?>
                                    <option value="<?= $product['product_id']; ?>"><?= htmlspecialchars($product['product_code']); ?>-<?= htmlspecialchars($product['product_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Throw Away Quantity<span class="text-danger">*</span></label>
                            <input type="number" name="store_qty" class="form-control text-end" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" maxlength="255" placeholder="Reason for throw away..."></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i>&nbsp;Back</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i>&nbsp;Save Throw Away</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>