<?php

/*
|--------------------------------------------------------------------------
| System Footer
|--------------------------------------------------------------------------
| Shared Footer Layout
|--------------------------------------------------------------------------
*/

?>

        <!-- Footer -->
        <footer class="system-footer">

            <div class="container-fluid">

                <div class="row align-items-center">

                    <div class="col-md-6 text-md-start text-center">

                        <strong><?= SYSTEM_NAME; ?></strong>

                        <br>

                        <small class="text-muted">

                            Version <?= SYSTEM_VERSION; ?>

                        </small>

                    </div>

                    <div class="col-md-6 text-md-end text-center mt-2 mt-md-0">

                        <small class="text-muted">

                            © <?= date('Y'); ?> Panda Development Corporation

                            <br>

                            Information Technology Department

                        </small>

                    </div>

                </div>

            </div>

        </footer>

    </main>

</div>
<!-- End Page Wrapper -->

</div>
<!-- End Wrapper -->

<!-- jQuery -->
<script src="<?= BASE_URL ?>/assets/vendor/jquery/jquery.min.js"></script>

<!-- Bootstrap -->
<script src="<?= BASE_URL ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="<?= BASE_URL ?>/assets/vendor/datatables/jquery.dataTables.min.js"></script>
<!-- <script src="<?= BASE_URL ?>/assets/vendor/datatables/jquery.min.js"></script>
<script src="<?= BASE_URL ?>/assets/vendor/datatables/jquerys.min.js"></script>
<script src="<?= BASE_URL ?>/assets/vendor/datatables/jquery-3.5.1.js"></script> -->

<!-- Layout Script -->
<script src="<?= BASE_URL ?>/assets/js/layout.js"></script>

<?php
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'super_admin') {
    ?>
    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <?php
    }
    elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'accounting') {
    ?>
    <script src="<?= BASE_URL ?>/assets/js/accounting.js"></script>
    <?php
    }
    elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'store') {
    ?>
    <script src="<?= BASE_URL ?>/assets/js/store.js"></script>
    <?php
    }
?>

</body>

</html>