<?php

/*
|--------------------------------------------------------------------------
| Throw Away Verification
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

requireLogin();
requireRole('accounting');

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedStore = $_GET['store'] ?? '';

/*
|--------------------------------------------------------------------------
| Load Stores
|--------------------------------------------------------------------------
*/

$stores = $conn->query("
    SELECT
        location_id,
        location_name
    FROM locations
    WHERE status='active'
    ORDER BY location_name
");

/*
|--------------------------------------------------------------------------
| Load Throw Away Queue
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
    pih.inventory_header_id,
    pih.business_date,
    l.location_name,
    COUNT(pt.throwaway_id) AS total_products,
    SUM(pt.store_qty) AS total_store_qty,
    SUM(pt.accounting_qty) AS total_accounting_qty,
    MIN(pt.status) AS status
FROM product_inventory_header pih
INNER JOIN locations l
    ON l.location_id = pih.location_id
LEFT JOIN product_throwaway pt
    ON pt.inventory_header_id = pih.inventory_header_id
WHERE
    pih.business_date = ?
";
$params = [];
$types = "";
$params[] = $selectedDate;
$types .= "s";
if (!empty($selectedStore)) {
    $sql .= " AND pih.location_id = ? ";
    $params[] = $selectedStore;
    $types .= "i";
}
$sql .= "
GROUP BY
    pih.inventory_header_id
ORDER BY
    l.location_name ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Throw Away Verification";
$breadcrumbs = [
    [
        'title' => 'Throw Away Verification'
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
                <h5 class="mb-0">Throw Away Verification</h5>
                <small class="text-muted">Verify Store Throw Away before generating Product Variance.</small>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Business Date</label>
                            <input type="date"  name="date" class="form-control" value="<?= $selectedDate; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Store</label>
                            <select name="store" class="form-control">
                                <option value="">All Stores</option>
                                <?php while($store = $stores->fetch_assoc()) : ?>
                                    <option value="<?= $store['location_id']; ?>" <?= $selectedStore == $store['location_id'] ? 'selected' : ''; ?>><?= htmlspecialchars($store['location_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100"><i class="bi bi-search"></i>&nbsp;Filter</button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table id="throwAwayTable" class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Store</th>
                                <th class="text-end">Products</th>
                                <th class="text-end">Store TA</th>
                                <th class="text-end">Verified TA</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0) : ?>
                                <?php while($row = $result->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= date('M d, Y',strtotime($row['business_date'])); ?></td>
                                        <td><?= htmlspecialchars($row['location_name']); ?></td>
                                        <td class="text-end"><?= number_format($row['total_products']); ?></td>
                                        <td class="text-end"><?= number_format($row['total_store_qty'],2); ?></td>
                                        <td class="text-end"><?= number_format($row['total_accounting_qty'],2); ?></td>
                                        <td>
                                            <?php 
                                                $statusClass='warning';
                                                if($row['status']=='verified'){
                                                    $statusClass='success';
                                                }
                                            ?>
                                            <span class="badge bg-<?= $statusClass; ?>"><?= ucfirst($row['status']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="view.php?id=<?= $row['inventory_header_id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-eye-fill"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No throw away records found.</td>
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