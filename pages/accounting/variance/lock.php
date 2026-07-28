<?php

/*
|--------------------------------------------------------------------------
| Lock Business Day
|--------------------------------------------------------------------------
| Final Accounting Process
| Locks the Business Day permanently after Product Variance generation.
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
require_once '../../../includes/store_permission.php';

requireLogin();
requireRole(['accounting', 'super_admin']);

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
        'Invalid inventory record.'
    );
    header('Location:index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Begin Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();
try {

    /*
    |--------------------------------------------------------------------------
    | Lock Inventory Header
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            inventory_header_id,
            business_date,
            business_status,
            location_id
        FROM product_inventory_header
        WHERE inventory_header_id = ?
        FOR UPDATE
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception(
            'Unable to prepare inventory query.'
        );
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
            'Inventory record not found.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store Permission
    |--------------------------------------------------------------------------
    */

    if (
        !canAccessStore(
            $conn,
            $header['location_id']
        )
    ) {
        throw new Exception(
            'You are not assigned to this store.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Business Status
    |--------------------------------------------------------------------------
    */

    if ($header['business_status'] != 'generated') {
        throw new Exception(
            'Only generated business days can be locked.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lock Business Day
    |--------------------------------------------------------------------------
    */

    $userId = (int) $_SESSION['user_id'];
    $update = $conn->prepare("
        UPDATE product_inventory_header
        SET
            business_status='locked',
            locked_by=?,
            locked_at=NOW()
        WHERE
            inventory_header_id=?
            AND business_status='generated'
    ");
    if (!$update) {
        throw new Exception(
            'Unable to prepare lock update.'
        );
    }
    $update->bind_param(
        "ii",
        $userId,
        $inventoryHeaderId
    );
    if (!$update->execute()) {
        throw new Exception(
            'Unable to lock business day.'
        );
    }
    if ($update->affected_rows != 1) {
        throw new Exception(
            'Business Day has already been locked.'
        );
    }
    $update->close();

    /*
    |--------------------------------------------------------------------------
    | Get Store Information
    |--------------------------------------------------------------------------
    */

    $storeStmt = $conn->prepare("
        SELECT
            location_name
        FROM locations
        WHERE location_id=?
    ");
    if (!$storeStmt) {
        throw new Exception(
            'Unable to retrieve store information.'
        );
    }
    $storeStmt->bind_param(
        "i",
        $header['location_id']
    );
    $storeStmt->execute();
    $store = $storeStmt
        ->get_result()
        ->fetch_assoc();
    $storeStmt->close();

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */
    if (!logAudit(
        'LOCK BUSINESS DAY',
        'Product Inventory',
        $inventoryHeaderId,
        'Store: '
        . $store['location_name']
        . ' | Business Date: '
        . $header['business_date']
        . ' | Business Day locked by Accounting.'
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
        'Business Day has been successfully locked.'
    );
    header(
        'Location:view.php?id='
        . $inventoryHeaderId
    );
    exit;
} catch (Exception $e) {
    $conn->rollback();
    setFlash(
        'danger',
        $e->getMessage()
    );
    header(
        'Location:view.php?id='
        . $inventoryHeaderId
    );
    exit;
}