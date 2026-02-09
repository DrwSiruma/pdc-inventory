<?php
require 'auth_check.php';
include 'header.php';
include 'sidebar.php';
?>

<div class="content p-4 w-100">

    <!-- Top Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn btn-outline-secondary d-md-none" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>
        <h3>Dashboard</h3>
        <div>
            Welcome, <strong><?php echo $_SESSION['fullname']; ?></strong>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Total Outlets</h6>
                    <h3>35</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Low Stock Items</h6>
                    <h3 class="text-danger">18</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Near Expiry</h6>
                    <h3 class="text-warning">6</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Today Transfers</h6>
                    <h3>12</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables -->
    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    Inventory Alerts
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Outlet</th>
                                <th>Item</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Dunkin East</td>
                                <td>Cups</td>
                                <td>10</td>
                                <td class="text-danger">Low</td>
                            </tr>
                            <tr>
                                <td>Dunkin West</td>
                                <td>Donuts</td>
                                <td>5</td>
                                <td class="text-warning">Near Expiry</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">
                    Recent Audit Logs
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Module</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>admin</td>
                                <td>LOGIN</td>
                                <td>Authentication</td>
                            </tr>
                            <tr>
                                <td>admin</td>
                                <td>CREATE</td>
                                <td>Inventory</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>