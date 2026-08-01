<?php

/*
|--------------------------------------------------------------------------
| Store Product Inventory
|--------------------------------------------------------------------------
| Daily Inventory Encoding
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

$inventoryHeaderId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);
if (!$inventoryHeaderId) {
    setFlash(
        'danger',
        'Invalid inventory.'
    );
    header('Location:index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Logged-in Store
|--------------------------------------------------------------------------
*/

$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT
        u.location_id,
        l.location_name
    FROM users u
    INNER JOIN locations l
    ON l.location_id=u.location_id
    WHERE
        u.user_id=?
    LIMIT 1
");
if(!$stmt){
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
$locationId   = (int)$user['location_id'];
$locationName = $user['location_name'];

/*
|--------------------------------------------------------------------------
| Load Inventory Header
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("SELECT * FROM product_inventory_header WHERE inventory_header_id=? AND location_id=? LIMIT 1");
if(!$stmt){
    die($conn->error);
}
$stmt->bind_param(
    "ii",
    $inventoryHeaderId,
    $locationId
);
$stmt->execute();
$header = $stmt
    ->get_result()
    ->fetch_assoc();
$stmt->close();
if(!$header){
    setFlash(
        'danger',
        'Inventory not found.'
    );
    header('Location:index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Read Only
|--------------------------------------------------------------------------
*/

$isReadOnly = ($header['business_status'] != 'draft');

/*
|--------------------------------------------------------------------------
| Load Inventory Details
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        d.inventory_detail_id,
        d.product_id,
        d.beginning_qty,
        d.received_qty,
        d.pdr_qty,
        d.throwaway_qty,
        d.ending_qty,
        d.expected_qty,
        d.variance_qty,
        p.product_code,
        p.product_name
    FROM product_inventory_details d
    INNER JOIN products p
    ON p.product_id=d.product_id
    WHERE
        d.inventory_header_id=?
    ORDER BY
        p.product_name ASC
");
if(!$stmt){
    die($conn->error);
}
$stmt->bind_param(
    "i",
    $inventoryHeaderId
);
$stmt->execute();
$details = $stmt->get_result();
/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = "Product Inventory";
$breadcrumbs = [
    [
        'title'=>'Product Inventory',
        'link'=>'index.php'
    ],
    [
        'title'=>'Daily Encoding'
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
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Daily Product Inventory</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Store</strong>
                        <br>
                        <?= htmlspecialchars($locationName); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Business Date</strong>
                        <br>
                        <?= date(
                        'F d, Y',
                        strtotime($header['business_date'])
                        ); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Status</strong>
                        <br>
                        <span class="badge bg-primary"><?= ucfirst($header['business_status']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <form action="save.php" method="POST">
            <input type="hidden" name="inventory_header_id" value="<?= $inventoryHeaderId; ?>">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Inventory Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="inventoryTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-end">Beginning</th>
                                    <th class="text-end">Received</th>
                                    <th class="text-end">PDR</th>
                                    <th class="text-end">Throw Away</th>
                                    <th class="text-end">Expected</th>
                                    <th class="text-end">Ending</th>
                                    <th class="text-end">Variance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = $details->fetch_assoc()) :
                                    $expectedQty =
                                        $row['beginning_qty']
                                        + $row['received_qty']
                                        + $row['pdr_qty']
                                        - $row['throwaway_qty'];
                                    $varianceQty =
                                        $expectedQty
                                        - $row['ending_qty'];
                                ?>
                                    <tr>
                                        <td>
                                            <?= $no++; ?>
                                            <input type="hidden" name="detail_id[]" value="<?= $row['inventory_detail_id']; ?>">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['product_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($row['product_code']); ?></small>
                                        </td>
                                        <td><input type="text" class="form-control text-end bg-light" value="<?= number_format($row['beginning_qty'], 2, '.', ''); ?>" readonly></td>
                                        <td><input type="text" class="form-control text-end bg-light" value="<?= number_format($row['received_qty'], 2, '.', ''); ?>" readonly></td>
                                        <td><input  type="number" name="pdr_qty[]" class="form-control text-end pdrQty" step="0.01"  min="0"   value="<?= number_format($row['pdr_qty'], 2, '.', ''); ?>" <?= $isReadOnly ? 'readonly' : ''; ?>></td>
                                        <td><input type="number" name="throwaway_qty[]" class="form-control text-end throwQty" step="0.01" min="0" value="<?= number_format($row['throwaway_qty'], 2, '.', ''); ?>" <?= $isReadOnly ? 'readonly' : ''; ?>></td>
                                        <td><input type="text" class="form-control text-end bg-warning expectedQty" value="<?= number_format($expectedQty, 2, '.', ''); ?>" readonly></td>
                                        <td><input type="number" name="ending_qty[]" class="form-control text-end endingQty" step="0.01" min="0" value="<?= number_format($row['ending_qty'], 2, '.', ''); ?>" <?= $isReadOnly ? 'readonly' : ''; ?>></td>
                                        <td><input type="text" class="form-control text-end bg-danger varianceQty" value="<?= number_format($varianceQty, 2, '.', ''); ?>" readonly></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i>&nbsp;Back</a>
                <div>
                    <?php if (!$isReadOnly) : ?>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i>&nbsp;Save Progress</button>
                        <a href="submit.php?id=<?= $inventoryHeaderId; ?>" class="btn btn-success" onclick="return confirm('Submit today's inventory? You will no longer be able to edit it.');">
                        <i class="bi bi-send-check-fill"></i>&nbsp;Submit Inventory</a>
                    <?php else : ?>
                        <button type="button" class="btn btn-success" disabled>&nbsp;Inventory Submitted</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>