<?php

/*
|--------------------------------------------------------------------------
| Product Inventory
|--------------------------------------------------------------------------
| Accounting - Product Inventory Monitoring
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
requireRole('accounting');

/*
|--------------------------------------------------------------------------
| Load Product Inventory Headers
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    h.inventory_header_id,

    h.business_date,

    h.business_status,

    h.submitted_at,

    h.verified_at,

    h.generated_at,

    l.location_name,

    submitter.full_name AS submitted_by,

    verifier.full_name AS verified_by,

    COUNT(d.inventory_detail_id) AS total_products

FROM product_inventory_header h

INNER JOIN locations l
    ON l.location_id = h.location_id

LEFT JOIN users submitter
    ON submitter.user_id = h.submitted_by

LEFT JOIN users verifier
    ON verifier.user_id = h.verified_by

LEFT JOIN product_inventory_details d
    ON d.inventory_header_id = h.inventory_header_id

GROUP BY h.inventory_header_id

ORDER BY

    h.business_date DESC,

    l.location_name ASC

";

$result = $conn->query($sql);

/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$pageTitle = "Product Inventory";

$breadcrumbs = [

    [

        'title' => 'Product Inventory'

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

                        Product Inventory Monitoring

                    </h5>

                    <small class="text-muted">

                        Daily outlet inventory submitted by stores.

                    </small>

                </div>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table
                        id="productInventoryTable"
                        class="table table-bordered table-hover align-middle">

                        <thead>

                        <tr>

                            <th>Business Date</th>

                            <th>Store</th>

                            <th class="text-center">

                                Products

                            </th>

                            <th>Submitted By</th>

                            <th>Status</th>

                            <th>Verified By</th>

                            <th width="170">

                                Action

                            </th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php if($result->num_rows > 0): ?>

                            <?php while($row = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>

                                        <?= date('M d, Y', strtotime($row['business_date'])); ?>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars($row['location_name']); ?>

                                    </td>

                                    <td class="text-center">

                                        <?= number_format($row['total_products']); ?>

                                    </td>

                                    <td>

                                        <?= $row['submitted_by'] ?? '-'; ?>

                                    </td>

                                    <td>

                                        <?php

                                        switch($row['business_status']){

                                            case 'submitted':

                                                $badge='primary';

                                            break;

                                            case 'verified':

                                                $badge='success';

                                            break;

                                            case 'generated':

                                                $badge='info';

                                            break;

                                            case 'locked':

                                                $badge='dark';

                                            break;

                                            default:

                                                $badge='warning';

                                            break;

                                        }

                                        ?>

                                        <span class="badge bg-<?= $badge ?>">

                                            <?= ucfirst($row['business_status']); ?>

                                        </span>

                                    </td>

                                    <td>

                                        <?= $row['verified_by'] ?? '-'; ?>

                                    </td>

                                    <td class="text-center">

                                        <a

                                            href="view.php?id=<?= $row['inventory_header_id']; ?>"

                                            class="btn btn-sm btn-info"

                                            title="View">

                                            <i class="bi bi-eye-fill"></i>

                                        </a>

                                        <?php if(

                                            $row['business_status']=='submitted'

                                        ): ?>

                                        <a

                                            href="verify.php?id=<?= $row['inventory_header_id']; ?>"

                                            class="btn btn-sm btn-success"

                                            title="Verify">

                                            <i class="bi bi-check-circle-fill"></i>

                                        </a>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center text-muted">

                                    No submitted inventory found.

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