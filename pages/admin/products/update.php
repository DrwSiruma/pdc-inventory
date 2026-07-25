<?php

/*
|--------------------------------------------------------------------------
| Update Product
|--------------------------------------------------------------------------
| Administrator - Update Product Information
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
requireRole('super_admin');

/*
|--------------------------------------------------------------------------
| Retrieve Form Data
|--------------------------------------------------------------------------
*/

$product_id        = (int) $_POST['product_id'];
$product_code      = trim($_POST['product_code']);
$product_name      = trim($_POST['product_name']);
$category          = trim($_POST['category']);
$expiry_required   = (int) $_POST['expiry_required'];
$status            = trim($_POST['status']);

/*
|--------------------------------------------------------------------------
| Validate Required Fields
|--------------------------------------------------------------------------
*/

if (
    $product_id <= 0 ||
    empty($product_code) ||
    empty($product_name) ||
    empty($category)
) {

    setFlash(
        'danger',
        'Please complete all required fields.'
    );

    header('Location: edit.php?id=' . $product_id);

    exit;

}

/*
|--------------------------------------------------------------------------
| Check Duplicate Product Code
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT product_id
    FROM products
    WHERE product_code = ?
    AND product_id <> ?
    LIMIT 1
");

$stmt->bind_param(
    "si",
    $product_code,
    $product_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    setFlash(
        'danger',
        'Product Code already exists.'
    );

    header('Location: edit.php?id=' . $product_id);

    exit;

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Check Duplicate Product Name
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT product_id
    FROM products
    WHERE product_name = ?
    AND product_id <> ?
    LIMIT 1
");

$stmt->bind_param(
    "si",
    $product_name,
    $product_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    setFlash(
        'danger',
        'Product Name already exists.'
    );

    header('Location: edit.php?id=' . $product_id);

    exit;

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Update Product
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    UPDATE products
    SET
        product_code = ?,
        product_name = ?,
        category = ?,
        expiry_required = ?,
        status = ?
    WHERE product_id = ?
");

$stmt->bind_param(
    "sssisi",
    $product_code,
    $product_name,
    $category,
    $expiry_required,
    $status,
    $product_id
);

if (!$stmt->execute()) {

    $stmt->close();

    setFlash(
        'danger',
        'Unable to update product.'
    );

    header('Location: edit.php?id=' . $product_id);

    exit;

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Audit Trail
|--------------------------------------------------------------------------
*/

logAudit(

    'UPDATE',

    'Products',

    $product_id,

    'Updated product: ' .
    $product_name .
    ' (' . $product_code . ').'

);

/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

setFlash(

    'success',

    'Product updated successfully.'

);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;

?>