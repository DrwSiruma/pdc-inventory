<?php

/*
|--------------------------------------------------------------------------
| Save Product Throw Away
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
$productId = filter_input(
    INPUT_POST,
    'product_id',
    FILTER_VALIDATE_INT
);
$throwawayId = filter_input(
    INPUT_POST,
    'throwaway_id',
    FILTER_VALIDATE_INT
);
$storeQty = filter_input(
    INPUT_POST,
    'store_qty',
    FILTER_VALIDATE_FLOAT
);
$remarks = trim(
    $_POST['remarks'] ?? ''
);
if (
    !$inventoryHeaderId ||
    !$productId ||
    $storeQty === false
) {
    setFlash(
        'danger',
        'Invalid request.'
    );
    header(
        'Location:index.php'
    );
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
    | Verify Inventory Header
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            business_status
        FROM product_inventory_header
        WHERE
            inventory_header_id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        throw new Exception(
            $conn->error
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
            'Business day is already submitted.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT or UPDATE
    |--------------------------------------------------------------------------
    */
    if (empty($throwawayId)) {
        /*
        |--------------------------------------------------------------------------
        | Check Duplicate Product
        |--------------------------------------------------------------------------
        */
        $stmt = $conn->prepare("
            SELECT
                throwaway_id
            FROM product_throwaway
            WHERE
                inventory_header_id = ?
            AND
                product_id = ?
            LIMIT 1
        ");
        if (!$stmt) {
            throw new Exception(
                $conn->error
            );
        }
        $stmt->bind_param(
            "ii",
            $inventoryHeaderId,
            $productId
        );
        $stmt->execute();
        $duplicate = $stmt
            ->get_result()
            ->fetch_assoc();
        $stmt->close();
        if ($duplicate) {
            throw new Exception(
                'This product already exists in the Throw Away list.'
            );
        }
        /*
        |--------------------------------------------------------------------------
        | Insert Throw Away
        |--------------------------------------------------------------------------
        */
        $stmt = $conn->prepare("
            INSERT INTO product_throwaway
            (
                inventory_header_id,
                product_id,
                store_qty,
                remarks,
                status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                'pending'
            )
        ");
        if (!$stmt) {
            throw new Exception(
                $conn->error
            );
        }
        $stmt->bind_param(
            "iids",
            $inventoryHeaderId,
            $productId,
            $storeQty,
            $remarks
        );
        if (!$stmt->execute()) {
            throw new Exception(
                'Unable to save throw away record.'
            );
        }
        $throwawayId = $stmt->insert_id;
        $stmt->close();
        $auditAction = 'ADD PRODUCT THROW AWAY';
    } else {
        /*
        |--------------------------------------------------------------------------
        | Update Throw Away
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE product_throwaway
            SET
                store_qty = ?,
                remarks = ?
            WHERE
                throwaway_id = ?
        ");
        if (!$stmt) {
            throw new Exception(
                $conn->error
            );
        }
        $stmt->bind_param(
            "dsi",
            $storeQty,
            $remarks,
            $throwawayId
        );
        if (!$stmt->execute()) {
            throw new Exception(
                'Unable to update throw away record.'
            );
        }
        $stmt->close();
        $auditAction = 'UPDATE PRODUCT THROW AWAY';
    }
    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */

    if (!logAudit(
        $auditAction,
        'Store Product Throw Away',
        $throwawayId,
        'Throw Away record has been saved successfully.'
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
    if ($auditAction == 'ADD PRODUCT THROW AWAY') {
        setFlash(
            'success',
            'Throw Away record has been added successfully.'
        );
    } else {
        setFlash(
            'success',
            'Throw Away record has been updated successfully.'
        );
    }
    header(
        'Location:index.php'
    );
    exit;
} catch (Exception $e) {
    $conn->rollback();
    setFlash(
        'danger',
        $e->getMessage()
    );
    if (!empty($throwawayId)) {
        header(
            'Location:edit.php?id=' .
            $throwawayId
        );
    } else {
        header(
            'Location:add.php'
        );
    }
    exit;
}