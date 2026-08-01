<?php

/*
|--------------------------------------------------------------------------
| Save Product Inventory
|--------------------------------------------------------------------------
| Store Module
|--------------------------------------------------------------------------
*/

require_once '../../../includes/config.php';
require_once '../../../includes/connection.php';
require_once '../../../includes/session.php';
require_once '../../../includes/no_cache.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/flash.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/audit.php';

requireLogin();
requireRole('store');

/*
|--------------------------------------------------------------------------
| Validate Request
|--------------------------------------------------------------------------
*/

$inventoryHeaderId = filter_input(
    INPUT_POST,
    'inventory_header_id',
    FILTER_VALIDATE_INT
);
if (!$inventoryHeaderId) {
    setFlash(
        'danger',
        'Invalid inventory.'
    );
    header('Location:index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Arrays
|--------------------------------------------------------------------------
*/

$detailIds = $_POST['detail_id'] ?? [];
$pdrQtys = $_POST['pdr_qty'] ?? [];
$throwQtys = $_POST['throwaway_qty'] ?? [];
$endingQtys = $_POST['ending_qty'] ?? [];

/*
|--------------------------------------------------------------------------
| Begin Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();
try {

    /*
    |--------------------------------------------------------------------------
    | Verify Header
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            business_status
        FROM product_inventory_header
        WHERE inventory_header_id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param(
        "i",
        $inventoryHeaderId
    );
    $stmt->execute();
    $header = $stmt
        ->get_result()
        ->fetch_assoc();
    $stmt->close();
    if (!$header) {
        throw new Exception(
            'Inventory not found.'
        );
    }
    if ($header['business_status'] != 'draft') {
        throw new Exception(
            'Inventory can no longer be edited.'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Prepare Update Statement
    |--------------------------------------------------------------------------
    */

    $update = $conn->prepare("
        UPDATE product_inventory_details
        SET
            pdr_qty = ?,
            throwaway_qty = ?,
            ending_qty = ?,
            expected_qty = ?,
            variance_qty = ?
        WHERE
            inventory_detail_id = ?
    ");

    if (!$update) {
        throw new Exception(
            $conn->error
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Save Every Product
    |--------------------------------------------------------------------------
    */

    $totalVariance = 0;
    foreach ($detailIds as $index => $detailId) {
        $detailId = (int) $detailId;
        /*
        |--------------------------------------------------------------------------
        | Load Existing Quantities
        |--------------------------------------------------------------------------
        */
        $stmt = $conn->prepare("
            SELECT
                beginning_qty,
                received_qty
            FROM product_inventory_details
            WHERE inventory_detail_id = ?
            LIMIT 1
        ");
        if (!$stmt) {
            throw new Exception(
                $conn->error
            );
        }
        $stmt->bind_param(
            "i",
            $detailId
        );
        $stmt->execute();
        $detail = $stmt
            ->get_result()
            ->fetch_assoc();
        $stmt->close();
        if (!$detail) {
            throw new Exception(
                "Inventory detail not found."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | User Inputs
        |--------------------------------------------------------------------------
        */

        $beginningQty =
            (float) $detail['beginning_qty'];
        $receivedQty =
            (float) $detail['received_qty'];
        $pdrQty =
            isset($pdrQtys[$index])
                ? (float) $pdrQtys[$index]
                : 0;
        $throwawayQty =
            isset($throwQtys[$index])
                ? (float) $throwQtys[$index]
                : 0;
        $endingQty =
            isset($endingQtys[$index])
                ? (float) $endingQtys[$index]
                : 0;

        /*
        |--------------------------------------------------------------------------
        | System Computation
        |--------------------------------------------------------------------------
        */

        $expectedQty =
            $beginningQty
            + $receivedQty
            + $pdrQty
            - $throwawayQty;
        $varianceQty =
            $expectedQty
            - $endingQty;
        $totalVariance +=
            abs($varianceQty);

        /*
        |--------------------------------------------------------------------------
        | Update Inventory Detail
        |--------------------------------------------------------------------------
        */

        $update->bind_param(
            "dddddi",
            $pdrQty,
            $throwawayQty,
            $endingQty,
            $expectedQty,
            $varianceQty,
            $detailId
        );
        if (!$update->execute()) {
            throw new Exception(
                "Unable to save inventory detail."
            );
        }
    }
    $update->close();
    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */
    if (!logAudit(
        'SAVE PRODUCT INVENTORY',
        'Store Product Inventory',
        $inventoryHeaderId,
        'Store inventory saved successfully.'
    )) {
        throw new Exception(
            'Unable to create audit trail.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Commit Transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();
    setFlash(
        'success',
        'Product inventory has been saved successfully.'
    );
    header(
        'Location:view.php?id=' .
        $inventoryHeaderId
    );
    exit;
} catch (Exception $e) {
    $conn->rollback();
    setFlash(
        'danger',
        $e->getMessage()
    );
    header(
        'Location:view.php?id=' .
        $inventoryHeaderId
    );
    exit;
}