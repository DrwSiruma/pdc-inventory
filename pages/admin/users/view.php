<?php

/*
|--------------------------------------------------------------------------
| View User
|--------------------------------------------------------------------------
| Administrator - View User Information
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

    setFlash('danger', 'Invalid user selected.');

    header('Location: index.php');

    exit;

}

$user_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load User
|--------------------------------------------------------------------------
*/

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

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    setFlash('danger', 'User not found.');

    header('Location: index.php');

    exit;

}

$userInfo = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Page Details
|--------------------------------------------------------------------------
*/

$pageTitle = "View User";

$breadcrumbs = [

    [
        'title' => 'User Management',
        'link'  => 'index.php'
    ],

    [
        'title' => 'View User'
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

                <h5 class="mb-0">

                    User Information

                </h5>

                <small class="text-muted">

                    View complete user information

                </small>

            </div>

            <div>

                <?php if ($userInfo['user_id'] != 1): ?>

                    <a href="edit.php?id=<?= $userInfo['user_id']; ?>"
                       class="btn btn-warning">

                        <i class="bi bi-pencil-fill"></i>

                        Edit

                    </a>

                <?php endif; ?>

                <a href="index.php"
                   class="btn btn-secondary">

                    <i class="bi bi-arrow-left"></i>

                    Back

                </a>

            </div>

        </div>

        <div class="card-body">

            <table class="table table-bordered align-middle">

                <tr>

                    <th width="220">

                        Employee No

                    </th>

                    <td>

                        <?= htmlspecialchars($userInfo['employee_no']); ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Full Name

                    </th>

                    <td>

                        <?= htmlspecialchars($userInfo['full_name']); ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Username

                    </th>

                    <td>

                        <?= htmlspecialchars($userInfo['username']); ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Email Address

                    </th>

                    <td>

                        <?= htmlspecialchars($userInfo['email']); ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Contact Number

                    </th>

                    <td>

                        <?= !empty($userInfo['contact_no'])
                            ? htmlspecialchars($userInfo['contact_no'])
                            : '<span class="text-muted">N/A</span>'; ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Role

                    </th>

                    <td>

                        <?= ucwords(str_replace('_',' ', $userInfo['role'])); ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Assigned Location

                    </th>

                    <td>

                        <?= !empty($userInfo['location_name'])
                            ? htmlspecialchars($userInfo['location_name'])
                            : '<span class="text-muted">Not Assigned</span>'; ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Status

                    </th>

                    <td>

                        <?php if ($userInfo['status'] == 'active'): ?>

                            <span class="badge bg-success">

                                Active

                            </span>

                        <?php else: ?>

                            <span class="badge bg-danger">

                                Inactive

                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Date Created

                    </th>

                    <td>

                        <?= date(
                            'F d, Y h:i A',
                            strtotime($userInfo['created_at'])
                        ); ?>

                    </td>

                </tr>

                <tr>

                    <th>

                        Last Login

                    </th>

                    <td>

                        <?php

                        if (!empty($userInfo['last_login'])) {

                            echo date(
                                'F d, Y h:i A',
                                strtotime($userInfo['last_login'])
                            );

                        } else {

                            echo '<span class="text-muted">Never Logged In</span>';

                        }

                        ?>

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