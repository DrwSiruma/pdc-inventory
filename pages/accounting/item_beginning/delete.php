<?php

/*
|--------------------------------------------------------------------------
| Delete Beginning Inventory
|--------------------------------------------------------------------------
| Accounting - Delete Beginning Inventory
|--------------------------------------------------------------------------
*/

require_once '../../../includes/config.php';
require_once '../../../includes/connection.php';
require_once '../../../includes/session.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/flash.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/audit.php';
require_once '../../../includes/store_permission.php';

requireLogin();
requireRole(['accounting', 'super_admin']);

/*
|--------------------------------------------------------------------------
| Validate Beginning Inventory ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    setFlash(

        'danger',

        'Invalid beginning inventory selected.'

    );

    header('Location: index.php');

    exit;

}

$beginning_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Beginning Inventory
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    SELECT

        b.beginning_id,
        p.product_code,
        p.product_name

    FROM beginning_inventory b

    INNER JOIN products p
        ON p.product_id = b.product_id

    WHERE b.beginning_id = ?

    LIMIT 1

");

$stmt->bind_param(

    "i",

    $beginning_id

);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();

    setFlash(

        'danger',

        'Beginning inventory record not found.'

    );

    header('Location: index.php');

    exit;

}

$inventory = $result->fetch_assoc();

$stmt->close();
enforceStorePermission($conn, (int) $inventory['location_id']);

/*
|--------------------------------------------------------------------------
| Delete Beginning Inventory
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    DELETE

    FROM beginning_inventory

    WHERE beginning_id = ?

");

$stmt->bind_param(

    "i",

    $beginning_id

);

if (!$stmt->execute()) {

    $stmt->close();

    setFlash(

        'danger',

        'Unable to delete beginning inventory.'

    );

    header('Location: index.php');

    exit;

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Audit Trail
|--------------------------------------------------------------------------
*/

logAudit(

    'DELETE',

    'Beginning Inventory',

    $beginning_id,

    'Deleted beginning inventory for product: ' .

    $inventory['product_name'] .

    ' (' . $inventory['product_code'] . ').'

);

/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

setFlash(

    'success',

    'Beginning inventory has been deleted successfully.'

);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;

?>
