<?php

/*
|--------------------------------------------------------------------------
| Approve Product Variance
|--------------------------------------------------------------------------
| Accounting approval after reviewing generated variance.
| Once approved, the business day can be locked.
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
requireRole('accounting');

/*
|--------------------------------------------------------------------------
| Validate Request
|--------------------------------------------------------------------------
*/

$inventoryHeaderId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;
if ($inventoryHeaderId <= 0) {
    setFlash('danger', 'Invalid variance record.');
    header('Location: index.php');
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
        throw new Exception('Unable to prepare inventory query.');
    }
    $stmt->bind_param("i", $inventoryHeaderId);
    $stmt->execute();
    $header = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$header) {
        throw new Exception('Inventory record not found.');
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Status
    |--------------------------------------------------------------------------
    */

    if ($header['business_status'] != 'generated') {
        throw new Exception(
            'Only generated variances can be approved.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Inventory Header
    |--------------------------------------------------------------------------
    */

    $update = $conn->prepare("
        UPDATE product_inventory_header
        SET
            business_status = 'approved',
            approved_by = ?,
            approved_at = NOW()
        WHERE
            inventory_header_id = ?
    ");
    if (!$update) {
        throw new Exception(
            'Unable to prepare approval update.'
        );
    }
    $userId = (int) $_SESSION['user_id'];
    $update->bind_param(
        "ii",
        $userId,
        $inventoryHeaderId
    );
    if (!$update->execute()) {
        throw new Exception(
            'Unable to approve variance.'
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
        WHERE location_id = ?
    ");
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
        'APPROVE PRODUCT VARIANCE',
        'Product Variance',
        $inventoryHeaderId,
        'Store: '
        . $store['location_name']
        . ' | Business Date: '
        . $header['business_date']
        . ' | Product variance approved.'
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
        'Product variance has been successfully approved.'
    );
    header(
        'Location: view.php?id='
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
        'Location: view.php?id='
        . $inventoryHeaderId
    );
    exit;
}
