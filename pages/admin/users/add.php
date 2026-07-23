<?php

/*
|--------------------------------------------------------------------------
| Add User
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

$pageTitle = "Add User";

$breadcrumbs = [

    [
        'title' => 'User Management',
        'link'  => 'index.php'
    ],

    [
        'title' => 'Add User'
    ]

];

/*
|--------------------------------------------------------------------------
| Load Locations
|--------------------------------------------------------------------------
*/

$locations = mysqli_query($conn,"
    SELECT location_id, location_name
    FROM locations
    ORDER BY location_name ASC
");

include '../../../includes/layout/header.php';
include '../../../includes/layout/sidebar.php';
include '../../../includes/layout/topbar.php';
include '../../../includes/layout/breadcrumb.php';

?>

<div class="main-content">

    <div class="container-fluid">

        <?php showFlash(); ?>

        <form action="save.php" method="POST">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">Add New User</h5>

                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Back</a>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Employee No</label>

                            <input type="text" name="employee_no" class="form-control" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Full Name</label>

                            <input type="text" name="full_name" class="form-control" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Username</label>

                            <input type="text" name="username" class="form-control" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Email</label>

                            <input type="email" name="email" class="form-control">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Contact Number</label>

                            <input type="text" name="contact_no" class="form-control">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Role</label>

                            <select name="role" class="form-select" required>

                                <option value="">Select Role</option>
                                <option value="super_admin">Admin</option>
                                <option value="accounting">Accounting</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="store">Store</option>
                                <option value="spectator">Spectator</option>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Location</label>

                            <select name="location_id" class="form-select">

                                <option value="">Select Location</option>

                                <?php while($loc=mysqli_fetch_assoc($locations)): ?>

                                <option value="<?= $loc['location_id']; ?>">
                                    <?= htmlspecialchars($loc['location_name']); ?>
                                </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Status</label>

                            <select name="status" class="form-select">

                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Password</label>

                            <input type="password" name="password" class="form-control" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Confirm Password</label>

                            <input type="password" name="confirm_password" class="form-control" required>

                        </div>

                    </div>

                </div>

                <div class="card-footer text-end">

                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle-fill"></i>&nbsp;Save User</button>

                </div>

            </div>

        </form>

    </div>

</div>

<?php include '../../../includes/layout/footer.php'; ?>