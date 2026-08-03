<?php

/*
|--------------------------------------------------------------------------
| Submit Business Day
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
        'Invalid business day.'
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
    | Load Business Day
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            inventory_header_id,
            business_date,
            business_status,
            location_id
        FROM product_inventory_header
        WHERE
            inventory_header_id = ?
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
            'Business Day not found.'
        );
    }
    if ($header['business_status'] != 'draft') {
        throw new Exception(
            'Business Day has already been submitted.'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Validate Inventory Details
    |--------------------------------------------------------------------------
    */
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) total
        FROM product_inventory_details
        WHERE
            inventory_header_id = ?
    ");
    $stmt->bind_param(
        "i",
        $inventoryHeaderId
    );
    $stmt->execute();
    $inventoryCount = $stmt
        ->get_result()
        ->fetch_assoc()['total'];
    $stmt->close();
    if ($inventoryCount <= 0) {
        throw new Exception(
            'Product Inventory is empty.'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Validate Delivery Validation
    |--------------------------------------------------------------------------
    */
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) total
        FROM product_delivery_header
        WHERE
            location_id = ?
        AND
            business_date = ?
    ");
    $stmt->bind_param(
        "is",
        $header['location_id'],
        $header['business_date']
    );
    $stmt->execute();
    $deliveryCount = $stmt
        ->get_result()
        ->fetch_assoc()['total'];
    $stmt->close();
    if ($deliveryCount <= 0) {
        throw new Exception(
            'Delivery Validation has not been completed.'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Validate Product Inventory
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS total
        FROM product_inventory_details
        WHERE
            inventory_header_id = ?
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

    $inventory = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();

    if ($inventory['total'] <= 0) {

        throw new Exception(
            'Product Inventory has not been completed.'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Throw Away
    |--------------------------------------------------------------------------
    | Throw Away is OPTIONAL.
    | Zero records are allowed.
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Update Business Day Status
    |--------------------------------------------------------------------------
    */

    $submittedAt = date('Y-m-d H:i:s');

    $submittedBy = (int) $_SESSION['user_id'];

    /*
    |--------------------------------------------------------------------------
    | Check Available Columns
    |--------------------------------------------------------------------------
    */

    $hasSubmittedColumns = false;

    $result = $conn->query("
        SHOW COLUMNS
        FROM product_inventory_header
        LIKE 'submitted_at'
    ");

    if ($result && $result->num_rows > 0) {

        $hasSubmittedColumns = true;

    }

    if ($hasSubmittedColumns) {

        $stmt = $conn->prepare("
            UPDATE product_inventory_header
            SET
                business_status = 'submitted',
                submitted_by = ?,
                submitted_at = ?
            WHERE
                inventory_header_id = ?
        ");

        if (!$stmt) {

            throw new Exception(
                $conn->error
            );

        }

        $stmt->bind_param(
            "isi",
            $submittedBy,
            $submittedAt,
            $inventoryHeaderId
        );

    } else {

        $stmt = $conn->prepare("
            UPDATE product_inventory_header
            SET
                business_status = 'submitted'
            WHERE
                inventory_header_id = ?
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

    }

    if (!$stmt->execute()) {

        throw new Exception(
            'Unable to submit Business Day.'
        );

    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */

    if (!logAudit(

        'SUBMIT BUSINESS DAY',

        'Store Business Day',

        $inventoryHeaderId,

        'Business Day submitted to Accounting.'

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

        'Business Day has been successfully submitted to Accounting.'

    );

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

    header(
        'Location:index.php'
    );

    exit;

}