<?php

/*
|--------------------------------------------------------------------------
| Breadcrumb Navigation
|--------------------------------------------------------------------------
| Shared Breadcrumb
|--------------------------------------------------------------------------
*/

if (!isset($breadcrumbs) || !is_array($breadcrumbs) || empty($breadcrumbs)) {
    return;
}

?>

<div class="breadcrumb-wrapper">

    <nav aria-label="breadcrumb">

        <ol class="breadcrumb">

            <li class="breadcrumb-item">

                <a href="<?= BASE_URL ?>/pages/admin/dashboard.php">

                    <i class="bi bi-house-door-fill"></i>

                    Home

                </a>

            </li>

            <?php

            $last = count($breadcrumbs) - 1;

            foreach ($breadcrumbs as $index => $crumb):

            ?>

                <?php if($index == $last): ?>

                    <li
                        class="breadcrumb-item active"
                        aria-current="page">

                        <?= htmlspecialchars($crumb['title']); ?>

                    </li>

                <?php else: ?>

                    <li class="breadcrumb-item">

                        <a href="<?= $crumb['link']; ?>">

                            <?= htmlspecialchars($crumb['title']); ?>

                        </a>

                    </li>

                <?php endif; ?>

            <?php endforeach; ?>

        </ol>

    </nav>

</div>