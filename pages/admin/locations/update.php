<?php

/*
|--------------------------------------------------------------------------
| Update Location
|--------------------------------------------------------------------------
| Administrator - Update Location Information
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
| Validate Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}

$location_id   = (int) $_POST['location_id'];
$location_code = trim($_POST['location_code']);
$location_name = trim($_POST['location_name']);
$area          = trim($_POST['area']);
$address       = trim($_POST['address']);
$manager       = trim($_POST['manager']);
$location_type = trim($_POST['location_type']);
$status        = trim($_POST['status']);

/*
|--------------------------------------------------------------------------
| Required Field Validation
|--------------------------------------------------------------------------
*/

if (
    empty($location_code) ||
    empty($location_name) ||
    empty($location_type)
) {

    setFlash('danger', 'Please complete all required fields.');

    header('Location: edit.php?id=' . $location_id);

    exit;

}

/*
|--------------------------------------------------------------------------
| Check Duplicate Location Code
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT location_id
    FROM locations
    WHERE location_code = ?
    AND location_id != ?
    LIMIT 1
");

$stmt->bind_param(
    "si",
    $location_code,
    $location_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    setFlash('danger', 'Location Code already exists.');

    header('Location: edit.php?id=' . $location_id);

    exit;

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Check Duplicate Location Name
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT location_id
    FROM locations
    WHERE location_name = ?
    AND location_id != ?
    LIMIT 1
");

$stmt->bind_param(
    "si",
    $location_name,
    $location_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    setFlash('danger', 'Location Name already exists.');

    header('Location: edit.php?id=' . $location_id);

    exit;

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Update Location
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    UPDATE locations
    SET
        location_code = ?,
        location_name = ?,
        area = ?,
        address = ?,
        manager = ?,
        location_type = ?,
        status = ?
    WHERE location_id = ?
");

$stmt->bind_param(
    "sssssssi",
    $location_code,
    $location_name,
    $area,
    $address,
    $manager,
    $location_type,
    $status,
    $location_id
);

if (!$stmt->execute()) {

    $stmt->close();

    setFlash('danger', 'Failed to update location.');

    header('Location: edit.php?id=' . $location_id);

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
    'Locations',
    $location_id,
    'Updated location: ' . $location_name . ' (' . $location_code . ').'
);

/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

setFlash(
    'success',
    'Location updated successfully.'
);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;

?>