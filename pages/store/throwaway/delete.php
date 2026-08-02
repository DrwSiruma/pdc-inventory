<?php

/*
|--------------------------------------------------------------------------
| Delete Product Throw Away
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
$throwawayId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);
if (!$throwawayId) {
    setFlash(
        'danger',
        'Invalid throw away record.'
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
    | Load Throw Away Record
    |--------------------------------------------------------------------------
    */
    $stmt = $conn->prepare("
        SELECT
            t.throwaway_id,
            t.inventory_header_id,
            t.product_id,
            p.product_name,
            h.business_status
        FROM product_throwaway t
        INNER JOIN products p
            ON p.product_id = t.product_id
        INNER JOIN product_inventory_header h
            ON h.inventory_header_id =
               t.inventory_header_id
        WHERE
            t.throwaway_id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        throw new Exception(
            $conn->error
        );
    }
    $stmt->bind_param(
        "i",
        $throwawayId
    );
    $stmt->execute();
    $throwaway = $stmt
        ->get_result()
        ->fetch_assoc();
    $stmt->close();
    if (!$throwaway) {
        throw new Exception(
            'Throw Away record not found.'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Prevent Deletion
    |--------------------------------------------------------------------------
    */
    if ($throwaway['business_status'] != 'draft') {
        throw new Exception(
            'Submitted business day can no longer be modified.'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Delete Throw Away Record
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM product_throwaway
        WHERE
            throwaway_id = ?
    ");

    if (!$stmt) {

        throw new Exception(
            $conn->error
        );

    }

    $stmt->bind_param(
        "i",
        $throwawayId
    );

    if (!$stmt->execute()) {

        throw new Exception(
            'Unable to delete Throw Away record.'
        );

    }

    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */

    if (!logAudit(

        'DELETE PRODUCT THROW AWAY',

        'Store Product Throw Away',

        $throwawayId,

        'Deleted Throw Away record for product: '
        . $throwaway['product_name']

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

        'Throw Away record deleted successfully.'

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