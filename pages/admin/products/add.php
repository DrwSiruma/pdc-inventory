<?php

/*
|--------------------------------------------------------------------------
| Add Product
|--------------------------------------------------------------------------
| Administrator - Add New Product
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
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Add Product";
$breadcrumbs = [
    [
        'title' => 'Product Management',
        'link'  => 'index.php'
    ],
    [
        'title' => 'Add Product'
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
                    <h5 class="mb-0">Add Product</h5>
                    <small class="text-muted">Enter the product information below.</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Product Code<span class="text-danger">*</span></label>
                            <input type="text" name="product_code" class="form-control" maxlength="30" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Product Name<span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" maxlength="150" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category<span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <option value="Donuts">Donuts</option>
                                <option value="Munchkins">Munchkins</option>
                                <option value="Coffee">Coffee</option>
                                <option value="Hot Beverages">Hot Beverages</option>
                                <option value="Cold Beverages">Cold Beverages</option>
                                <option value="Bunwich">Bunwich</option>
                                <option value="Baked Goods">Baked Goods</option>
                                <option value="Raw Materials">Raw Materials</option>
                                <option value="Packaging">Packaging</option>
                                <option value="Office Supplies">Office Supplies</option>
                                <option value="Cleaning Supplies">Cleaning Supplies</option>
                                <option value="Supplies">Supplies</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Expiry Required<span class="text-danger">*</span></label>
                            <select name="expiry_required" class="form-select" required>
                                <option value="1" selected>Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i>&nbsp;Save Product</button>
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