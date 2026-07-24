<?php

require_once '../../includes/config.php';
require_once '../../includes/connection.php';
require_once '../../includes/session.php';
require_once '../../includes/no_cache.php';
require_once '../../includes/functions.php';
require_once '../../includes/flash.php';
require_once '../../includes/auth.php';

requireLogin();

$pageTitle = "Change Password";

$breadcrumbs = [

    [
        'title' => 'Change Password'
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

<h5 class="mb-0">

Change Password

</h5>

<small class="text-muted">

Update your account password

</small>

</div>

<form method="POST" action="update_password.php">

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Current Password

</label>

<input
type="password"
name="current_password"
class="form-control"
required>

</div>

</div>

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

New Password

</label>

<input
type="password"
name="new_password"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Confirm Password

</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

</div>

</div>

<div class="card-footer bg-white">

<button
type="submit"
class="btn btn-primary">

<i class="bi bi-key-fill"></i>

Update Password

</button>

<a
href="<?= BASE_URL; ?>/pages/admin/dashboard.php"
class="btn btn-secondary">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

<?php

include '../../includes/layout/footer.php';

?>