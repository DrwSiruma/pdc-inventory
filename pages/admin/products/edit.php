<?php

/*
|--------------------------------------------------------------------------
| Edit Product
|--------------------------------------------------------------------------
| Administrator - Edit Product
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
requireRole('super_admin');

/*
|--------------------------------------------------------------------------
| Validate Product ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlash(
        'danger',
        'Invalid product selected.'
    );
    header('Location: index.php');
    exit;
}

$product_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(" SELECT * FROM products WHERE product_id = ? LIMIT 1");
$stmt->bind_param(
    "i",
    $product_id
);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    $stmt->close();
    setFlash(
        'danger',
        'Product not found.'
    );
    header('Location: index.php');
    exit;
}
$product = $result->fetch_assoc();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Edit Product";
$breadcrumbs = [
    ['title' => 'Product Management','link'  => 'index.php'],['title' => 'Edit Product']
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
            <form method="POST" action="update.php">
                <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Product</h5>
                    <small class="text-muted">Update product information.</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Product Code<span class="text-danger">*</span></label>
                            <input type="text" name="product_code" class="form-control" maxlength="30" value="<?= htmlspecialchars($product['product_code']); ?>" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Product Name<span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" maxlength="150" value="<?= htmlspecialchars($product['product_name']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category<span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="Donuts" <?= ($product['category'] == 'Donuts') ? 'selected' : ''; ?>>Donuts</option>
                                <option value="Munchkins" <?= ($product['category'] == 'Munchkins') ? 'selected' : ''; ?>>Munchkins</option>
                                <option value="Coffee" <?= ($product['category'] == 'Coffee') ? 'selected' : ''; ?>>Coffee</option>
                                <option value="Hot Beverages" <?= ($product['category'] == 'Hot Beverages') ? 'selected' : ''; ?>>Hot Beverages</option>
                                <option value="Cold Beverages" <?= ($product['category'] == 'Cold Beverages') ? 'selected' : ''; ?>>Cold Beverages</option>
                                <option value="Bunwich" <?= ($product['category'] == 'Bunwich') ? 'selected' : ''; ?>>Bunwich</option>
                                <option value="Baked Goods" <?= ($product['category'] == 'Baked Goods') ? 'selected' : ''; ?>>Baked Goods</option>
                                <option value="Raw Materials" <?= ($product['category'] == 'Raw Materials') ? 'selected' : ''; ?>>Raw Materials</option>
                                <option value="Packaging" <?= ($product['category'] == 'Packaging') ? 'selected' : ''; ?>>Packaging</option>
                                <option value="Office Supplies" <?= ($product['category'] == 'Office Supplies') ? 'selected' : ''; ?>>Office Supplies</option>
                                <option value="Cleaning Supplies" <?= ($product['category'] == 'Cleaning Supplies') ? 'selected' : ''; ?>>Cleaning Supplies</option>
                                <option value="Supplies" <?= ($product['category'] == 'Supplies') ? 'selected' : ''; ?>>Supplies</option>
                                <option value="Others" <?= ($product['category'] == 'Others') ? 'selected' : ''; ?>>Others</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Expiry Required</label>
                            <select name="expiry_required" class="form-select">
                                <option value="1" <?= ($product['expiry_required'] == 1) ? 'selected' : ''; ?>>Yes</option>
                                <option value="0" <?= ($product['expiry_required'] == 0) ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($product['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?= ($product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i>&nbsp;Update Product</button>
                    <button type="reset" class="btn btn-warning"><i class="bi bi-arrow-clockwise"></i>&nbsp;Reset</button>
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle-fill"></i>&nbsp;Back to List</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

include '../../../includes/layout/footer.php';

?>