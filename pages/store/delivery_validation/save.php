<?php

/*
|--------------------------------------------------------------------------
| Save Delivery Validation
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

$deliveryHeaderId = filter_input(
    INPUT_POST,
    'delivery_header_id',
    FILTER_VALIDATE_INT
);
$deliveryIds = $_POST['delivery_id'] ?? [];
$actualQty = $_POST['actual_qty'] ?? [];
$remarks = $_POST['remarks'] ?? [];
if (
    !$deliveryHeaderId ||
    empty($deliveryIds)
) {
    setFlash(
        'danger',
        'Invalid delivery submission.'
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
    | Validate Delivery Header
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            delivery_header_id,
            delivery_status,
            location_id,
            reference_no,
            business_date,
            delivery_no
        FROM product_delivery_header
        WHERE delivery_header_id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param(
        "i",
        $deliveryHeaderId
    );
    $stmt->execute();
    $header = $stmt
        ->get_result()
        ->fetch_assoc();
    $stmt->close();
    if (!$header) {
        throw new Exception(
            'Delivery record not found.'
        );
    }
    if ($header['delivery_status'] != 'pending') {
        throw new Exception(
            'This delivery has already been validated.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare Update Statement
    |--------------------------------------------------------------------------
    */

    $update = $conn->prepare("
        UPDATE product_delivery_logs
        SET
            actual_qty = ?,
            short_qty = ?,
            remarks = ?,
            posted_by = ?,
            posted_at = NOW()
        WHERE delivery_id = ?
    ");
    if (!$update) {
        throw new Exception($conn->error);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Delivery Items
    |--------------------------------------------------------------------------
    */

    foreach ($deliveryIds as $index => $deliveryId) {
        $deliveryId = (int) $deliveryId;
        $actual = isset($actualQty[$index])
            ? (float) $actualQty[$index]
            : 0;
        $remark = trim($remarks[$index] ?? '');

        /*
        |--------------------------------------------------------------------------
        | Get Expected Quantity
        |--------------------------------------------------------------------------
        */

        $expectedStmt = $conn->prepare("
            SELECT
                expected_qty
            FROM product_delivery_logs
            WHERE delivery_id = ?
            LIMIT 1
        ");
        if (!$expectedStmt) {
            throw new Exception($conn->error);
        }
        $expectedStmt->bind_param(
            "i",
            $deliveryId
        );
        $expectedStmt->execute();
        $expectedRow = $expectedStmt
            ->get_result()
            ->fetch_assoc();
        $expectedStmt->close();
        if (!$expectedRow) {
            throw new Exception(
                "Delivery item not found."
            );
        }
        $expected = (float) $expectedRow['expected_qty'];

        /*
        |--------------------------------------------------------------------------
        | Compute Short Quantity
        |--------------------------------------------------------------------------
        */

        $shortQty = $expected - $actual;
        if ($shortQty < 0) {
            $shortQty = 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Update Item
        |--------------------------------------------------------------------------
        */

        $update->bind_param(
            "ddsii",
            $actual,
            $shortQty,
            $remark,
            $userId,
            $deliveryId
        );
        if (!$update->execute()) {
            throw new Exception(
                "Unable to update delivery item."
            );
        }
    }
    $update->close();

    /*
    |--------------------------------------------------------------------------
    | Update Delivery Header
    |--------------------------------------------------------------------------
    */

    $headerUpdate = $conn->prepare("
        UPDATE product_delivery_header
        SET
            delivery_status = 'validated',
            validated_by = ?,
            validated_at = NOW()
        WHERE delivery_header_id = ?
    ");
    if (!$headerUpdate) {
        throw new Exception($conn->error);
    }
    $headerUpdate->bind_param(
        "ii",
        $userId,
        $deliveryHeaderId
    );
    if (!$headerUpdate->execute()) {
        throw new Exception(
            "Unable to update delivery status."
        );
    }
    $headerUpdate->close();

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */

    if (!logAudit(
        'VALIDATE DELIVERY',
        'Store Delivery Validation',
        $deliveryHeaderId,
        'Reference No: ' . $header['reference_no'] .
        ' | Delivery: ' . $header['delivery_no'] .
        ' | Business Date: ' . $header['business_date'] .
        ' | Delivery successfully validated by Store.'
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
        'Delivery has been successfully validated.'
    );
    header(
        'Location:view.php?id=' . $deliveryHeaderId
    );
    exit;
} catch (Exception $e) {
    $conn->rollback();
    setFlash(
        'danger',
        $e->getMessage()
    );
    header(
        'Location:view.php?id=' . $deliveryHeaderId
    );
    exit;
}