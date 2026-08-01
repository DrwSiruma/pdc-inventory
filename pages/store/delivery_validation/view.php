<?php

/*
|--------------------------------------------------------------------------
| Store Delivery Validation
|--------------------------------------------------------------------------
| View Delivery
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

$deliveryHeaderId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);
if(!$deliveryHeaderId){
    setFlash(
        'danger',
        'Invalid delivery selected.'
    );
    header('Location:index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Logged-in User
|--------------------------------------------------------------------------
*/

$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT
        u.location_id,
        l.location_name
    FROM users u
    INNER JOIN locations l
    ON l.location_id=u.location_id
    WHERE u.user_id=?
    LIMIT 1
");
$stmt->bind_param("i",$userId);
$stmt->execute();
$user=$stmt->get_result()->fetch_assoc();
$stmt->close();
$locationId=$user['location_id'];

/*
|--------------------------------------------------------------------------
| Load Delivery Header
|--------------------------------------------------------------------------
*/

$stmt=$conn->prepare("
    SELECT
    *
    FROM product_delivery_header
    WHERE
    delivery_header_id=?
    AND location_id=?
    LIMIT 1
");
$stmt->bind_param(
"ii",
$deliveryHeaderId,
$locationId
);
$stmt->execute();
$header=$stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$header){
    setFlash(
        'danger',
        'Delivery not found.'
    );
    header('Location:index.php');
    exit;
}

$isReadOnly = ($header['delivery_status'] != 'pending');

/*
|--------------------------------------------------------------------------
| Load Delivery Details
|--------------------------------------------------------------------------
*/

$stmt=$conn->prepare("
    SELECT
        l.delivery_id,
        l.product_id,
        p.product_code,
        p.product_name,
        l.expected_qty,
        l.actual_qty,
        l.short_qty,
        l.remarks
    FROM product_delivery_logs l
    INNER JOIN products p
        ON p.product_id=l.product_id
    WHERE
        l.delivery_header_id=?
    ORDER BY
        p.product_name ASC
");
$stmt->bind_param(
    "i",
    $deliveryHeaderId
);
$stmt->execute();
$details=$stmt->get_result();

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle="Validate Delivery";
$breadcrumbs=[
    [
        'title'=>'Delivery Validation',
        'link'=>'index.php'
    ],
    [
        'title'=>'Validate Delivery'
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
                <h5 class="mb-0">Validate Delivery</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Business Date</strong>
                        <br>
                        <?=date('F d, Y',strtotime($header['business_date']))?>
                    </div>
                    <div class="col-md-3">
                        <strong>Delivery</strong>
                        <br>
                        <?= $header['delivery_no']; ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Reference No.</strong>
                        <br>
                        <?=htmlspecialchars($header['reference_no']);?>
                    </div>
                    <div class="col-md-3">
                        <strong>Status</strong>
                        <br>
                        <span class="badge bg-warning"><?=ucfirst($header['delivery_status']);?></span>
                    </div>
                </div>
            </div>
        </div>

        <form action="save.php" method="POST">
            <input type="hidden" name="delivery_header_id" value="<?= $deliveryHeaderId; ?>">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Delivery Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="deliveryItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">#</th>
                                    <th>Product</th>
                                    <th width="120" class="text-end">Expected</th>
                                    <th width="120" class="text-end">Actual</th>
                                    <th width="120" class="text-end">Short</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $no = 1;
                                    while ($row = $details->fetch_assoc()) :
                                ?>
                                    <tr>
                                        <td>
                                            <?= $no++; ?>
                                            <input type="hidden" name="delivery_id[]" value="<?= $row['delivery_id']; ?>">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['product_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($row['product_code']); ?></small>
                                        </td>
                                        <td><input type="text" class="form-control text-end bg-light expectedQty" value="<?= number_format($row['expected_qty'],2,'.',''); ?>" readonly></td>
                                        <td><input type="number" name="actual_qty[]" class="form-control text-end actualQty" step="0.01" min="0" value="<?= number_format($row['actual_qty'],2,'.',''); ?>" <?= $isReadOnly ? 'readonly' : ''; ?> required></td>
                                        <td><input type="text" class="form-control text-end bg-warning shortQty" value="<?= number_format($row['short_qty'],2,'.',''); ?>" readonly></td>
                                        <td><input type="text" name="remarks[]" class="form-control" value="<?= htmlspecialchars($row['remarks']); ?>" maxlength="255"></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i>&nbsp;Back</a>
                <?php if ($header['delivery_status'] == 'pending') : ?>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill"></i>&nbsp;Save Validation</button>
                <?php else : ?>
                    <button type="button" class="btn btn-success" disabled>Already Validated</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>