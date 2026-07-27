<?php

/*
|--------------------------------------------------------------------------
| Update Beginning Inventory
|--------------------------------------------------------------------------
| Accounting - Update Beginning Inventory
|--------------------------------------------------------------------------
*/

require_once '../../../includes/config.php';
require_once '../../../includes/connection.php';
require_once '../../../includes/session.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/flash.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/audit.php';

requireLogin();
requireRole('accounting');

/*
|--------------------------------------------------------------------------
| Retrieve Form Data
|--------------------------------------------------------------------------
*/

$beginning_id = (int) $_POST['beginning_id'];

$inventory_date = trim($_POST['inventory_date']);

$product_id = (int) $_POST['product_id'];

$location_id = (int) $_POST['location_id'];

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

    $beginning_id <= 0 ||

    empty($inventory_date) ||

    $product_id <= 0 ||

    $location_id <= 0 ||

    $quantity < 0

) {

    setFlash(

        'danger',

        'Please complete all required fields.'

    );

    header('Location: edit.php?id=' . $beginning_id);

    exit;

}

/*
|--------------------------------------------------------------------------
| Check Duplicate Record
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    SELECT beginning_id

    FROM beginning_inventory

    WHERE inventory_date = ?

    AND product_id = ?

    AND location_id = ?

    AND beginning_id <> ?

    LIMIT 1

");

$stmt->bind_param(

    "siii",

    $inventory_date,

    $product_id,

    $location_id,

    $beginning_id

);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    setFlash(

        'danger',

        'Beginning inventory already exists for the selected date, product and location.'

    );

    header('Location: edit.php?id=' . $beginning_id);

    exit;

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Update Beginning Inventory
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    UPDATE beginning_inventory
    SET

        inventory_date = ?,
        product_id = ?,
        location_id = ?,
        quantity = ?,
        expiry_date = ?,
        remarks = ?

    WHERE beginning_id = ?

");

$stmt->bind_param(

    "siidssi",

    $inventory_date,

    $product_id,

    $location_id,

    $quantity,

    $expiry_date,

    $remarks,

    $beginning_id

);

if (!$stmt->execute()) {

    $stmt->close();

    setFlash(

        'danger',

        'Unable to update beginning inventory.'

    );

    header('Location: edit.php?id=' . $beginning_id);

    exit;

}

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

    'UPDATE',

    'Beginning Inventory',

    $beginning_id,

    'Updated beginning inventory for product: ' .

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

    'Beginning inventory has been updated successfully.'

);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: view.php?id=' . $beginning_id);

exit;

?>