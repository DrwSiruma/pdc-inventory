<?php

/*
|--------------------------------------------------------------------------
| View Location
|--------------------------------------------------------------------------
| Administrator - View Location Information
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
requireRole('super_admin');

/*
|--------------------------------------------------------------------------
| Validate ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlash(
        'danger',
        'Invalid location selected.'
    );
    header('Location: index.php');
    exit;
}
$location_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Location
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("SELECT * FROM locations WHERE location_id = ? LIMIT 1");
$stmt->bind_param("i",$location_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    setFlash('danger','Location not found.');
    header('Location: index.php');
    exit;
}

$location = $result->fetch_assoc();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "View Location";
$breadcrumbs = [
    [
        'title' => 'Location Management',
        'link'  => 'index.php'
    ],
    [
        'title' => 'View Location'
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
                    <h5 class="mb-0">Location Information</h5>
                    <small class="text-muted">View location details</small>
                </div>
                <div>
                    <a href="edit.php?id=<?= $location['location_id']; ?>" class="btn btn-warning"><i class="bi bi-pencil-fill"></i>&nbsp;Edit</a>
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Back</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="220">Location Code</th>
                        <td><?= htmlspecialchars($location['location_code']); ?></td>
                    </tr>
                    <tr>
                        <th width="220">Location Name</th>
                        <td><?= htmlspecialchars($location['location_name']); ?></td>
                    </tr>
                    <tr>
                        <th width="220">Area</th>
                        <td><?= htmlspecialchars($location['area']); ?></td>
                    </tr>
                    <tr>
                        <th width="220">Manager</th>
                        <td><?= htmlspecialchars($location['manager']); ?></td>
                    </tr>
                    <tr>
                        <th width="220">Address</th>
                        <td><?= nl2br(htmlspecialchars($location['address'])); ?></td>
                    </tr>
                    <tr>
                        <th width="220">Location Type</th>
                        <td>
                            <?php
                            switch ($location['location_type']) {
                                case 'office':
                                    echo '<span class="badge bg-primary">Office</span>';
                                    break;
                                case 'warehouse':
                                    echo '<span class="badge bg-warning text-dark">Warehouse</span>';
                                    break;
                                case 'store':
                                    echo '<span class="badge bg-success">Store</span>';
                                    break;
                                default:
                                    echo '<span class="badge bg-secondary">Unknown</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th width="220">Status</th>
                        <td>
                            <?php if ($location['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
    include '../../../includes/layout/footer.php';
?>