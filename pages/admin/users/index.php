<?php

/*
|--------------------------------------------------------------------------
| User Management
|--------------------------------------------------------------------------
| Administrator - Users List
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

$pageTitle = "User Management";

$breadcrumbs = [

    [
        'title' => 'User Management'
    ]

];

include '../../../includes/layout/header.php';
include '../../../includes/layout/sidebar.php';
include '../../../includes/layout/topbar.php';
include '../../../includes/layout/breadcrumb.php';

/*
|--------------------------------------------------------------------------
| Load Users
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$sql = "

SELECT
    u.*,
    l.location_name

FROM users u

LEFT JOIN locations l
    ON l.location_id = u.location_id

";

if($search != ''){

    $search = mysqli_real_escape_string($conn,$search);

    $sql .= "

    WHERE

        u.employee_no LIKE '%{$search}%'

        OR u.full_name LIKE '%{$search}%'

        OR u.username LIKE '%{$search}%'

        OR u.email LIKE '%{$search}%'

        OR u.role LIKE '%{$search}%'

        OR l.location_name LIKE '%{$search}%'

    ";

}

$sql .= " ORDER BY u.user_id ASC";

$result = mysqli_query($conn,$sql);
$totalUsers = mysqli_num_rows($result);

?>

<div class="main-content">

<div class="container-fluid">

    <?php showFlash(); ?>

    <div class="card shadow-sm">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <div>

                <h5 class="mb-0">

                    User Management

                </h5>

                <small class="text-muted">

                    Total Users :
                    <strong><?= number_format($totalUsers); ?></strong>

                </small>

            </div>

            <a href="add.php" class="btn btn-primary">

                <i class="bi bi-person-plus-fill"></i>

                Add User

            </a>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table id="usersTable" class="table table-hover table-bordered align-middle">

                    <thead class="table-light">

                    <tr>

                        <th width="60">#</th>

                        <th>Employee No</th>

                        <th>Full Name</th>

                        <th>Username</th>

                        <th>Email</th>

                        <th>Role</th>

                        <th>Location</th>

                        <th>Status</th>

                        <th width="220">Actions</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php

                    if(mysqli_num_rows($result) > 0):

                        $i = 1;

                        while($row = mysqli_fetch_assoc($result)):

                    ?>

                    <tr>

                        <td><?= $i++; ?></td>

                        <td><?= htmlspecialchars($row['employee_no']); ?></td>

                        <td><?= htmlspecialchars($row['full_name']); ?></td>

                        <td><?= htmlspecialchars($row['username']); ?></td>

                        <td><?= htmlspecialchars($row['email']); ?></td>

                        <td>

                            <?php

                            $roleColor = [

                                'super_admin' => 'danger',

                                'accounting' => 'success',

                                'warehouse' => 'warning',

                                'store' => 'primary',

                                'spectator' => 'secondary'

                            ];

                            ?>

                            <span style="color:white;" class="badge bg-<?= $roleColor[$row['role']] ?? 'light'; ?>">

                                <?= ucwords(str_replace('_',' ', $row['role'])); ?>

                            </span>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['location_name'] ?? '-'); ?>

                        </td>

                        <td>

                            <?php if($row['status']=="active"): ?>

                                <span class="badge rounded-pill bg-success px-3" style="color:white;">

                                    Active

                                </span>

                            <?php else: ?>

                                <span class="badge rounded-pill bg-danger px-3" style="color:white;">

                                    Inactive

                                </span>

                            <?php endif; ?>

                        </td>

                        <td class="text-center">

                            <!-- View -->
                            <a href="view.php?id=<?= $row['user_id']; ?>"
                            class="btn btn-sm btn-info"
                            title="View">

                                <i class="bi bi-eye-fill"></i>

                            </a>

                            <?php if ($row['user_id'] == 1): ?>

                                <button
                                    class="btn btn-sm btn-secondary"
                                    disabled
                                    title="Primary Administrator">

                                    <i class="bi bi-shield-lock-fill"></i>

                                </button>

                            <?php else: ?>

                                <a href="edit.php?id=<?= $row['user_id']; ?>"
                                class="btn btn-sm btn-warning"
                                title="Edit">

                                    <i class="bi bi-pencil-fill"></i>

                                </a>

                                <?php if ($row['user_id'] != $_SESSION['user_id']): ?>

                                    <?php if ($row['status'] == 'active'): ?>

                                        <a href="delete.php?id=<?= $row['user_id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        title="Deactivate User"
                                        onclick="return confirm('Deactivate this user?');">

                                            <i class="bi bi-person-x-fill"></i>

                                        </a>

                                    <?php else: ?>

                                        <a href="delete.php?id=<?= $row['user_id']; ?>"
                                        class="btn btn-sm btn-success"
                                        title="Activate User"
                                        onclick="return confirm('Activate this user?');">

                                            <i class="bi bi-person-check-fill"></i>

                                        </a>

                                    <?php endif; ?>

                                <?php endif; ?>

                            <?php endif; ?>

                        </td>

                    </tr>

                    <?php

                        endwhile;

                    else:

                    ?>

                    <tr>

                        <td colspan="9" class="text-center text-muted">

                            No users found.

                        </td>

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