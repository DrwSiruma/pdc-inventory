<?php

/*
|--------------------------------------------------------------------------
| Create Today's Product Inventory
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
| Logged-in Store
|--------------------------------------------------------------------------
*/

$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT
        u.location_id,
        l.location_name
    FROM users u
    INNER JOIN locations l
        ON l.location_id = u.location_id
    WHERE u.user_id = ?
    LIMIT 1
");
if (!$stmt) {
    die($conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$locationId = (int) $user['location_id'];
$businessDate = date('Y-m-d');

/*
|--------------------------------------------------------------------------
| Check Existing Inventory
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        inventory_header_id
    FROM product_inventory_header
    WHERE
        location_id = ?
    AND business_date = ?
    LIMIT 1
");
$stmt->bind_param(
    "is",
    $locationId,
    $businessDate
);
$stmt->execute();
$existing = $stmt
    ->get_result()
    ->fetch_assoc();
$stmt->close();
if ($existing) {
    header(
        "Location:view.php?id=" .
        $existing['inventory_header_id']
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
    | Create Inventory Header
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        INSERT INTO product_inventory_header(location_id,business_date,business_status,submitted_by,submitted_at)VALUES( ?, ?, 'draft', ?, NOW())
    ");
    if (!$stmt) {
        throw new Exception(
            $conn->error
        );
    }
    $stmt->bind_param(
        "isi",
        $locationId,
        $businessDate,
        $userId
    );
    if (!$stmt->execute()) {
        throw new Exception(
            "Unable to create inventory header."
        );
    }
    $inventoryHeaderId = $conn->insert_id;
    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Load Active Products
    |--------------------------------------------------------------------------
    */

    $products = $conn->query("
        SELECT
            product_id
        FROM products
        WHERE active = 1
        ORDER BY
            product_name ASC
    ");

    if (!$products) {
        throw new Exception(
            "Unable to load products."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare Detail Insert
    |--------------------------------------------------------------------------
    */

    
    $stmtDetail = $conn->prepare("
        INSERT INTO product_inventory_details
        (
            inventory_header_id,
            product_id,
            beginning_qty,
            received_qty,
            pdr_qty,
            throwaway_qty,
            ending_qty,
            expected_qty,
            variance_qty
        )
        VALUES (?, ?, ?, ?, 0, 0, 0, 0, 0)
    ");
    if (!$detailStmt) {
        throw new Exception(
            $conn->error
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Process Every Product
    |--------------------------------------------------------------------------
    */

    while ($product = $products->fetch_assoc()) {
        $productId = (int) $product['product_id'];

        /*
        |--------------------------------------------------------------------------
        | Compute Validated Deliveries
        |--------------------------------------------------------------------------
        */

        $deliveryStmt = $conn->prepare("
            SELECT
                COALESCE(
                    SUM(l.actual_qty),
                    0
                ) AS total_delivery
            FROM product_delivery_logs l
            INNER JOIN product_delivery_header h
                ON h.delivery_header_id =
                   l.delivery_header_id
            WHERE
                h.location_id = ?
            AND h.business_date = ?
            AND h.delivery_status = 'validated'
            AND l.product_id = ?
        ");
        if (!$deliveryStmt) {
            throw new Exception(
                $conn->error
            );
        }
        $deliveryStmt->bind_param(
            "isi",
            $locationId,
            $businessDate,
            $productId
        );
        $deliveryStmt->execute();
        $delivery = $deliveryStmt
            ->get_result()
            ->fetch_assoc();
        $deliveryStmt->close();
        $beginningQty = (float)
            $delivery['total_delivery'];
        $deliveryQty = $beginningQty;
        $receivedQty = $beginningQty;

        /*
        |--------------------------------------------------------------------------
        | Insert Product Inventory Detail
        |--------------------------------------------------------------------------
        */

        $stmtDetail->bind_param(
            "iidd",
            $inventoryHeaderId,
            $productId,
            $beginningQty,
            $receivedQty
        );

        if (!$detailStmt->execute()) {
            throw new Exception(
                "Unable to create inventory detail."
            );
        }
    }
    $detailStmt->close();

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */

    if (!logAudit(
        'CREATE PRODUCT INVENTORY',
        'Store Product Inventory',
        $inventoryHeaderId,
        'Business Date: '
        . $businessDate
        . ' | Initial inventory automatically generated from validated deliveries.'
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
        "Today's product inventory has been created successfully."
    );
    header(
        'Location:view.php?id=' . $inventoryHeaderId
    );
    exit;

} catch (Exception $e) {
    $conn->rollback();
    setFlash(
        'danger',
        $e->getMessage()
    );
    header('Location:index.php');
    exit;
}