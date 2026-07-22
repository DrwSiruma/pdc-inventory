<?php

/*
|--------------------------------------------------------------------------
| Administrator Dashboard
|--------------------------------------------------------------------------
| Dashboard Landing Page
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
requireRole('super_admin');

$pageTitle = "Dashboard";

$breadcrumbs = [

    [
        'title' => 'Dashboard'
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

        <div class="row">

            <div class="col-lg-12">

                <div class="card mb-4">

                    <div class="card-body">

                        <h3 class="mb-2">

                            Welcome back,
                            <strong><?= htmlspecialchars($_SESSION['fullname']); ?></strong>

                        </h3>

                        <p class="text-muted mb-0">

                            Panda Development Corporation Inventory Management System

                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- Statistics -->

        <div class="row">

            <div class="col-lg-3 col-md-6 mb-4">

                <div class="card">

                    <div class="card-body">

                        <h6 class="text-muted">

                            Total Users

                        </h6>

                        <h2>

                            0

                        </h2>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6 mb-4">

                <div class="card">

                    <div class="card-body">

                        <h6 class="text-muted">

                            Products

                        </h6>

                        <h2>

                            0

                        </h2>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6 mb-4">

                <div class="card">

                    <div class="card-body">

                        <h6 class="text-muted">

                            Locations

                        </h6>

                        <h2>

                            0

                        </h2>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6 mb-4">

                <div class="card">

                    <div class="card-body">

                        <h6 class="text-muted">

                            Reports

                        </h6>

                        <h2>

                            0

                        </h2>

                    </div>

                </div>

            </div>

        </div>

        <!-- User Information -->

        <div class="row">

            <div class="col-lg-6 mb-4">

                <div class="card">

                    <div class="card-header">

                        Logged-in User

                    </div>

                    <div class="card-body">

                        <table class="table table-borderless mb-0">

                            <tr>

                                <th width="170">

                                    Employee No

                                </th>

                                <td>

                                    <?= htmlspecialchars($_SESSION['employee_no']); ?>

                                </td>

                            </tr>

                            <tr>

                                <th>

                                    Full Name

                                </th>

                                <td>

                                    <?= htmlspecialchars($_SESSION['fullname']); ?>

                                </td>

                            </tr>

                            <tr>

                                <th>

                                    Username

                                </th>

                                <td>

                                    <?= htmlspecialchars($_SESSION['username']); ?>

                                </td>

                            </tr>

                            <tr>

                                <th>

                                    Role

                                </th>

                                <td>

                                    <?= ucwords(str_replace('_',' ',$_SESSION['role'])); ?>

                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

            </div>

            <div class="col-lg-6 mb-4">

                <div class="card">

                    <div class="card-header">

                        Quick Actions

                    </div>

                    <div class="card-body">

                        <a href="#" class="btn btn-primary mb-2">

                            <i class="bi bi-person-plus-fill"></i>

                            Create User

                        </a>

                        <br>

                        <a href="#" class="btn btn-success mb-2">

                            <i class="bi bi-box-seam"></i>

                            Add Product

                        </a>

                        <br>

                        <a href="#" class="btn btn-warning mb-2">

                            <i class="bi bi-geo-alt-fill"></i>

                            Manage Locations

                        </a>

                        <br>

                        <a href="#" class="btn btn-info">

                            <i class="bi bi-bar-chart-fill"></i>

                            View Reports

                        </a>

                    </div>

                </div>

            </div>

        </div>

        <!-- Recent Activities -->

        <div class="card">

            <div class="card-header">

                Recent Activities

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-striped">

                        <thead>

                            <tr>

                                <th>Date</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Module</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td colspan="4" class="text-center text-muted">

                                    No records available.

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<?php

include '../../includes/layout/footer.php';

?>