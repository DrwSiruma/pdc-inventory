<?php

/*
|--------------------------------------------------------------------------
| Store Delivery Validation
|--------------------------------------------------------------------------
| Validate Deliveries Received From Warehouse / Production
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
| Logged-in User
|--------------------------------------------------------------------------
*/

$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT
        u.location_id,
        u.full_name,
        l.location_name
    FROM users u
    INNER JOIN locations l
        ON l.location_id = u.location_id
    WHERE u.user_id = ?
    LIMIT 1
");
if(!$stmt){
    die($conn->error);
}
$stmt->bind_param("i",$userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$locationId   = (int)$user['location_id'];
$locationName = $user['location_name'];

/*
|--------------------------------------------------------------------------
| Current Business Date
|--------------------------------------------------------------------------
*/

$businessDate = date('Y-m-d');

/*
|--------------------------------------------------------------------------
| Load Today's Deliveries
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
    h.delivery_header_id,
    h.delivery_no,
    h.reference_no,
    h.business_date,
    h.delivery_status,
    COUNT(l.delivery_id) total_products,
    SUM(l.expected_qty) expected_total,
    SUM(l.actual_qty) actual_total,
    SUM(l.short_qty) short_total
FROM product_delivery_header h
LEFT JOIN product_delivery_logs l
       ON l.delivery_header_id = h.delivery_header_id
WHERE
    h.location_id = ?
AND h.business_date = ?
GROUP BY
    h.delivery_header_id
ORDER BY
    h.delivery_no ASC
");
if(!$stmt){
    die($conn->error);
}
$stmt->bind_param(
    "is",
    $locationId,
    $businessDate
);
$stmt->execute();
$result = $stmt->get_result();
/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalDeliveries = 0;
$pendingDeliveries = 0;
$validatedDeliveries = 0;
$deliveryRows = [];
while($row = $result->fetch_assoc()){
    $deliveryRows[] = $row;
    $totalDeliveries++;
    if($row['delivery_status'] == 'pending'){
        $pendingDeliveries++;
    }
    if($row['delivery_status'] == 'validated'){
        $validatedDeliveries++;
    }
}
$stmt->close();

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Delivery Validation";
$breadcrumbs = [
    [
        'title' => 'Delivery Validation'
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
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="fw-bold mb-1"><?= htmlspecialchars($locationName); ?></h3>
                                <p class="text-muted mb-0">
                                    Delivery Validation
                                    <br>
                                    Business Date :
                                    <strong><?= date('F d, Y',strtotime($businessDate)); ?></strong>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <h5 class="mb-1"><?= number_format($totalDeliveries); ?>&nbsp;Deliveries</h5>
                                <small class="text-muted">Today's Delivery Transactions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-left-primary shadow">
                    <div class="card-body">
                        <div class="text-xs fw-bold text-primary text-uppercase">Total Deliveries</div>
                        <div class="display-6 fw-bold"><?= number_format($totalDeliveries); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-left-warning shadow">
                    <div class="card-body">
                        <div class="text-xs fw-bold text-warning text-uppercase">Pending</div>
                        <div class="display-6 fw-bold"><?= number_format($pendingDeliveries); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-left-success shadow">
                    <div class="card-body">
                        <div class="text-xs fw-bold text-success text-uppercase">Validated</div>
                        <div class="display-6 fw-bold"><?= number_format($validatedDeliveries); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Today's Deliveries</h5>
                    <small class="text-muted">Validate all deliveries received from Warehouse / Production.</small>
                </div>
                <div>
                    <a href="history.php" class="btn btn-outline-primary"><i class="bi bi-clock-history"></i>&nbsp;Delivery History</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="deliveryTable" class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="90">Delivery</th>
                                <th width="150">Reference No.</th>
                                <th>Business Date</th>
                                <th class="text-center">Products</th>
                                <th class="text-end">Expected</th>
                                <th class="text-end">Actual</th>
                                <th class="text-end">Short</th>
                                <th width="120">Status</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($deliveryRows)>0): ?>
                                <?php foreach($deliveryRows as $row): ?>
                                <?php
                                    switch($row['delivery_status']){
                                        case 'pending':
                                            $badge='warning';
                                        break;

                                        case 'validated':
                                            $badge='success';
                                        break;

                                        case 'posted':
                                            $badge='primary';
                                        break;

                                        default:
                                            $badge='secondary';
                                        break;
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <?php
                                            $deliveryLabel = [
                                                1 => '1st Delivery',
                                                2 => '2nd Delivery',
                                                3 => '3rd Delivery',
                                                4 => '4th Delivery',
                                                5 => '5th Delivery'
                                            ];
                                            echo $deliveryLabel[$row['delivery_no']] ?? ($row['delivery_no'].'th Delivery');
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['reference_no']); ?></td>
                                    <td>
                                        <?= date(
                                            'M d, Y',
                                            strtotime($row['business_date'])
                                        ); ?>
                                    </td>
                                    <td class="text-center"><?= number_format($row['total_products']); ?></td>
                                    <td class="text-end">
                                        <?= number_format(
                                            $row['expected_total'],
                                            2
                                        ); ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            $row['actual_total'],
                                            2
                                        ); ?>
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        <?= number_format(
                                            $row['short_total'],
                                            2
                                        ); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $badge ?>">
                                            <?= ucfirst(
                                                $row['delivery_status']
                                            ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if(
                                            $row['delivery_status']=='pending'
                                        ): ?>
                                            <a href="view.php?id=<?= $row['delivery_header_id']; ?>" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i>&nbsp;Validate</a>
                                        <?php else: ?>
                                            <a href="view.php?id=<?= $row['delivery_header_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-eye-fill"></i>&nbsp;View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">
                                        <div class="text-center py-5">
                                            <i class="bi bi-truck fs-1 text-muted"></i>
                                            <h5 class="mt-3">No deliveries available.</h5>
                                            <p class="text-muted mb-0">
                                                Waiting for Warehouse / Production
                                                to post today's deliveries.
                                            </p>
                                        </div>
                                    </td>
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