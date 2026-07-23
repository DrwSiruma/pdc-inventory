<?php

/*
|--------------------------------------------------------------------------
| Edit User
|--------------------------------------------------------------------------
| Administrator - Edit Existing User
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
| Validate User ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    setFlash('danger', 'Invalid user selected.');

    header('Location: index.php');

    exit;

}

$user_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load User Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    SELECT *

    FROM users

    WHERE user_id = ?

");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    setFlash('danger', 'User not found.');

    header('Location: index.php');

    exit;

}

$userInfo = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Load Locations
|--------------------------------------------------------------------------
*/

$locations = mysqli_query($conn, "

    SELECT
        location_id,
        location_name

    FROM locations

    ORDER BY location_name ASC

");

/*
|--------------------------------------------------------------------------
| Page Information
|--------------------------------------------------------------------------
*/

$pageTitle = "Edit User";

$breadcrumbs = [

    [
        'title' => 'User Management',
        'link'  => 'index.php'
    ],

    [
        'title' => 'Edit User'
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

                    <h5 class="mb-0">Edit User</h5>
                    <small class="text-muted">Update user information</small>

                </div>

            </div>

            <div class="card-body">

                <form action="update.php" method="POST" autocomplete="off">

                    <input type="hidden" name="user_id" value="<?= $userInfo['user_id']; ?>">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Employee No <span class="text-danger">*</span></label>
                            <input type="text" name="employee_no" class="form-control" value="<?= htmlspecialchars($userInfo['employee_no']); ?>" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($userInfo['full_name']); ?>" required>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($userInfo['username']); ?>" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userInfo['email']); ?>">

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_no" class="form-control" value="<?= htmlspecialchars($userInfo['contact_no']); ?>">

                        </div>

                        <div class="col-md-6 mb-3">

                            <?php if ($userInfo['user_id'] == 1): ?>

                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?= ucwords(str_replace('_',' ', $userInfo['role'])); ?>" readonly>
                                <input type="hidden" name="role" value="<?= $userInfo['role']; ?>">

                            <?php else: ?>

                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="super_admin" <?= $userInfo['role']=='super_admin' ? 'selected' : ''; ?>>Administrator</option>
                                    <option value="accounting" <?= $userInfo['role']=='accounting' ? 'selected' : ''; ?>>Accounting</option>
                                    <option value="warehouse" <?= $userInfo['role']=='warehouse' ? 'selected' : ''; ?>>Warehouse</option>
                                    <option value="store" <?= $userInfo['role']=='store' ? 'selected' : ''; ?>>Store</option>
                                    <option value="spectator" <?= $userInfo['role']=='spectator' ? 'selected' : ''; ?>>Spectator</option>
                                </select>

                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <?php if ($userInfo['user_id'] == 1): ?>

                                <label class="form-label">Assigned Location</label>
                                <input type="text" class="form-control" value="Head Office" readonly>
                                <input type="hidden" name="location_id" value="<?= $userInfo['location_id']; ?>">

                            <?php else: ?>

                                <label class="form-label">Assigned Location</label>
                                <select name="location_id" class="form-select">

                                    <option value="">-- Select Location --</option>
                                    <?php while($location = mysqli_fetch_assoc($locations)): ?>

                                        <option value="<?= $location['location_id']; ?>" <?= $location['location_id']==$userInfo['location_id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($location['location_name']); ?>
                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            <?php endif; ?>

                        </div>

                        <div class="col-md-6 mb-3">

                            <?php if ($userInfo['user_id'] == 1): ?>

                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="Active" readonly>
                                <input type="hidden" name="status" value="active">

                            <?php else: ?>

                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $userInfo['status']=='active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?= $userInfo['status']=='inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>

                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control">
                        <small class="text-muted">Leave blank to keep the current password.</small>

                    </div>

                    <div class="d-flex justify-content-end">

                        <a href="index.php" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        &nbsp;&nbsp;
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save-fill"></i> Update User
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include '../../../includes/layout/footer.php'; ?>