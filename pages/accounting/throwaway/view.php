<?php

/*
|--------------------------------------------------------------------------
| Throw Away Verification - View
|--------------------------------------------------------------------------
| Accounting Module
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

/*
|--------------------------------------------------------------------------
| Validate Inventory Header
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlash(
        'danger',
        'Invalid throw away record.'
    );
    header('Location: index.php');
    exit;
}
$inventory_header_id = (int)$_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Header
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
    pih.inventory_header_id,
    pih.business_date,
    pih.business_status,
    pih.location_id,
    l.location_name,
    u.full_name AS submitted_by
FROM product_inventory_header pih
INNER JOIN locations l
    ON l.location_id = pih.location_id
LEFT JOIN users u
    ON u.user_id = pih.submitted_by
WHERE
    pih.inventory_header_id = ?
LIMIT 1
");
$stmt->bind_param("i", $inventory_header_id);
$stmt->execute();
$headerResult = $stmt->get_result();
if ($headerResult->num_rows == 0) {
    setFlash(
        'danger',
        'Record not found.'
    );
    header('Location:index.php');
    exit;
}
$header = $headerResult->fetch_assoc();
$stmt->close();
enforceStorePermission($conn, (int) $header['location_id']);

/*
|--------------------------------------------------------------------------
| Load Products
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
    pid.inventory_detail_id,
    p.product_id,
    p.product_code,
    p.product_name,
    p.category,
    pid.beginning_qty,
    pid.received_qty,
    pid.pdr_qty,
    pid.ending_qty,
    pt.throwaway_id,
    pt.store_qty,
    pt.accounting_qty,
    pt.remarks,
    pt.status
FROM product_inventory_details pid
INNER JOIN products p
    ON p.product_id = pid.product_id
LEFT JOIN product_throwaway pt
    ON pt.inventory_header_id = pid.inventory_header_id
    AND pt.product_id = pid.product_id
WHERE
    pid.inventory_header_id = ?
ORDER BY
    p.category,
    p.product_name
");
$stmt->bind_param("i", $inventory_header_id);
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Throw Away Verification";
$breadcrumbs = [
    [
        'title' => 'Throw Away Verification',
        'link'  => 'index.php'
    ],
    [
        'title' => 'View'
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
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Throw Away Verification</h5>
                        <small class="text-muted">Review and verify the store's throw away before generating product variance.</small>
                    </div>
                    <div>
                        <?php
                            $statusClass = 'warning';
                            switch($header['business_status']){
                                case 'verified':
                                    $statusClass='success';
                                break;
                                case 'generated':
                                    $statusClass='primary';
                                break;
                                case 'locked':
                                    $statusClass='dark';
                                break;
                            }
                        ?>
                        <span class="badge bg-<?= $statusClass ?>"><?= strtoupper($header['business_status']) ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="fw-bold">Business Date</label>
                        <div><?= date('F d, Y',strtotime($header['business_date'])) ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Store</label>
                        <div><?= htmlspecialchars($header['location_name']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Submitted By</label>
                        <div><?= htmlspecialchars($header['submitted_by']) ?></div>
                    </div>
                </div>
                <hr>
                <form action="update.php" method="POST">
                    <input type="hidden" name="inventory_header_id" value="<?= $inventory_header_id ?>">
                    <div class="table-responsive">
                        <table id="throwAwayDetailsTable" class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="120">Code</th>
                                    <th>Product</th>
                                    <th class="text-end">Beginning</th>
                                    <th class="text-end">Received</th>
                                    <th class="text-end">PDR</th>
                                    <th class="text-end">Ending</th>
                                    <th class="text-end">Store TA</th>
                                    <th class="text-end">Accounting TA</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $totalStoreTA = 0;
                                    $totalAccountingTA = 0;

                                    while ($row = $products->fetch_assoc()) :
                                        $totalStoreTA += (float)$row['store_qty'];
                                        $totalAccountingTA += (float)$row['accounting_qty'];
                                ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($row['product_code']) ?>
                                            <input type="hidden" name="throwaway_id[]" value="<?= $row['throwaway_id'] ?>">
                                            <input type="hidden" name="product_id[]" value="<?= $row['product_id'] ?>">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['product_name']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($row['category']) ?></small>
                                        </td>
                                        <td class="text-end"><?= number_format($row['beginning_qty'],2) ?></td>
                                        <td class="text-end"><?= number_format($row['received_qty'],2) ?></td>
                                        <td class="text-end"><?= number_format($row['pdr_qty'],2) ?></td>
                                        <td class="text-end"><?= number_format($row['ending_qty'],2) ?></td>
                                        <td class="text-end"><span class="fw-bold"><?= number_format($row['store_qty'],2) ?></span></td>
                                        <td width="150"><input type="number" step="0.01" min="0" class="form-control text-end" name="accounting_qty[]" value="<?= $row['accounting_qty'] ?>" <?= $header['business_status']=='generated' || $header['business_status']=='locked' ? 'readonly' : '' ?>></td>
                                        <td><input type="text" class="form-control" name="remarks[]" value="<?= htmlspecialchars($row['remarks']) ?>" <?= $header['business_status']=='generated' || $header['business_status']=='locked' ? 'readonly' : '' ?>></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="6" class="text-end">TOTAL</th>
                                    <th class="text-end"><?= number_format($totalStoreTA,2) ?></th>
                                    <th class="text-end"><?= number_format($totalAccountingTA,2) ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <hr>
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle-fill"></i>
                                <strong>Accounting Reminder:</strong>
                                Verify the actual throw away returned by the store against the reported throw away. Once verified, these values will become the official Throw Away quantities used during Product Variance generation.
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Back</a>
                            <?php if($header['business_status']=='verified' || $header['business_status']=='submitted') : ?>
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill"></i>&nbsp;Save Verification</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
    include '../../../includes/layout/footer.php';
?>
