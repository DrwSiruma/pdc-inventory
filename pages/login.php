<?php include('header.php'); ?>

    <div class="col-11 col-sm-8 col-md-5 col-lg-4">

        <div class="card shadow login-card">
            <div class="card-body p-4">

                <!-- Logo -->
                <div class="text-center mb-3">
                <!-- Optional: Replace with image -->
                    <!-- <h3 class="text-danger fw-bold">DUNKIN'</h3> -->
                    <img src="../img/pdcxdunkin_logo.png" alt="Logo" class="img-fluid mb-2">
                    <div class="system-title">Digital Inventory System</div>
                    <small class="text-muted">PARL & SCIR Monitoring</small>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="login_process">

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text"      name="username" class="form-control" placeholder="Enter username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-magenta btn-lg w-100">
                    Login
                    </button>
                </div>

                </form>

            </div>

            <div class="card-footer text-center bg-light small">
                © <?= date('Y'); ?> Panda Development Corp. | IT Department
            </div>
        </div>

    </div>

<?php include('footer.php'); ?>