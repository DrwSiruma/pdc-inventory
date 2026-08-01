<?php

/*
|--------------------------------------------------------------------------
| Product Inventory History
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
| Logged-in Store
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
| Load History
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        h.inventory_header_id,
        h.business_date,
        h.business_status,
        h.submitted_at,
        COUNT(d.inventory_detail_id) AS total_products
    FROM product_inventory_header h
    LEFT JOIN product_inventory_details d
        ON d.inventory_header_id =
           h.inventory_header_id
    WHERE
        h.location_id = ?
    GROUP BY
        h.inventory_header_id
    ORDER BY
        h.business_date DESC
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

$pageTitle = "Inventory History";
$breadcrumbs = [
    [
        'title' => 'Product Inventory',
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
                    <h5 class="mb-0">Inventory History</h5>
                    <small class="text-muted"><?= htmlspecialchars($locationName); ?></small>
                </div>
                <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i>&nbsp;Back</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="inventoryHistoryTable">
                        <thead>
                            <tr>
                                <th>Business Date</th>
                                <th>Status</th>
                                <th class="text-center">Products</th>
                                <th>Submitted At</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $history->fetch_assoc()) : ?>
                                <?php
                                    switch ($row['business_status']) {
                                        case 'draft':
                                            $badge = 'secondary';
                                            break;
                                        case 'submitted':
                                            $badge = 'warning';
                                            break;
                                        case 'verified':
                                            $badge = 'info';
                                            break;
                                        case 'generated':
                                            $badge = 'primary';
                                            break;
                                        case 'locked':
                                            $badge = 'dark';
                                            break;
                                        default:
                                            $badge = 'success';
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
                                    <td><span class="badge bg-<?= $badge; ?>"><?= ucfirst($row['business_status']); ?></span></td>
                                    <td class="text-center">
                                        <?= number_format(
                                            $row['total_products']
                                        ); ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['submitted_at'])) : ?>
                                            <?= date(
                                                'M d, Y h:i A',
                                                strtotime($row['submitted_at'])
                                            ); ?>
                                        <?php else : ?>
                                            <span class="text-muted">Not Submitted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><a href="view.php?id=<?= $row['inventory_header_id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye-fill"></i>&nbsp;View</a></td>
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
    include '../../../includes/layout/footer.php';
?>