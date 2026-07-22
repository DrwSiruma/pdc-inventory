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

<!-- Layout Script -->
<script src="<?= BASE_URL ?>/assets/js/layout.js"></script>

</body>

</html>