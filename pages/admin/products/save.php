<?php

/*
|--------------------------------------------------------------------------
| Save Product
|--------------------------------------------------------------------------
| Administrator - Save New Product
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

$product_code     = trim($_POST['product_code']);
$product_name     = trim($_POST['product_name']);
$category         = trim($_POST['category']);
$expiry_required  = (int) $_POST['expiry_required'];
$status           = trim($_POST['status']);

/*
|--------------------------------------------------------------------------
| Required Field Validation
|--------------------------------------------------------------------------
*/

if (
    empty($product_code) ||
    empty($product_name) ||
    empty($category)
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
| Check Duplicate Product Code
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare(" SELECT product_id FROM products WHERE product_code = ? LIMIT 1");
$stmt->bind_param("s", $product_code);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $stmt->close();
    setFlash(
        'danger',
        'Product Code already exists.'
    );
    header('Location: add.php');
    exit;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Check Duplicate Product Name
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(" SELECT product_id FROM products WHERE product_name = ? LIMIT 1");
$stmt->bind_param("s", $product_name);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $stmt->close();
    setFlash(
        'danger',
        'Product Name already exists.'
    );
    header('Location: add.php');
    exit;
}
$stmt->close();
/*
|--------------------------------------------------------------------------
| Save Product
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    INSERT INTO products
    (
        product_code,
        product_name,
        category,
        expiry_required,
        status
    )
    VALUES
    (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssis",
    $product_code,
    $product_name,
    $category,
    $expiry_required,
    $status
);

if (!$stmt->execute()) {
    $stmt->close();
    setFlash(
        'danger',
        'Unable to save product.'
    );
    header('Location: add.php');
    exit;
}

$product_id = $stmt->insert_id;
$stmt->close();

/*
|--------------------------------------------------------------------------
| Audit Trail
|--------------------------------------------------------------------------
*/

logAudit(
    'ADD',

    'Products',

    $product_id,

    'Added product: ' .
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
    'Product added successfully.'
);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: index.php');
exit;
?>