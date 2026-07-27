<?php

/*
|--------------------------------------------------------------------------
| Assign Stores to Accounting Staff
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
| Validate User
|--------------------------------------------------------------------------
*/

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) {
    setFlash('danger', 'Invalid accounting user.');
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Load Accounting User
|--------------------------------------------------------------------------
*/

$userStmt = $conn->prepare("
    SELECT
        user_id,
        employee_no,
        full_name,
        username,
        status
    FROM users
    WHERE
        user_id = ?
        AND role = 'accounting'
");

$userStmt->bind_param("i", $userId);
$userStmt->execute();
$users = $userStmt->get_result()->fetch_assoc();
$userStmt->close();
if (!$users) {
    setFlash('danger', 'Accounting user not found.');
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Load All Active Stores
|--------------------------------------------------------------------------
*/

$stores = [];
$result = $conn->query("
    SELECT
        location_id,
        location_name,
        location_code
    FROM locations
    WHERE
        location_type='store'
        AND status='active'
    ORDER BY
        location_name
");
while ($row = $result->fetch_assoc()) {
    $stores[] = $row;
}

/*
|--------------------------------------------------------------------------
| Load Assigned Stores
|--------------------------------------------------------------------------
*/

$assigned = [];
$stmt = $conn->prepare("
    SELECT
        location_id
    FROM accounting_store_assignment
    WHERE
        user_id=?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $assigned[] = $row['location_id'];
}
$stmt->close();

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Store Assignment";
$breadcrumbs = [
    ['title' => 'Accounting', 'link' => 'index.php'],
    ['title' => 'Store Assignment']
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
            <div class="card-header bg-white">
                <h4 class="mb-0">Assign Stores</h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <strong>Employee No.</strong><br>
                        <?= htmlspecialchars($users['employee_no']); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Full Name</strong><br>
                        <?= htmlspecialchars($users['full_name']); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Username</strong><br>
                        <?= htmlspecialchars($users['username']); ?>
                    </div>
                </div>
                <form action="save_assignment.php" method="POST">
                    <input type="hidden" name="user_id" value="<?= $userId; ?>">
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" id="checkAll" class="form-check-input">
                            <label class="form-check-label fw-bold" for="checkAll">Select All Stores</label>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach($stores as $store): ?>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input store-checkbox" type="checkbox" name="stores[]" value="<?= $store['location_id']; ?>" id="store<?= $store['location_id']; ?>" <?= in_array($store['location_id'],$assigned) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="store<?= $store['location_id']; ?>">
                                        <strong><?= htmlspecialchars($store['location_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($store['location_code']); ?></small>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <button class="btn btn-success" type="submit"><i class="bi bi-check-circle-fill"></i>&nbsp;Save Assignment</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../includes/layout/footer.php'; ?>