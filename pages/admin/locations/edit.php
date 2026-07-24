<?php

/*
|--------------------------------------------------------------------------
| Edit Location
|--------------------------------------------------------------------------
| Administrator - Edit Location
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
| Validate Location ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlash('danger', 'Invalid location selected.');
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
$stmt->bind_param("i", $location_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    setFlash('danger', 'Location not found.');
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

$pageTitle = "Edit Location";
$breadcrumbs = [
    [
        'title' => 'Location Management',
        'link'  => 'index.php'
    ],
    [
        'title' => 'Edit Location'
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

            <form method="POST" action="update.php">
                <input type="hidden" name="location_id" value="<?= $location['location_id']; ?>">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Location</h5>
                    <small class="text-muted">Update the location information below.</small>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Location Code<span class="text-danger">*</span></label>
                            <input type="text" name="location_code" class="form-control" maxlength="20" value="<?= htmlspecialchars($location['location_code']); ?>" required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Location Name<span class="text-danger">*</span></label>
                            <input type="text" name="location_name" class="form-control" maxlength="100" value="<?= htmlspecialchars($location['location_name']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Area</label>
                            <input type="text" name="area" class="form-control" maxlength="100" value="<?= htmlspecialchars($location['area']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Manager</label>
                            <input type="text" name="manager" class="form-control" maxlength="100" value="<?= htmlspecialchars($location['manager']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($location['address']); ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location Type<span class="text-danger">*</span></label>
                            <select name="location_type" class="form-select" required>
                                <option value="office" <?= ($location['location_type'] == 'office') ? 'selected' : ''; ?>>Office</option>
                                <option value="warehouse" <?= ($location['location_type'] == 'warehouse') ? 'selected' : ''; ?>>Warehouse</option>
                                <option value="store" <?= ($location['location_type'] == 'store') ? 'selected' : ''; ?>>Store</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($location['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?= ($location['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i>&nbsp;Update Location</button>
                    <button type="reset" class="btn btn-warning"><i class="bi bi-arrow-clockwise"></i>&nbsp;Reset</button>
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle-fill"></i>&nbsp;Back to List</a>
                </div>
            </form>

        </div>
    </div>
</div>

<?php
include '../../../includes/layout/footer.php';
?>