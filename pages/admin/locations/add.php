<?php

/*
|--------------------------------------------------------------------------
| Add Location
|--------------------------------------------------------------------------
| Administrator - Add New Location
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

$pageTitle = "Add Location";

$breadcrumbs = [

    [
        'title' => 'Location Management',
        'link'  => 'index.php'
    ],

    [
        'title' => 'Add Location'
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
            <form method="POST" action="save.php">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Add New Location</h5>
                    <small class="text-muted">Enter the location information below.</small>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Location Code<span class="text-danger">*</span></label>
                            <input type="text" name="location_code" class="form-control" maxlength="20" required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Location Name<span class="text-danger">*</span></label>
                            <input type="text" name="location_name" class="form-control" maxlength="100" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Area</label>
                            <input type="text" name="area" class="form-control" maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Manager</label>
                            <input type="text" name="manager" class="form-control" maxlength="100">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Location Type<span class="text-danger">*</span></label>
                            <select name="location_type" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                <option value="office">Office</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="store">Store</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i>&nbsp;Save Location</button>
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