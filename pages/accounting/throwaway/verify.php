<?php

/*
|--------------------------------------------------------------------------
| Verify Throw Away
|--------------------------------------------------------------------------
*/

require_once '../../../includes/config.php';
require_once '../../../includes/connection.php';
require_once '../../../includes/session.php';
require_once '../../../includes/no_cache.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/flash.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/store_permission.php';

requireLogin();
requireRole(['accounting', 'super_admin']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlash(
        'danger',
        'Invalid record.'
    );
    header("Location:index.php");
    exit;
}
$inventory_header_id = (int)$_GET['id'];
$headerStmt = $conn->prepare("SELECT location_id FROM product_inventory_header WHERE inventory_header_id = ? LIMIT 1");
$headerStmt->bind_param("i", $inventory_header_id);
$headerStmt->execute();
$header = $headerStmt->get_result()->fetch_assoc();
$headerStmt->close();
if (!$header) {
    setFlash('danger', 'Record not found.');
    header('Location: index.php');
    exit;
}
enforceStorePermission($conn, (int) $header['location_id']);
$conn->begin_transaction();

try {

    /*
    |--------------------------------------------------------------------------
    | Verify all Throw Away
    |--------------------------------------------------------------------------
    */

    $verify = $conn->prepare("
        UPDATE product_throwaway
        SET
            status='verified',
            verified_by=?,
            verified_at=NOW()
        WHERE
            inventory_header_id=?
    ");
    $verify->bind_param(
        "ii",
        $_SESSION['user_id'],
        $inventory_header_id
    );
    $verify->execute();
    $verify->close();

    /*
    |--------------------------------------------------------------------------
    | Copy verified Throw Away
    | into Inventory Details
    |--------------------------------------------------------------------------
    */

    $copy = $conn->prepare("
        UPDATE
            product_inventory_details pid
        INNER JOIN product_throwaway pt
            ON pt.inventory_header_id = pid.inventory_header_id
            AND pt.product_id = pid.product_id
        SET
            pid.throwaway_qty = pt.accounting_qty
        WHERE
            pid.inventory_header_id = ?
    ");
    $copy->bind_param(
        "i",
        $inventory_header_id
    );
    $copy->execute();
    $copy->close();
    
    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */

    if (function_exists('createAuditTrail')) {
        createAuditTrail(
            $_SESSION['user_id'],
            'Verified Product Throw Away',
            'product_inventory_header',
            $inventory_header_id
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
        'Throw Away has been verified successfully. You may now proceed to Product Variance.'
    );
} catch (Exception $e) {
    $conn->rollback();
    setFlash(
        'danger',
        $e->getMessage()
    );
}
header("Location: ../variance/index.php?header_id=" . $inventory_header_id);
exit;
