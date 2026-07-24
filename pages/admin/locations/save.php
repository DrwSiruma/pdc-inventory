<?php

/*
|--------------------------------------------------------------------------
| Save Location
|--------------------------------------------------------------------------
| Administrator - Save New Location
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

    setFlash(

        'danger',

        'Please complete all required fields.'

    );

    header('Location: add.php');

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

    LIMIT 1

");

$stmt->bind_param(

    "s",

    $location_code

);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    setFlash(

        'danger',

        'Location Code already exists.'

    );

    header('Location: add.php');

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

    LIMIT 1

");

$stmt->bind_param(

    "s",

    $location_name

);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    setFlash(

        'danger',

        'Location Name already exists.'

    );

    header('Location: add.php');

    exit;

}

$stmt->close();
/*
|--------------------------------------------------------------------------
| Save Location
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    INSERT INTO locations
    (

        location_code,
        location_name,
        area,
        address,
        manager,
        location_type,
        status

    )

    VALUES
    (

        ?, ?, ?, ?, ?, ?, ?

    )

");

$stmt->bind_param(

    "sssssss",

    $location_code,
    $location_name,
    $area,
    $address,
    $manager,
    $location_type,
    $status

);

if (!$stmt->execute()) {

    $stmt->close();

    setFlash(

        'danger',

        'Failed to save location.'

    );

    header('Location: add.php');

    exit;

}

/*
|--------------------------------------------------------------------------
| Get Newly Created Location ID
|--------------------------------------------------------------------------
*/

$location_id = $conn->insert_id;

$stmt->close();

/*
|--------------------------------------------------------------------------
| Audit Trail
|--------------------------------------------------------------------------
*/

logAudit(

    'ADD',

    'Locations',

    $location_id,

    'Added new location: ' . $location_name .
    ' (' . $location_code . ').'

);

/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

setFlash(

    'success',

    'Location has been added successfully.'

);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;

?>