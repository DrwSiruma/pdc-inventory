<?php

/*
|--------------------------------------------------------------------------
| Save Beginning Inventory
|--------------------------------------------------------------------------
| Accounting - Save Beginning Inventory
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
| Retrieve Form Data
|--------------------------------------------------------------------------
*/

$inventory_date = trim($_POST['inventory_date']);
$product_id = (int) $_POST['product_id'];
$location_id = (int) $_POST['location_id'];
enforceStorePermission($conn, $location_id);
$quantity = (float) $_POST['quantity'];
$expiry_date = !empty($_POST['expiry_date'])
    ? $_POST['expiry_date']
    : null;
$remarks = trim($_POST['remarks']);

/*
|--------------------------------------------------------------------------
| Validate Required Fields
|--------------------------------------------------------------------------
*/

if (
    empty($inventory_date) ||
    $product_id <= 0 ||
    $location_id <= 0 ||
    $quantity < 0
) {
    setFlash(
        'danger',
        'Please complete all required fields.'
    );
    header('Location: add.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Check Duplicate Beginning Inventory
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT beginning_id
    FROM beginning_inventory
    WHERE inventory_date = ?
    AND product_id = ?
    AND location_id = ?
    LIMIT 1
");
$stmt->bind_param(
    "sii",
    $inventory_date,
    $product_id,
    $location_id
);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $stmt->close();
    setFlash(
        'danger',
        'Beginning inventory already exists for the selected date, product and location.'
    );
    header('Location: add.php');
    exit;
}
$stmt->close();

/*
|--------------------------------------------------------------------------
| Save Beginning Inventory
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    INSERT INTO beginning_inventory
    (

        inventory_date,
        product_id,
        location_id,
        quantity,
        expiry_date,
        remarks,
        encoded_by

    )
    VALUES
    (?, ?, ?, ?, ?, ?, ?)

");

$user_id = $_SESSION['user_id'];

$stmt->bind_param(

    "siidssi",

    $inventory_date,

    $product_id,

    $location_id,

    $quantity,

    $expiry_date,

    $remarks,

    $user_id

);

if (!$stmt->execute()) {

    $stmt->close();

    setFlash(

        'danger',

        'Unable to save beginning inventory.'

    );

    header('Location: add.php');

    exit;

}

$beginning_id = $stmt->insert_id;

$stmt->close();

/*
|--------------------------------------------------------------------------
| Load Product Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        product_code,
        product_name
    FROM products
    WHERE product_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $product_id
);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Audit Trail
|--------------------------------------------------------------------------
*/

logAudit(
    'CREATE',
    'Beginning Inventory',
    $beginning_id,
    'Added beginning inventory for product: ' .
    $product['product_name'] .
    ' (' . $product['product_code'] . ').'
);

/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

setFlash(
    'success',
    'Beginning inventory has been saved successfully.'
);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: index.php');
exit;

?>
