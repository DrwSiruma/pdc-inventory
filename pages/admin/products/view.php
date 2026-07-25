<?php

/*
|--------------------------------------------------------------------------
| View Product
|--------------------------------------------------------------------------
| Administrator - View Product Information
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

$pageTitle = "View Product";
$breadcrumbs = [
    [
        'title' => 'Product Management',
        'link'  => 'index.php'
    ],
    [
        'title' => 'View Product'
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
                    <h5 class="mb-0">Product Information</h5>
                    <small class="text-muted">View product details</small>
                </div>
                <div>
                    <a href="edit.php?id=<?= $product['product_id']; ?>" class="btn btn-warning"><i class="bi bi-pencil-fill"></i>&nbsp;Edit</a>
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Back</a>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="220">Product Code</th>
                        <td><?= htmlspecialchars($product['product_code']); ?></td>
                    </tr>
                    <tr>
                        <th>Product Name</th>
                        <td><?= htmlspecialchars($product['product_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td><?= htmlspecialchars($product['category']); ?></td>
                    </tr>
                    <tr>
                        <th>Expiry Required</th>
                        <td>
                            <?php if ($product['expiry_required'] == 1): ?>
                                <span class="badge bg-success">Yes</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php if ($product['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Date Created</th>
                        <td><?= date('F d, Y h:i A', strtotime($product['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>