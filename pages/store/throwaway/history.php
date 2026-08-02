<?php

/*
|--------------------------------------------------------------------------
| Product Throw Away History
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
    WHERE
        u.user_id = ?
    LIMIT 1
");
if (!$stmt) {
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
$locationId = (int) $user['location_id'];
$locationName = $user['location_name'];

/*
|--------------------------------------------------------------------------
| Load Throw Away History
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        t.throwaway_id,
        h.business_date,
        p.product_code,
        p.product_name,
        t.store_qty,
        t.accounting_qty,
        t.status,
        t.remarks
    FROM product_throwaway t
    INNER JOIN product_inventory_header h
        ON h.inventory_header_id = t.inventory_header_id
    INNER JOIN products p
        ON p.product_id = t.product_id
    WHERE
        h.location_id = ?
    ORDER BY
        h.business_date DESC,
        p.product_name ASC
");
if (!$stmt) {
    die($conn->error);
}
$stmt->bind_param(
    "i",
    $locationId
);
$stmt->execute();
$history = $stmt->get_result();
$stmt->close();
/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/
$pageTitle = "Throw Away History";
$breadcrumbs = [
    [
        'title' => 'Product Throw Away',
        'link'  => 'index.php'
    ],
    [
        'title' => 'History'
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
                    <h5 class="mb-0">Throw Away History</h5>
                    <small class="text-muted"><?= htmlspecialchars($locationName); ?></small>
                </div>
                <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i>&nbsp;Back</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="throwAwayHistoryTable">
                        <thead>
                            <tr>
                                <th>Business Date</th>
                                <th>Product</th>
                                <th class="text-end">Store Qty</th>
                                <th class="text-end">Accounting Qty</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $history->fetch_assoc()) : ?>
                                <?php
                                    switch ($row['status']) {
                                        case 'pending':
                                            $badge = 'warning';
                                            break;
                                        case 'verified':
                                            $badge = 'success';
                                            break;
                                        case 'rejected':
                                            $badge = 'danger';
                                            break;
                                        default:
                                            $badge = 'secondary';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <?= date(
                                            'F d, Y',
                                            strtotime($row['business_date'])
                                        ); ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['product_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($row['product_code']); ?></small>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            $row['store_qty'],
                                            2
                                        ); ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($row['accounting_qty'] !== null) : ?>
                                            <?= number_format(
                                                $row['accounting_qty'],
                                                2
                                            ); ?>
                                        <?php else : ?>
                                            <span class="text-muted">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $badge; ?>"><?= ucfirst($row['status']); ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['remarks']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($history->num_rows == 0) : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No throw away history found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../../../includes/layout/footer.php'; ?>