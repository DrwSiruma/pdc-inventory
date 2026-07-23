<?php

/*
|--------------------------------------------------------------------------
| Activate / Deactivate User
|--------------------------------------------------------------------------
| Administrator - Toggle User Status
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
requireRole('super_admin');

/*
|--------------------------------------------------------------------------
| Validate Request
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    setFlash('danger', 'Invalid user selected.');

    header('Location: index.php');

    exit;

}

$user_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Protect Main Administrator
|--------------------------------------------------------------------------
*/

if ($user_id == 1) {

    setFlash('warning', 'The Main Administrator account cannot be modified.');

    header('Location: index.php');

    exit;

}

/*
|--------------------------------------------------------------------------
| Load User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    SELECT
        full_name,
        username,
        status

    FROM users

    WHERE user_id = ?

");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    setFlash('danger', 'User not found.');

    header('Location: index.php');

    exit;

}

$user = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Toggle Status
|--------------------------------------------------------------------------
*/

$new_status = ($user['status'] == 'active')
    ? 'inactive'
    : 'active';

/*
|--------------------------------------------------------------------------
| Update Status
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    UPDATE users

    SET status = ?

    WHERE user_id = ?

");

$stmt->bind_param(

    "si",

    $new_status,

    $user_id

);

if ($stmt->execute()) {

    if ($new_status == 'active') {

        logAudit(

            'ACTIVATE',

            'User Management',

            $user_id,

            'Activated user "' .
            $user['full_name'] .
            '" (' .
            $user['username'] .
            ').'

        );

        setFlash(

            'success',

            'User has been activated successfully.'

        );

    } else {

        logAudit(

            'DEACTIVATE',

            'User Management',

            $user_id,

            'Deactivated user "' .
            $user['full_name'] .
            '" (' .
            $user['username'] .
            ').'

        );

        setFlash(

            'success',

            'User has been deactivated successfully.'

        );

    }

} else {

    setFlash(

        'danger',

        'Unable to update user status.'

    );

}

$stmt->close();

header('Location: index.php');

exit;