<?php

/*
|--------------------------------------------------------------------------
| Activate / Deactivate Location
|--------------------------------------------------------------------------
| Administrator - Change Location Status
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
| Validate Location ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlash('danger', 'Invalid location selected.');
    header('Location: index.php');
    exit;
}

$location_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Location
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        location_name,
        location_code,
        status
    FROM locations
    WHERE location_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $location_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();

    setFlash('danger', 'Location not found.');

    header('Location: index.php');

    exit;
}

$location = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Determine New Status
|--------------------------------------------------------------------------
*/

$new_status = ($location['status'] == 'active')
    ? 'inactive'
    : 'active';

/*
|--------------------------------------------------------------------------
| Update Status
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    UPDATE locations
    SET status = ?
    WHERE location_id = ?
");

$stmt->bind_param(
    "si",
    $new_status,
    $location_id
);

if (!$stmt->execute()) {

    $stmt->close();

    setFlash(
        'danger',
        'Unable to update location status.'
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

if ($new_status == 'active') {

    logAudit(

        'ACTIVATE',

        'Locations',

        $location_id,

        'Activated location: ' .
        $location['location_name'] .
        ' (' . $location['location_code'] . ').'

    );

    setFlash(

        'success',

        'Location has been activated successfully.'

    );

} else {

    logAudit(

        'DEACTIVATE',

        'Locations',

        $location_id,

        'Deactivated location: ' .
        $location['location_name'] .
        ' (' . $location['location_code'] . ').'

    );

    setFlash(

        'success',

        'Location has been deactivated successfully.'

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