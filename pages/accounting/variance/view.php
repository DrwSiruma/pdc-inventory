<?php

/*
|--------------------------------------------------------------------------
| Product Variance View
|--------------------------------------------------------------------------
| Displays the generated variance for one business date and one store.
| This page serves as the official Accounting review before locking.
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
| Validate Request
|--------------------------------------------------------------------------
*/

$inventoryHeaderId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;
if ($inventoryHeaderId <= 0) {
    setFlash('danger', 'Invalid variance record.');
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Load Inventory Header
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
    h.inventory_header_id,
    h.business_date,
    h.business_status,
    h.generated_at,
    h.location_id,
    l.location_name,
    submitter.full_name AS submitted_by,
    verifier.full_name AS verified_by,
    generator.full_name AS generated_by
FROM product_inventory_header h
INNER JOIN locations l
    ON l.location_id = h.location_id
LEFT JOIN users submitter
    ON submitter.user_id = h.submitted_by
LEFT JOIN users verifier
    ON verifier.user_id = h.verified_by
LEFT JOIN users generator
    ON generator.user_id = h.generated_by
WHERE
    h.inventory_header_id = ?
LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inventoryHeaderId);
$stmt->execute();
$header = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$header) {
    setFlash('danger', 'Variance record not found.');
    header('Location: index.php');
    exit;
}
enforceStorePermission($conn, (int) $header['location_id']);

/*
|--------------------------------------------------------------------------
| Load Product Variance
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
    d.inventory_detail_id,
    p.product_code,
    p.product_name,
    d.beginning_qty,
    d.received_qty,
    d.pdr_qty,
    d.throwaway_qty,
    d.expected_qty,
    d.ending_qty,
    d.variance_qty
FROM product_inventory_details d
INNER JOIN products p
    ON p.product_id = d.product_id
WHERE
    d.inventory_header_id = ?
ORDER BY
    p.product_name ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inventoryHeaderId);
$stmt->execute();
$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Compute Totals
|--------------------------------------------------------------------------
*/

