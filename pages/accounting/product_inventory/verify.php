<?php

/*
|--------------------------------------------------------------------------
| Verify Product Inventory
|--------------------------------------------------------------------------
| Accounting Module
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

/*
|--------------------------------------------------------------------------
| Validate ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) || !is_numeric($_GET['id'])
) {
    setFlash(
        'danger',
        'Invalid inventory selected.'
    );
    header(
        'Location: index.php'
    );
    exit;
}
$inventory_header_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Check Inventory
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
    inventory_header_id,
    business_status,
    location_id,
    business_date
FROM
    product_inventory_header
WHERE
    inventory_header_id = ?
LIMIT 1
");
$stmt->bind_param(
    "i",
    $inventory_header_id
);
$stmt->execute();
$result = $stmt->get_result();
if (
    $result->num_rows == 0
) {
    $stmt->close();
    setFlash(
        'danger',
        'Inventory record not found.'
    );
    header(
        'Location: index.php'
    );
    exit;
}
$inventory = $result->fetch_assoc();
$stmt->close();
enforceStorePermission($conn, (int) $inventory['location_id']);

/*
|--------------------------------------------------------------------------
| Already Verified?
|--------------------------------------------------------------------------
*/

if (
    $inventory['business_status'] != 'submitted'
) {
    setFlash(
        'warning',
        'This inventory has already been processed.'
    );
    header(
        'Location: view.php?id=' . $inventory_header_id
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Start Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();
try {
    /*
    |--------------------------------------------------------------------------
    | Verify Inventory
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("

        UPDATE product_inventory_header

        SET

            business_status = 'verified',

            verified_by = ?,

            verified_at = NOW()

        WHERE

            inventory_header_id = ?

    ");

    $stmt->bind_param(

        "ii",

        $_SESSION['user_id'],

        $inventory_header_id

    );

    if (!$stmt->execute()) {

        throw new Exception(

            'Unable to verify inventory.'

        );

    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Audit Log
    |--------------------------------------------------------------------------
    */

    logAudit(

        $conn,

        $_SESSION['user_id'],

        'VERIFY PRODUCT INVENTORY',

        'Verified Product Inventory (' .

        $inventory['business_date'] .

        ')',

        'product_inventory_header',

        $inventory_header_id

    );

    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    setFlash(

        'success',

        'Product inventory has been verified successfully.'

    );

    header(

        'Location: view.php?id=' . $inventory_header_id

    );

    exit;

} catch (Exception $e) {

    $conn->rollback();
        /*
    |--------------------------------------------------------------------------
    | Rollback
    |--------------------------------------------------------------------------
    */

} catch (Exception $e) {

    $conn->rollback();

    setFlash(

        'danger',

        'Verification failed. ' . $e->getMessage()

    );

    header(

        'Location: view.php?id=' . $inventory_header_id

    );

    exit;

}

/*
|--------------------------------------------------------------------------
| Close Connection
|--------------------------------------------------------------------------
*/

$conn->close();

?>
