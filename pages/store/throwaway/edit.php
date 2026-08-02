<?php

/*
|--------------------------------------------------------------------------
| Edit Product Throw Away
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
| Validate Request
|--------------------------------------------------------------------------
*/

$throwawayId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);
if (!$throwawayId) {
    setFlash(
        'danger',
        'Invalid throw away record.'
    );
    header('Location:index.php');
    exit;
}

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
| Load Throw Away Record
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        t.*,
        p.product_code,
        p.product_name,
        h.business_status
    FROM product_throwaway t
    INNER JOIN products p
        ON p.product_id = t.product_id
    INNER JOIN product_inventory_header h
        ON h.inventory_header_id = t.inventory_header_id
    WHERE
        t.throwaway_id = ?
    LIMIT 1
");
if (!$stmt) {
    die($conn->error);
}
$stmt->bind_param(
    "i",
    $throwawayId
);
$stmt->execute();
$throwaway = $stmt
    ->get_result()
    ->fetch_assoc();
$stmt->close();
if (!$throwaway) {
    setFlash(
        'danger',
        'Throw away record not found.'
    );
    header('Location:index.php');
    exit;
}
if ($throwaway['business_status'] != 'draft') {
    setFlash(
        'warning',
        'Business day is already submitted.'
    );
    header('Location:index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = "Edit Throw Away";
$breadcrumbs = [
    [
        'title' => 'Product Throw Away',
        'link'  => 'index.php'
    ],
    [
        'title' => 'Edit Throw Away'
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
            <input type="hidden" name="throwaway_id" value="<?= $throwaway['throwaway_id']; ?>">
            <input type="hidden" name="inventory_header_id" value="<?= $throwaway['inventory_header_id']; ?>">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Product Throw Away</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($throwaway['product_code']); ?> - <?= htmlspecialchars($throwaway['product_name']); ?>" readonly>
                            <input type="hidden" name="product_id" value="<?= $throwaway['product_id']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Store Throw Away Quantity<span class="text-danger">*</span></label>
                            <input type="number" name="store_qty" class="form-control text-end" step="0.01" min="0.01" value="<?= number_format($throwaway['store_qty'], 2, '.', ''); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" maxlength="255"><?= htmlspecialchars($throwaway['remarks']); ?></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Accounting Quantity</label>
                            <input type="text" class="form-control text-end" value="<?= $throwaway['accounting_qty'] !== null ? number_format($throwaway['accounting_qty'], 2) : 'Pending Verification'; ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Verification Status</label>
                            <input type="text" class="form-control" value="<?= ucfirst($throwaway['status']); ?>" readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i>&nbsp;Back</a>
                       <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i>&nbsp;Update Throw Away</button>                        
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>