$totalBeginning = 0;
$totalReceived = 0;
$totalPDR = 0;
$totalThrowAway = 0;
$totalExpected = 0;
$totalEnding = 0;
$totalVariance = 0;

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Product Variance";
$breadcrumbs = [
    [
        'title' => 'Product Variance',
        'link'  => 'index.php'
    ],
    [
        'title' => 'Variance Details'
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
        <!-- ==========================================================
        Variance Summary
        =========================================================== -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Product Variance Review</h4>
                    <small class="text-muted">Official inventory variance generated for this business day.</small>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i>Back</a>
                    <button class="btn btn-primary" onclick="window.print();"><i class="bi bi-printer-fill"></i>Print</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="fw-bold text-muted">Business Date</label>
                        <div><?= date('F d, Y', strtotime($header['business_date'])); ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold text-muted">Store</label>
                        <div><?= htmlspecialchars($header['location_name']); ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold text-muted">Submitted By</label>
                        <div><?= htmlspecialchars($header['submitted_by'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold text-muted">Verified By</label>
                        <div><?= htmlspecialchars($header['verified_by'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold text-muted">Generated By</label>
                        <div><?= htmlspecialchars($header['generated_by'] ?? '-'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold text-muted">Generated Date</label>
                        <div>
                            <?= !empty($header['generated_at'])
                                ? date('F d, Y h:i A', strtotime($header['generated_at']))
                                : '-'; ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold text-muted">Business Status</label>
                        <div>
                            <?php
                                switch ($header['business_status']) {
                                    case 'generated':
                                        $badge = 'primary';
                                        break;
                                    case 'locked':
                                        $badge = 'dark';
                                        break;
                                    case 'verified':
                                        $badge = 'success';
                                        break;
                                    default:
                                        $badge = 'warning';
                                        break;
                                }
                            ?>
                            <span class="badge bg-<?= $badge; ?>"><?= strtoupper($header['business_status']); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold text-muted">Total Products</label>
                        <div><?= $result->num_rows; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ==========================================================
        Product Variance Table
        =========================================================== -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Product Variance Details</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="varianceTable" class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
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
                            <?php while ($row = $result->fetch_assoc()) : ?>
                            <?php
                                $totalBeginning += $row['beginning_qty'];
                                $totalReceived += $row['received_qty'];
                                $totalPDR += $row['pdr_qty'];
                                $totalThrowAway += $row['throwaway_qty'];
                                $totalExpected += $row['expected_qty'];
                                $totalEnding += $row['ending_qty'];
                                $totalVariance += $row['variance_qty'];
                                $variance = (float)$row['variance_qty'];
                                if ($variance > 0) {
                                    $varianceBadge = "danger";
                                } elseif ($variance < 0) {
                                    $varianceBadge = "warning";
                                } else {
                                    $varianceBadge = "success";
                                }
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['product_code']); ?></td>
                                    <td><strong><?= htmlspecialchars($row['product_name']); ?></strong></td>
                                    <td class="text-end"><?= number_format($row['beginning_qty'],2); ?></td>
                                    <td class="text-end"><?= number_format($row['received_qty'],2); ?></td>
                                    <td class="text-end"><?= number_format($row['pdr_qty'],2); ?></td>
                                    <td class="text-end"><?= number_format($row['throwaway_qty'],2); ?></td>
                                    <td class="text-end fw-bold"><?= number_format($row['expected_qty'],2); ?></td>
                                    <td class="text-end"><?= number_format($row['ending_qty'],2); ?></td>
                                    <td class="text-end"><span class="badge bg-<?= $varianceBadge; ?>"><?= number_format($variance,2); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">GRAND TOTAL</th>
                                <th class="text-end"><?= number_format($totalBeginning,2); ?></th>
                                <th class="text-end"><?= number_format($totalReceived,2); ?></th>
                                <th class="text-end"><?= number_format($totalPDR,2); ?></th>
                                <th class="text-end"><?= number_format($totalThrowAway,2); ?></th>
                                <th class="text-end"><?= number_format($totalExpected,2); ?></th>
                                <th class="text-end"><?= number_format($totalEnding,2); ?></th>
                                <th class="text-end">
                                    <?php
                                        if ($totalVariance > 0) {
                                            $grandBadge = "danger";
                                        } elseif ($totalVariance < 0) {
                                            $grandBadge = "warning";
                                        } else {
                                            $grandBadge = "success";
                                        }
                                    ?>
                                    <span class="badge bg-<?= $grandBadge; ?>"><?= number_format($totalVariance,2); ?></span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- ==========================================================
        Variance Summary
        ========================================================== -->
        <?php
            $positiveVariance = 0;
            $negativeVariance = 0;
            $zeroVariance = 0;

            /*
            |--------------------------------------------------------------------------
            | Reload Variance Statistics
            |--------------------------------------------------------------------------
            */

            $statsSQL = "
            SELECT
                variance_qty
            FROM product_inventory_details
            WHERE inventory_header_id = ?
            ";
            $statsStmt = $conn->prepare($statsSQL);
            $statsStmt->bind_param("i", $inventoryHeaderId);
            $statsStmt->execute();
            $statsResult = $statsStmt->get_result();
            while ($stat = $statsResult->fetch_assoc()) {
                $qty = (float)$stat['variance_qty'];
                if ($qty > 0) {
                    $positiveVariance++;
                } elseif ($qty < 0) {
                    $negativeVariance++;
                } else {
                    $zeroVariance++;
                }
            }
            $statsStmt->close();
        ?>
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Variance Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h2 class="text-danger"><?= $positiveVariance; ?></h2>
                        <small class="text-muted">Positive Variance</small>
                    </div>
                    <div class="col-md-3">
                        <h2 class="text-warning"><?= $negativeVariance; ?></h2>
                        <small class="text-muted">Negative Variance</small>
                    </div>
                    <div class="col-md-3">
                        <h2 class="text-success"><?= $zeroVariance; ?></h2>
                        <small class="text-muted">Zero Variance</small>
                    </div>
                    <div class="col-md-3">
                        <h2 class="<?= ($totalVariance == 0) ? 'text-success' : 'text-danger'; ?>"><?= number_format($totalVariance,2); ?></h2>
                        <small class="text-muted">Net Variance</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- ==========================================================
        Accounting Actions
        ========================================================== -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Accounting Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end gap-2">
                    <?php if ($header['business_status'] == 'generated') : ?>
                        <a href="verification.php?id=<?= $inventoryHeaderId; ?>" class="btn btn-success"><i class="bi bi-check-circle-fill"></i>&nbsp;Review & Verify Variance</a>
                    <?php endif; ?>
                    <?php if ($header['business_status'] == 'locked') : ?>
                        <button class="btn btn-dark" disabled>
                            <i class="bi bi-lock-fill"></i>&nbsp;Business Day Locked
                        </button>
                    <?php endif; ?>
                    <button onclick="window.print();" class="btn btn-primary"><i class="bi bi-printer-fill"></i>&nbsp;Print</button>
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i>&nbsp;Back</a>
                </div>
            </div>
        </div>
        <!-- ==========================================================
        ERP Variance Summary
        ========================================================== -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card border-start border-danger border-4 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Positive Variance</small>
                        <h2 class="text-danger"><?= $positiveVariance; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-warning border-4 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Negative Variance</small>
                        <h2 class="text-warning"><?= $negativeVariance; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-success border-4 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Zero Variance</small>
                        <h2 class="text-success"><?= $zeroVariance; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-primary border-4 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Net Variance</small>
                        <h2 class="<?= ($totalVariance == 0) ? 'text-success' : 'text-danger'; ?>"><?= number_format($totalVariance,2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <!-- ==========================================================
            Accounting Actions
        ========================================================== -->
        <div class="card shadow-sm mt-4">
            <div class="card-body text-end">
                <?php if ($header['business_status'] == 'generated') : ?>
                    <a href="approve.php?id=<?= $inventoryHeaderId; ?>" class="btn btn-success"><i class="bi bi-check-circle-fill"></i>&nbsp;Approve Variance</a>
                <?php endif; ?>
                <?php if ($header['business_status'] == 'approved') : ?>
                    <a href="lock.php?id=<?= $inventoryHeaderId; ?>" class="btn btn-dark"><i class="bi bi-lock-fill"></i>&nbsp;Lock Business Day</a>
                <?php endif; ?>
                <?php if ($header['business_status'] == 'locked') : ?>
                    <button class="btn btn-dark" disabled>
                        <i class="bi bi-lock-fill"></i>&nbsp;Locked
                    </button>
                <?php endif; ?>
                <button onclick="window.print();" class="btn btn-primary">
                    <i class="bi bi-printer-fill"></i>Print
                </button>
                <a href="index.php" class="btn btn-secondary">&nbsp;Back</a>
            </div>
        </div>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>
