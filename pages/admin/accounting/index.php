<?php

/*
|--------------------------------------------------------------------------
| Accounting Administration
|--------------------------------------------------------------------------
| Manage Accounting Store Assignments
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
| Load Accounting Users
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
    u.user_id,
    u.employee_no,
    u.full_name,
    u.username,
    u.status,
    COUNT(a.location_id) AS assigned_stores
FROM users u
LEFT JOIN accounting_store_assignment a
    ON a.user_id = u.user_id
WHERE u.role = 'accounting'
GROUP BY
    u.user_id,
    u.employee_no,
    u.full_name,
    u.username,
    u.status
ORDER BY
    u.full_name ASC
";
$result = $conn->query($sql);

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Accounting Administration";
$breadcrumbs = [
    [
        'title' => 'Accounting'
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
        Accounting Users
        ========================================================== -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Accounting Staff</h5>
                        <small class="text-muted">Manage store assignments for Accounting personnel.</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="accountingTable" class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="120">Employee No.</th>
                                <th>Full Name</th>
                                <th width="180">Username</th>
                                <th width="150" class="text-center">Assigned Stores</th>
                                <th width="120" class="text-center">Status</th>
                                <th width="170" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['employee_no']); ?></td>
                                    <td><?= htmlspecialchars($row['full_name']); ?></td>
                                    <td><?= htmlspecialchars($row['username']); ?></td>
                                    <td class="text-center"><span class="badge bg-primary"><?= (int)$row['assigned_stores']; ?></span></td>
                                    <td class="text-center">
                                        <?php if($row['status'] == 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><a href="assignments.php?user_id=<?= $row['user_id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-diagram-3-fill"></i>&nbsp;Manage Stores</a></td>
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