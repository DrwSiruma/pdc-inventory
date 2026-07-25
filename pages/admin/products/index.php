<?php

/*
|--------------------------------------------------------------------------
| Product Management
|--------------------------------------------------------------------------
| Administrator - Products List
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

$pageTitle = "Product Management";
$breadcrumbs = [
    [
        'title' => 'Product Management'
    ]
];

include '../../../includes/layout/header.php';
include '../../../includes/layout/sidebar.php';
include '../../../includes/layout/topbar.php';
include '../../../includes/layout/breadcrumb.php';

/*
|--------------------------------------------------------------------------
| Load Products
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM products ORDER BY product_name ASC";
$result = mysqli_query($conn, $sql);

?>

<div class="main-content">
    <div class="container-fluid">
        <?php showFlash(); ?>
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Product Management</h5>
                    <small class="text-muted">Manage all products</small>
                </div>
                <a href="add.php" class="btn btn-primary"><i class="bi bi-box-seam"></i>&nbsp;Add Product</a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="productsTable" class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="60">#</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th width="120">Expiry</th>
                                <th width="120">Status</th>
                                <th width="170">Created</th>
                                <th width="220">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                if (mysqli_num_rows($result) > 0):
                                    $i = 1;
                                    while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($row['product_code']); ?></td>
                                <td><?= htmlspecialchars($row['product_name']); ?></td>
                                <td><?= htmlspecialchars($row['category']); ?></td>
                                <td class="text-center">
                                    <?php if ($row['expiry_required'] == 1): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td class="text-center">
                                    <a href="view.php?id=<?= $row['product_id']; ?>" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye-fill"></i></a>
                                    <a href="edit.php?id=<?= $row['product_id']; ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                    <?php if ($row['status'] == 'active'): ?>
                                        <a href="delete.php?id=<?= $row['product_id']; ?>" class="btn btn-sm btn-danger" title="Deactivate Product" onclick="return confirm('Deactivate this product?');"><i class="bi bi-box-arrow-down"></i></a>
                                    <?php else: ?>
                                        <a href="delete.php?id=<?= $row['product_id']; ?>" class="btn btn-sm btn-success" title="Activate Product" onclick="return confirm('Activate this product?');"><i class="bi bi-box-arrow-in-up"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                                    endwhile;
                                else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No products found.</td>
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