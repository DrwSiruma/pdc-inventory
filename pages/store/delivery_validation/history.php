<?php

/*
|--------------------------------------------------------------------------
| Delivery Validation History
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
    WHERE u.user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die($conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$locationId = (int) $user['location_id'];

/*
|--------------------------------------------------------------------------
| Load Delivery History
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        h.delivery_header_id,
        h.business_date,
        h.delivery_no,
        h.reference_no,
        h.delivery_status,
        h.validated_at,
        validator.full_name AS validated_by,
        COUNT(l.delivery_id) AS total_products,
        SUM(l.expected_qty) AS expected_total,
        SUM(l.actual_qty) AS actual_total,
        SUM(l.short_qty) AS short_total
    FROM product_delivery_header h
    LEFT JOIN product_delivery_logs l
        ON l.delivery_header_id = h.delivery_header_id
    LEFT JOIN users validator
        ON validator.user_id = h.validated_by
    WHERE
        h.location_id = ?
    GROUP BY
        h.delivery_header_id
    ORDER BY
        h.business_date DESC,
        h.delivery_no DESC
");
if (!$stmt) {
    die($conn->error);
}
$stmt->bind_param("i", $locationId);
$stmt->execute();
$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Delivery History";
$breadcrumbs = [
    [
        'title' => 'Delivery Validation',
        'link'  => 'index.php'
    ],
    [
        'title' => 'Delivery History'
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
                    <h5 class="mb-0">Delivery Validation History</h5>
                    <small class="text-muted">Previously validated deliveries.</small>
                </div>
                <div>
                    <a href="index.php" class="btn btn-primary"><i class="bi bi-arrow-left-circle"></i>&nbsp;Back to Today's Deliveries</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="historyTable" class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Business Date</th>
                                <th>Delivery</th>
                                <th>Reference No.</th>
                                <th class="text-center">Products</th>
                                <th class="text-end">Expected</th>
                                <th class="text-end">Actual</th>
                                <th class="text-end">Short</th>
                                <th>Status</th>
                                <th>Validated By</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0) : ?>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <?php
                                        switch ($row['delivery_status']) {
                                            case 'pending':
                                                $badge = 'warning';
                                                break;
                                            case 'validated':
                                                $badge = 'success';
                                                break;
                                            case 'posted':
                                                $badge = 'primary';
                                                break;
                                            default:
                                                $badge = 'secondary';
                                                break;
                                        }
                                        $deliveryLabel = [
                                            1 => '1st Delivery',
                                            2 => '2nd Delivery',
                                            3 => '3rd Delivery',
                                            4 => '4th Delivery',
                                            5 => '5th Delivery'
                                        ];
                                    ?>
                                    <tr>
                                        <td>
                                            <?= date(
                                                'M d, Y',
                                                strtotime($row['business_date'])
                                            ); ?>
                                        </td>
                                        <td>
                                            <?= $deliveryLabel[$row['delivery_no']] ?? $row['delivery_no'] . 'th Delivery'; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['reference_no']); ?></td>
                                        <td class="text-center"><?= number_format($row['total_products']); ?></td>
                                        <td class="text-end"><?= number_format($row['expected_total'],2); ?></td>
                                        <td class="text-end"><?= number_format($row['actual_total'],2); ?></td>
                                        <td class="text-end fw-bold text-danger"><?= number_format($row['short_total'],2); ?></td>
                                        <td><span class="badge bg-<?= $badge; ?>"><?= ucfirst($row['delivery_status']); ?></span></td>
                                        <td>
                                            <?= $row['validated_by'] ?? '-'; ?>
                                            <?php if (!empty($row['validated_at'])) : ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?= date(
                                                        'M d, Y h:i A',
                                                        strtotime($row['validated_at'])
                                                    ); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><a href="view.php?id=<?= $row['delivery_header_id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye-fill"></i>&nbsp;View</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="10">
                                        <div class="text-center py-5">
                                            <i class="bi bi-clock-history fs-1 text-muted"></i>
                                            <h5 class="mt-3">No delivery history found.</h5>
                                            <p class="text-muted mb-0">Validated deliveries will appear here.</p>
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