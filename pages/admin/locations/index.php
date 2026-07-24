<?php

/*
|--------------------------------------------------------------------------
| Location Management
|--------------------------------------------------------------------------
| Administrator - Locations List
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

$pageTitle = "Location Management";

$breadcrumbs = [

    [
        'title' => 'Location Management'
    ]

];

/*
|--------------------------------------------------------------------------
| Load Locations
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT *

    FROM locations

    ORDER BY location_id ASC

";

$result = mysqli_query($conn, $sql);

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

                    <h5 class="mb-0">Location Management</h5>
                    <small class="text-muted">Manage all company locations</small>

                </div>

                <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i>&nbsp;Add Location</a>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table id="locationsTable" class="table table-hover table-bordered align-middle">

                        <thead class="table-light">

                            <tr>
                                <th width="60">#</th>
                                <th>Location Code</th>
                                <th>Location Name</th>
                                <th>Area</th>
                                <th>Manager</th>
                                <th width="140">Type</th>
                                <th width="120">Status</th>
                                <th width="220">Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php
                                if (mysqli_num_rows($result) > 0):
                                    $i = 1;
                                    while ($row = mysqli_fetch_assoc($result)):
                                        $type = '';
                                        switch ($row['location_type']) {
                                            case 'office':
                                                $type = '<span class="badge bg-primary">Office</span>';
                                                break;
                                            case 'warehouse':
                                                $type = '<span class="badge bg-warning text-dark">Warehouse</span>';
                                                break;
                                            case 'store':
                                                $type = '<span class="badge bg-success">Store</span>';
                                                break;
                                            default:
                                                $type = '<span class="badge bg-secondary">Unknown</span>';
                                        }

                            ?>

                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($row['location_code']); ?></td>
                                <td><?= htmlspecialchars($row['location_name']); ?></td>
                                <td><?= htmlspecialchars($row['area']); ?></td>
                                <td><?= htmlspecialchars($row['manager']); ?></td>
                                <td><?= $type; ?></td>
                                <td>
                                    <?php if ($row['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <!-- View -->
                                    <a href="view.php?id=<?= $row['location_id']; ?>" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye-fill"></i></a>
                                    <!-- Edit -->
                                    <a href="edit.php?id=<?= $row['location_id']; ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                    <?php if ($row['status'] == 'active'): ?>
                                        <!-- Deactivate -->
                                        <a href="delete.php?id=<?= $row['location_id']; ?>" class="btn btn-sm btn-danger" title="Deactivate" onclick="return confirm('Deactivate this location?');"><i class="bi bi-x-circle-fill"></i></a>
                                    <?php else: ?>
                                        <!-- Activate -->
                                        <a href="delete.php?id=<?= $row['location_id']; ?>" class="btn btn-sm btn-success" title="Activate" onclick="return confirm('Activate this location?');"><i class="bi bi-check-circle-fill"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                                <?php
                                    endwhile;
                                else:

                                ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No locations found.</td>
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