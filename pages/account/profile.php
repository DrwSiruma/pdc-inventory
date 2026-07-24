<?php

/*
|--------------------------------------------------------------------------
| My Profile
|--------------------------------------------------------------------------
*/

require_once '../../includes/config.php';
require_once '../../includes/connection.php';
require_once '../../includes/session.php';
require_once '../../includes/no_cache.php';
require_once '../../includes/functions.php';
require_once '../../includes/flash.php';
require_once '../../includes/auth.php';

requireLogin();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("

    SELECT
        u.*,
        l.location_name

    FROM users u

    LEFT JOIN locations l

        ON l.location_id = u.location_id

    WHERE u.user_id = ?

");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$user_details = $stmt->get_result()->fetch_assoc();

$stmt->close();

$pageTitle = "My Profile";

$breadcrumbs = [

    [
        'title' => 'My Profile'
    ]

];

include '../../includes/layout/header.php';
include '../../includes/layout/sidebar.php';
include '../../includes/layout/topbar.php';
include '../../includes/layout/breadcrumb.php';

?>
<div class="main-content">

    <div class="container-fluid">

        <?php showFlash(); ?>

        <div class="card shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0">My Profile</h5>

                <small class="text-muted">Update your personal information</small>

            </div>

            <form method="POST" action="update_profile.php">

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Employee No</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user_details['employee_no']); ?>" readonly>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user_details['username']); ?>" readonly>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user_details['full_name']); ?>" required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_details['email']); ?>">

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_no" class="form-control" value="<?= htmlspecialchars($user_details['contact_no']); ?>">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?= ucwords(str_replace('_', ' ', $user_details['role'])); ?>" readonly>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Assigned Location</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user_details['location_name'] ?? '-'); ?>" readonly>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="<?= ucfirst($user_details['status']); ?>" readonly>

                        </div>

                    </div>

                </div>

                <div class="card-footer bg-white">

                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle-fill"></i>&nbsp;Update Profile</button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php

include '../../includes/layout/footer.php';

?>