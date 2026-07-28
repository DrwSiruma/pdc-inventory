<?php

/*
|--------------------------------------------------------------------------
| Daily Product Inventory Monitor
|--------------------------------------------------------------------------
| Displays today's inventory workflow for assigned stores.
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
requireRole('accounting');
/*
|--------------------------------------------------------------------------
| Store Permission
|--------------------------------------------------------------------------
*/
$assignedStores = getAccessibleStoreIds($conn);
$storeFilter = buildStoreWhereClause(
    $assignedStores,
    'l.location_id'
);
/*
|--------------------------------------------------------------------------
| Selected Business Date
|--------------------------------------------------------------------------
*/
$businessDate = $_GET['business_date'] ?? date('Y-m-d');
/*
|--------------------------------------------------------------------------
| Load Daily Business Status
|--------------------------------------------------------------------------
*/
$sql = "
SELECT
    l.location_id,
    l.location_code,
    l.location_name,
    h.inventory_header_id,
    h.business_date,
    h.business_status,
    h.submitted_at,
    h.generated_at,
    u.full_name AS submitted_by,
    (
        SELECT COUNT(*)
        FROM product_throwaway t
        WHERE
            t.inventory_header_id = h.inventory_header_id
            AND t.status='pending'
    ) AS pending_throwaway,
    (
        SELECT COUNT(*)
        FROM product_throwaway t
        WHERE
            t.inventory_header_id = h.inventory_header_id
            AND t.status='verified'
    ) AS verified_throwaway
FROM locations l
LEFT JOIN product_inventory_header h
    ON h.location_id=l.location_id
    AND h.business_date=?
LEFT JOIN users u
    ON u.user_id=h.submitted_by
WHERE
    l.location_type='store'
    AND l.status='active'
    {$storeFilter}
ORDER BY
    l.location_name
";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "s",
    $businessDate
);
$stmt->execute();
$result = $stmt->get_result();
/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/
$pageTitle = "Daily Inventory Monitor";
$breadcrumbs = [
    [
        'title'=>'Product Inventory',
        'link'=>'index.php'
    ],
    [
        'title'=>'Daily Monitor'
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
                    <h5 class="mb-0">Daily Inventory Monitor</h5>
                    <small class="text-muted">Monitor today's inventory workflow for your assigned stores.</small>
                </div>
                <form method="GET" class="d-flex">
                    <input type="date" name="business_date" class="form-control me-2" value="<?= htmlspecialchars($businessDate); ?>">
                    <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="dailyTable">
                        <thead class="table-light">
                            <tr>
                                <th>Store</th>
                                <th width="120">Business Date</th>
                                <th width="140">Inventory</th>
                                <th width="140">Throw Away</th>
                                <th width="140">Variance</th>
                                <th width="120">Status</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <?php
                                /*
                                |--------------------------------------------------------------------------
                                | Inventory Status
                                |--------------------------------------------------------------------------
                                */
                                $inventoryStatus = 'Pending';
                                $inventoryBadge = 'secondary';
                                if (!empty($row['inventory_header_id'])) {
                                    switch ($row['business_status']) {
                                        case 'submitted':
                                            $inventoryStatus = 'Submitted';
                                            $inventoryBadge = 'primary';
                                            break;
                                        case 'verified':
                                            $inventoryStatus = 'Verified';
                                            $inventoryBadge = 'info';
                                            break;
                                        case 'generated':
                                            $inventoryStatus = 'Generated';
                                            $inventoryBadge = 'success';
                                            break;
                                        case 'locked':
                                            $inventoryStatus = 'Locked';
                                            $inventoryBadge = 'dark';
                                            break;
                                        default:
                                            $inventoryStatus = ucfirst($row['business_status']);
                                            $inventoryBadge = 'secondary';
                                    }
                                }
                                /*
                                |--------------------------------------------------------------------------
                                | Throw Away Status
                                |--------------------------------------------------------------------------
                                */
                                $throwawayStatus = '-';
                                $throwawayBadge = 'secondary';
                                if (!empty($row['inventory_header_id'])) {
                                    if ($row['pending_throwaway'] > 0) {
                                        $throwawayStatus = 'Pending';
                                        $throwawayBadge = 'warning';
                                    } elseif ($row['verified_throwaway'] > 0) {
                                        $throwawayStatus = 'Verified';
                                        $throwawayBadge = 'success';
                                    } else {
                                        $throwawayStatus = 'None';
                                        $throwawayBadge = 'secondary';
                                    }
                                }
                                /*
                                |--------------------------------------------------------------------------
                                | Variance Status
                                |--------------------------------------------------------------------------
                                */
                                $varianceStatus = '-';
                                $varianceBadge = 'secondary';
                                if ($row['business_status'] == 'verified') {
                                    $varianceStatus = 'Ready';
                                    $varianceBadge = 'warning';
                                }
                                if ($row['business_status'] == 'generated') {
                                    $varianceStatus = 'Generated';
                                    $varianceBadge = 'success';
                                }
                                if ($row['business_status'] == 'locked') {
                                    $varianceStatus = 'Locked';
                                    $varianceBadge = 'dark';
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['location_name']); ?></strong>
                                    <br>
                                    <small class="text-muted">  <?= htmlspecialchars($row['location_code']); ?></small>
                                </td>
                                <td><?= date('M d, Y', strtotime($businessDate)); ?></td>
                                <td><span class="badge bg-<?= $inventoryBadge; ?>"><?= $inventoryStatus; ?></span></td>
                                <td><span class="badge bg-<?= $throwawayBadge; ?>"><?= $throwawayStatus; ?></span></td>
                                <td><span class="badge bg-<?= $varianceBadge; ?>"><?= $varianceStatus; ?></span></td>
                                <td>
                                    <?php
                                        switch($row['business_status']){
                                            case 'submitted':
                                                echo '<span class="text-primary fw-bold">Waiting Verification</span>';
                                                break;
                                            case 'verified':
                                                echo '<span class="text-warning fw-bold">Ready for Variance</span>';
                                                break;
                                            case 'generated':
                                                echo '<span class="text-success fw-bold">Generated</span>';
                                                break;
                                            case 'locked':
                                                echo '<span class="text-dark fw-bold">Completed</span>';
                                                break;
                                            default:
                                                echo '<span class="text-secondary">Waiting Store</span>';
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if(!empty($row['inventory_header_id'])): ?>
                                        <a href="view.php?id=<?= $row['inventory_header_id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye-fill"></i>&nbsp;View</a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>&nbsp;No Record</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    $stmt->close();
    include '../../../includes/layout/footer.php';
?>