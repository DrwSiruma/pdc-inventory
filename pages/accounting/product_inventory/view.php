<?php

/*
|--------------------------------------------------------------------------
| View Product Inventory
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
| Validate Header ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlash('danger', 'Invalid inventory.');
    header('Location: index.php');
    exit;
}
$inventory_header_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Header
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        h.*,
        l.location_name,
        submitter.full_name AS submitted_by_name,
        verifier.full_name AS verified_by_name
    FROM product_inventory_header h
    INNER JOIN locations l
    ON l.location_id = h.location_id
    LEFT JOIN users submitter
    ON submitter.user_id = h.submitted_by
    LEFT JOIN users verifier
    ON verifier.user_id = h.verified_by
    WHERE h.inventory_header_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $inventory_header_id);
$stmt->execute();
$header = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$header) {
    setFlash('danger', 'Inventory not found.');
    header('Location: index.php');
    exit;
}
enforceStorePermission($conn, (int) $header['location_id']);

/*
|--------------------------------------------------------------------------
| Load Products
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        d.*,
        p.product_code,
        p.product_name,
        p.category
    FROM product_inventory_details d
    INNER JOIN products p
    ON p.product_id = d.product_id
    WHERE d.inventory_header_id = ?
    ORDER BY
    p.product_name ASC
");
$stmt->bind_param("i", $inventory_header_id);
$stmt->execute();
$details = $stmt->get_result();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = "View Product Inventory";
$breadcrumbs = [
    [
        'title'=>'Product Inventory',
        'link'=>'index.php'
    ],
    [
        'title'=>'View Inventory'
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
            <div class="card-header">
                <h5 class="mb-0">Product Inventory Validation</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="35%">Store</th>
                                <td><?= htmlspecialchars($header['location_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Business Date</th>
                                <td><?= date('F d, Y', strtotime($header['business_date'])); ?></td>
                            </tr>
                            <tr>
                                <th>Submitted By</th>
                                <td><?= $header['submitted_by_name'] ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Submitted At</th>
                                <td><?= $header['submitted_at'] ? date('M d, Y h:i A', strtotime($header['submitted_at'])) : '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-lg-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="35%">Status</th>
                                <td>
                                    <?php
                                        switch ($header['business_status']) {
                                            case 'submitted':
                                                $badge = 'primary';
                                            break;
                                            case 'verified':
                                                $badge = 'success';
                                            break;
                                            case 'generated':
                                                $badge = 'info';
                                            break;
                                            case 'locked':
                                                $badge = 'dark';
                                            break;
                                            default:
                                                $badge = 'warning';
                                            break;
                                        }
                                    ?>
                                    <span class="badge bg-<?= $badge; ?>">
                                        <?= ucfirst($header['business_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Verified By</th>
                                <td><?= $header['verified_by_name'] ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Verified At</th>
                                <td><?= $header['verified_at'] ? date('M d, Y h:i A', strtotime($header['verified_at'])) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Generated At</th>
                                <td><?= $header['generated_at'] ? date('M d, Y h:i A', strtotime($header['generated_at'])) : '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <hr>
                <h5 class="mb-3">Product Inventory Details</h5>
                <div class="table-responsive">
                    <table id="productInventoryDetailsTable" class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Product</th>
                                <th class="text-end">Beginning</th>
                                <th class="text-end">Received</th>
                                <th class="text-end">PDR</th>
                                <th class="text-end">Throw Away</th>
                                <th class="text-end">Ending</th>
                                <th class="text-end">Expected</th>
                                <th class="text-end">Variance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($details->num_rows > 0) : ?>
                                <?php while ($row = $details->fetch_assoc()) : ?>
                                    <?php
                                        $variance = (float) $row['variance_qty'];
                                        if ($variance > 0) {
                                            $varianceClass = 'text-danger fw-bold';
                                        } elseif ($variance < 0) {
                                            $varianceClass = 'text-warning fw-bold';
                                        } else {
                                            $varianceClass = 'text-success fw-bold';
                                        }
                                    ?>
                                    <tr>
                                       <td><?= htmlspecialchars($row['product_code']); ?></td>
                                       <td>
                                            <strong><?= htmlspecialchars($row['product_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($row['category']); ?></small>
                                        </td>
                                        <td class="text-end"><?= number_format($row['beginning_qty'], 2); ?></td>
                                        <td class="text-end"><?= number_format($row['received_qty'], 2); ?></td>
                                        <td class="text-end"><?= number_format($row['pdr_qty'], 2); ?></td>
                                        <td class="text-end"><?= number_format($row['throwaway_qty'], 2); ?></td>
                                        <td class="text-end"><?= number_format($row['ending_qty'], 2); ?></td>
                                        <td class="text-end"><?= number_format($row['expected_qty'], 2); ?></td>
                                        <td class="text-end <?= $varianceClass; ?>"><?= number_format($variance, 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No product inventory details found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <hr>
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong>Accounting Reminder:</strong>
                            Verify all product quantities, deliveries, throw away records, and ending inventory before generating the daily variance. Once the variance has been generated and the business day is locked, further modifications should only be performed by an authorized administrator.
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Back</a>
                        <?php if ($header['business_status'] == 'submitted') : ?>
                            <a href="verify.php?id=<?= $inventory_header_id; ?>" class="btn btn-success"><i class="bi bi-check-circle-fill"></i>&nbsp;Verify Inventory</a>
                        <?php endif; ?>
                        <?php if ($header['business_status'] == 'verified') : ?>
                            <form method="post" action="../variance/generate.php" class="d-inline">
                                <input type="hidden" name="id" value="<?= $inventory_header_id; ?>">
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Generate and freeze the product variance for this business day?');">
                                    <i class="bi bi-calculator-fill"></i>&nbsp;Generate Variance
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>
