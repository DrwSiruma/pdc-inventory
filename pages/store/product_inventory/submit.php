<?php

/*
|--------------------------------------------------------------------------
| Submit Product Inventory
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
    INPUT_GET,
    'id',
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
| Logged-in User
|--------------------------------------------------------------------------
*/

$userId = (int) $_SESSION['user_id'];

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

    $stmt = $conn->prepare("
        SELECT
            inventory_header_id,
            business_date,
            business_status,
            location_id
        FROM product_inventory_header
        WHERE inventory_header_id = ?
        FOR UPDATE
    ");
    if (!$stmt) {
        throw new Exception(
            'Unable to load inventory.'
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
            'Inventory not found.'
        );
    }
    if ($header['business_status'] != 'draft') {
        throw new Exception(
            'Inventory has already been submitted.'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Verify Inventory Details
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_items,
            SUM(
                CASE
                    WHEN ending_qty = 0
                    THEN 1
                    ELSE 0
                END
            ) AS incomplete_items
        FROM product_inventory_details
        WHERE inventory_header_id = ?
    ");
    if (!$stmt) {
        throw new Exception(
            'Unable to validate inventory details.'
        );
    }
    $stmt->bind_param(
        "i",
        $inventoryHeaderId
    );
    $stmt->execute();
    $validation = $stmt
        ->get_result()
        ->fetch_assoc();
    $stmt->close();
    if ((int) $validation['total_items'] <= 0) {
        throw new Exception(
            'No inventory items found.'
        );
    }
    if ((int) $validation['incomplete_items'] > 0) {
        throw new Exception(
            'Please encode the ending quantity of all products before submitting.'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Submit Inventory
    |--------------------------------------------------------------------------
    */

    $update = $conn->prepare("
        UPDATE product_inventory_header
        SET
            business_status = 'submitted',
            submitted_by = ?,
            submitted_at = NOW()
        WHERE
            inventory_header_id = ?
    ");
    if (!$update) {
        throw new Exception(
            'Unable to prepare inventory submission.'
        );
    }
    $update->bind_param(
        "ii",
        $userId,
        $inventoryHeaderId
    );
    if (!$update->execute()) {
        throw new Exception(
            'Unable to submit inventory.'
        );
    }
    $update->close();
    /*
    |--------------------------------------------------------------------------
    | Load Store Information
    |--------------------------------------------------------------------------
    */

    $storeStmt = $conn->prepare("
        SELECT
            location_name
        FROM locations
        WHERE location_id = ?
        LIMIT 1
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
        'SUBMIT PRODUCT INVENTORY',
        'Store Product Inventory',
        $inventoryHeaderId,
        'Store: '
        . $store['location_name']
        . ' | Business Date: '
        . $header['business_date']
        . ' | Product inventory submitted for Accounting verification.'
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
        'Product inventory has been successfully submitted to Accounting.'
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