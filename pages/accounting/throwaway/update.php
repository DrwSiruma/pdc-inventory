<?php

/*
|--------------------------------------------------------------------------
| Update Throw Away Verification
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
| Validate Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}
$inventory_header_id = (int) $_POST['inventory_header_id'];
$throwaway_ids  = $_POST['throwaway_id'] ?? [];
$product_ids    = $_POST['product_id'] ?? [];
$accounting_qty = $_POST['accounting_qty'] ?? [];
$remarks        = $_POST['remarks'] ?? [];

/*
|--------------------------------------------------------------------------
| Begin Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();
try {
    /*
    |--------------------------------------------------------------------------
    | Save Throw Away Verification
    |--------------------------------------------------------------------------
    */

    for ($i = 0; $i < count($product_ids); $i++) {

        $throwawayId = (int)($throwaway_ids[$i] ?? 0);
        $productId = (int)$product_ids[$i];
        $qty = (float)$accounting_qty[$i];
        $remark = trim($remarks[$i]);

        /*
        |--------------------------------------------------------------------------
        | Existing Record
        |--------------------------------------------------------------------------
        */

        if ($throwawayId > 0) {
            $update = $conn->prepare("
                UPDATE product_throwaway
                SET
                    accounting_qty = ?,
                    remarks = ?,
                    status = 'verified',
                    verified_by = ?,
                    verified_at = NOW()
                WHERE
                    throwaway_id = ?
            ");
            $update->bind_param(
                "dsii",
                $qty,
                $remark,
                $_SESSION['user_id'],
                $throwawayId
            );
            if (!$update->execute()) {
                throw new Exception('Unable to update throw away record.');
            }
            $update->close();
        } else {
            /*
            |--------------------------------------------------------------------------
            | New Record
            |--------------------------------------------------------------------------
            */
            $insert = $conn->prepare("
                INSERT INTO product_throwaway (
                    inventory_header_id,
                    product_id,
                    store_qty,
                    accounting_qty,
                    remarks,
                    status,
                    verified_by,
                    verified_at
                )
                VALUES (
                    ?,
                    ?,
                    0,
                    ?,
                    ?,
                    'verified',
                    ?,
                    NOW()
                )
            ");
            $insert->bind_param(
                "iidsi",
                $inventory_header_id,
                $productId,
                $qty,
                $remark,
                $_SESSION['user_id']
            );
            if (!$insert->execute()) {
                throw new Exception('Unable to create throw away record.');
            }
            $insert->close();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Update Inventory Header
    |--------------------------------------------------------------------------
    */

    $updateHeader = $conn->prepare("
        UPDATE product_inventory_header
        SET
            business_status = 'submitted'
        WHERE
            inventory_header_id = ?
            AND business_status = 'verified'
    ");
    $updateHeader->bind_param(
        "i",
        $inventory_header_id
    );
    $updateHeader->execute();
    $updateHeader->close();
    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */
    if (function_exists('createAuditTrail')) {
        createAuditTrail(
            $_SESSION['user_id'],
            'Updated Throw Away Verification',
            'product_inventory_header',
            $inventory_header_id
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */
    $conn->commit();
    setFlash(
        'success',
        'Throw Away verification has been saved successfully.'
    );
} catch (Exception $e) {
    $conn->rollback();
    setFlash(
        'danger',
        $e->getMessage()
    );
}
header("Location: view.php?id=" . $inventory_header_id);
exit;