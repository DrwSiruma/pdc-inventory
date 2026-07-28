<?php

/*
|--------------------------------------------------------------------------
| Generate Product Variance
|--------------------------------------------------------------------------
| Finalizes one verified product inventory record.  The quantities written
| here are the daily snapshot used by Product Variance reports.
|--------------------------------------------------------------------------
*/

require_once '../../../includes/config.php';
require_once '../../../includes/connection.php';
require_once '../../../includes/session.php';
require_once '../../../includes/no_cache.php';
require_once '../../../includes/flash.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/audit.php';
require_once '../../../includes/store_permission.php';

requireLogin();
requireRole(['accounting', 'super_admin']);

function redirectToInventory($inventoryHeaderId)
{
    header('Location: ../product_inventory/view.php?id=' . $inventoryHeaderId);
    exit;
}

function redirectToVariance($inventoryHeaderId)
{
    header('Location: view.php?id=' . $inventoryHeaderId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Variance generation must be submitted from the inventory review page.');
    header('Location: ../product_inventory/index.php');
    exit;
}

$inventoryHeaderId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, array(
    'options' => array('min_range' => 1),
));

if (!$inventoryHeaderId) {
    setFlash('danger', 'Invalid inventory selected.');
    header('Location: ../product_inventory/index.php');
    exit;
}

$conn->begin_transaction();
$generated = false;

try {
    /* Lock the header first so two Accounting users cannot generate it twice. */
    $headerStmt = $conn->prepare(
        'SELECT inventory_header_id, business_date, business_status, location_id
         FROM product_inventory_header
         WHERE inventory_header_id = ?
         FOR UPDATE'
    );

    if (!$headerStmt) {
        throw new Exception('Unable to load the inventory record.');
    }

    $headerStmt->bind_param('i', $inventoryHeaderId);
    if (!$headerStmt->execute()) {
        throw new Exception('Unable to load the inventory record.');
    }

    $header = $headerStmt->get_result()->fetch_assoc();
    $headerStmt->close();

    if (!$header) {
        throw new Exception('Inventory record not found.');
    }
    enforceStorePermission($conn, (int) $header['location_id']);

    if ($header['business_status'] !== 'verified') {
        throw new Exception('Inventory must be verified before its variance can be generated.');
    }

    /* A reported throw-away must be verified before it can become official. */
    $throwawayStmt = $conn->prepare(
        "SELECT COUNT(*) AS pending_count
         FROM product_throwaway
         WHERE inventory_header_id = ?
           AND (status IS NULL OR status <> 'verified')"
    );

    if (!$throwawayStmt) {
        throw new Exception('Unable to validate throw-away records.');
    }

    $throwawayStmt->bind_param('i', $inventoryHeaderId);
    if (!$throwawayStmt->execute()) {
        throw new Exception('Unable to validate throw-away records.');
    }

    $pendingThrowaways = (int) $throwawayStmt->get_result()->fetch_assoc()['pending_count'];
    $throwawayStmt->close();

    if ($pendingThrowaways > 0) {
        throw new Exception('All throw-away records must be verified before generating variance.');
    }

    $detailsStmt = $conn->prepare(
        'SELECT inventory_detail_id, beginning_qty, received_qty, pdr_qty, throwaway_qty, ending_qty
         FROM product_inventory_details
         WHERE inventory_header_id = ?
         FOR UPDATE'
    );

    if (!$detailsStmt) {
        throw new Exception('Unable to load inventory details.');
    }

    $detailsStmt->bind_param('i', $inventoryHeaderId);
    if (!$detailsStmt->execute()) {
        throw new Exception('Unable to load inventory details.');
    }

    $details = $detailsStmt->get_result();
    if ($details->num_rows === 0) {
        $detailsStmt->close();
        throw new Exception('Variance cannot be generated because the inventory has no product details.');
    }

    $updateDetailStmt = $conn->prepare(
        'UPDATE product_inventory_details
         SET expected_qty = ?, variance_qty = ?
         WHERE inventory_detail_id = ?'
    );

    if (!$updateDetailStmt) {
        $detailsStmt->close();
        throw new Exception('Unable to prepare the variance snapshot.');
    }

    while ($detail = $details->fetch_assoc()) {
        $expectedQty = (float) $detail['beginning_qty']
            + (float) $detail['received_qty']
            + (float) $detail['pdr_qty']
            - (float) $detail['throwaway_qty'];
        $varianceQty = (float) $detail['ending_qty'] - $expectedQty;
        $detailId = (int) $detail['inventory_detail_id'];

        $updateDetailStmt->bind_param('ddi', $expectedQty, $varianceQty, $detailId);
        if (!$updateDetailStmt->execute()) {
            $updateDetailStmt->close();
            $detailsStmt->close();
            throw new Exception('Unable to save the variance snapshot.');
        }
    }

    $updateDetailStmt->close();
    $detailsStmt->close();

    $historyStmt = $conn->prepare(
        'INSERT INTO product_variance_history
            (inventory_header_id, generated_by, generated_at, remarks)
         VALUES (?, ?, NOW(), ?)'
    );

    if (!$historyStmt) {
        throw new Exception('Unable to prepare variance history.');
    }

    $userId = (int) $_SESSION['user_id'];
    $remarks = 'Variance generated from verified inventory and throw-away quantities.';
    $historyStmt->bind_param('iis', $inventoryHeaderId, $userId, $remarks);
    if (!$historyStmt->execute()) {
        $historyStmt->close();
        throw new Exception('Unable to save variance history.');
    }
    $historyStmt->close();

    $headerUpdateStmt = $conn->prepare(
        "UPDATE product_inventory_header
         SET business_status = 'generated', generated_by = ?, generated_at = NOW()
         WHERE inventory_header_id = ? AND business_status = 'verified'"
    );

    if (!$headerUpdateStmt) {
        throw new Exception('Unable to finalize the inventory record.');
    }

    $headerUpdateStmt->bind_param('ii', $userId, $inventoryHeaderId);
    if (!$headerUpdateStmt->execute() || $headerUpdateStmt->affected_rows !== 1) {
        $headerUpdateStmt->close();
        throw new Exception('Unable to finalize the inventory record.');
    }
    $headerUpdateStmt->close();

    if (!logAudit(
        'GENERATE PRODUCT VARIANCE',
        'Product Variance',
        $inventoryHeaderId,
        'Generated product variance for ' . $header['business_date'] . '.'
    )) {
        throw new Exception('Unable to create the audit trail.');
    }

    $conn->commit();
    $generated = true;
    setFlash('success', 'Product variance generated successfully. The daily snapshot is now frozen.');
} catch (Throwable $exception) {
    $conn->rollback();
    setFlash('danger', 'Variance generation failed. ' . $exception->getMessage());
}

if ($generated) {
    redirectToVariance($inventoryHeaderId);
}

redirectToInventory($inventoryHeaderId);
