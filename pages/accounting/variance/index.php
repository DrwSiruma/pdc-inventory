<?php

/*
|--------------------------------------------------------------------------
| Product Variance
|--------------------------------------------------------------------------
| Accounting Variance Monitoring
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

$assignedStores = getAccessibleStoreIds($conn);
$storeFilter = buildStoreWhereClause($assignedStores, 'h.location_id');

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$selectedStore = isset($_GET['store'])
    ? (int)$_GET['store']
    : 0;
$selectedDate = $_GET['date'] ?? '';
$selectedStatus = $_GET['status'] ?? '';

/*
|--------------------------------------------------------------------------
| Load Stores
|--------------------------------------------------------------------------
*/

$storeResult = $conn->query("
    SELECT
        location_id,
        location_name
    FROM locations
    WHERE 1=1
    " . buildStoreWhereClause($assignedStores) . "
    ORDER BY location_name
");

/*
|--------------------------------------------------------------------------
| Build Query
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
    h.inventory_header_id,
    h.business_date,
    h.business_status,
    l.location_name,
    COUNT(d.inventory_detail_id) AS total_products,
    SUM(d.variance_qty) AS total_variance
FROM product_inventory_header h
INNER JOIN locations l
    ON l.location_id = h.location_id
INNER JOIN product_inventory_details d
    ON d.inventory_header_id = h.inventory_header_id
WHERE h.business_status IN
(
    'generated',
    'approved',
    'locked'
)
{$storeFilter}
";

$params = [];
$types = "";

/*
|--------------------------------------------------------------------------
| Store Filter
|--------------------------------------------------------------------------
*/

if ($selectedStore > 0) {
    $sql .= " AND h.location_id = ? ";
    $types .= "i";
    $params[] = $selectedStore;
}

/*
|--------------------------------------------------------------------------
| Business Date Filter
|--------------------------------------------------------------------------
*/

if (!empty($selectedDate)) {
    $sql .= " AND h.business_date = ? ";
    $types .= "s";
    $params[] = $selectedDate;
}

/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

if (!empty($selectedStatus)) {
    $sql .= " AND h.business_status = ? ";
    $types .= "s";
    $params[] = $selectedStatus;
}

/*
|--------------------------------------------------------------------------
| Group By
|--------------------------------------------------------------------------
*/

$sql .= "
GROUP BY
    h.inventory_header_id,
    h.business_date,
    h.business_status,
    l.location_name
ORDER BY
    h.business_date DESC,
    l.location_name ASC
";

/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Product Variance";
$breadcrumbs = [
    [
        'title' => 'Product Variance'
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
        Filter Card
        ========================================================== -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Product Variance</h5>
                <small class="text-muted">Monitor generated, approved and locked product variances.</small>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Store</label>
                            <select name="store" class="form-control">
                                <option value="0">All Stores</option>
                                <?php while($store = $storeResult->fetch_assoc()) : ?>
                                    <option value="<?= $store['location_id']; ?>" <?= ($selectedStore == $store['location_id']) ? 'selected' : ''; ?>><?= htmlspecialchars($store['location_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Business Date</label>
                            <input type="date" name="date" value="<?= htmlspecialchars($selectedDate); ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="generated" <?= ($selectedStatus=='generated') ? 'selected' : ''; ?>>Generated</option>
                                <option value="approved" <?= ($selectedStatus=='approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="locked" <?= ($selectedStatus=='locked') ? 'selected' : ''; ?>>Locked</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i>&nbsp;Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- ==========================================================
        Variance List
        ========================================================== -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Product Variance List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="variancesTable" class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="140">Business Date</th>
                                <th>Store</th>
                                <th width="120" class="text-center">Products</th>
                                <th width="150" class="text-end">Net Variance</th>
                                <th width="140" class="text-center">Status</th>
                                <th width="120" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $generated = 0;
                                $approved = 0;
                                $locked = 0;
                                $grandVariance = 0;

                                while($row = $result->fetch_assoc()) :
                                    $grandVariance += (float)$row['total_variance'];
                                    switch($row['business_status']){
                                        case 'generated':
                                            $generated++;
                                            $badge='warning';
                                        break;
                                        case 'approved':
                                            $approved++;
                                            $badge='success';
                                        break;
                                        case 'locked':
                                            $locked++;
                                            $badge='dark';
                                        break;
                                        default:
                                            $badge='secondary';
                                    }
                            ?>
                                <tr>
                                    <td><?= date('M d, Y',strtotime($row['business_date'])); ?></td>
                                    <td><?= htmlspecialchars($row['location_name']); ?></td>
                                    <td class="text-center"><?= number_format($row['total_products']); ?></td>
                                    <td class="text-end">
                                        <?php $variance = (float)$row['total_variance']; ?>
                                        <span class="<?= ($variance==0)?'text-success':'text-danger'; ?> fw-bold"><?= number_format($variance,2); ?></span>
                                    </td>
                                    <td class="text-center"><span class="badge bg-<?= $badge; ?>"><?= ucfirst($row['business_status']); ?></span></td>
                                    <td class="text-center">
                                        <a href="view.php?id=<?= $row['inventory_header_id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye-fill"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- ==========================================================
        Variance Summary
        ========================================================== -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card border-start border-warning border-4 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Generated</small>
                        <h2 class="text-warning mb-0"><?= $generated; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-success border-4 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Approved</small>
                        <h2 class="text-success mb-0"><?= $approved; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-dark border-4 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Locked</small>
                        <h2 class="text-dark mb-0"><?= $locked; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-primary border-4 shadow-sm">
                    <div class="card-body">
                        <small class="text-muted">Net Variance</small>
                        <h2 class="<?= ($grandVariance == 0) ? 'text-success' : 'text-danger'; ?> mb-0"><?= number_format($grandVariance,2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>
