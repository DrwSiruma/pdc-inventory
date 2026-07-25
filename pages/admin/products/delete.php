<?php

/*
|--------------------------------------------------------------------------
| Toggle Product Status
|--------------------------------------------------------------------------
| Administrator - Activate / Deactivate Product
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
| Validate Product ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    setFlash(
        'danger',
        'Invalid product selected.'
    );

    header('Location: index.php');

    exit;

}

$product_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        product_name,
        product_code,
        status
    FROM products
    WHERE product_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $product_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();

    setFlash(
        'danger',
        'Product not found.'
    );

    header('Location: index.php');

    exit;

}

$product = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Determine New Status
|--------------------------------------------------------------------------
*/

$new_status = ($product['status'] == 'active')
    ? 'inactive'
    : 'active';

/*
|--------------------------------------------------------------------------
| Update Product Status
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    UPDATE products
    SET status = ?
    WHERE product_id = ?
");

$stmt->bind_param(
    "si",
    $new_status,
    $product_id
);

if (!$stmt->execute()) {

    $stmt->close();

    setFlash(
        'danger',
        'Unable to update product status.'
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

if ($new_status == 'inactive') {

    logAudit(

        'DEACTIVATE',

        'Products',

        $product_id,

        'Deactivated product: ' .
        $product['product_name'] .
        ' (' . $product['product_code'] . ').'

    );

    setFlash(

        'success',

        'Product has been deactivated.'

    );

} else {

    logAudit(

        'ACTIVATE',

        'Products',

        $product_id,

        'Activated product: ' .
        $product['product_name'] .
        ' (' . $product['product_code'] . ').'

    );

    setFlash(

        'success',

        'Product has been activated.'

    );

}

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;

?>