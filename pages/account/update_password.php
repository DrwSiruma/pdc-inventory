<?php

require_once '../../includes/config.php';
require_once '../../includes/connection.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/flash.php';
require_once '../../includes/auth.php';
require_once '../../includes/audit.php';

requireLogin();

$currentPassword = trim($_POST['current_password']);
$newPassword = trim($_POST['new_password']);
$confirmPassword = trim($_POST['confirm_password']);

if (
    empty($currentPassword) ||
    empty($newPassword) ||
    empty($confirmPassword)
) {

    setFlash(
        'danger',
        'Please complete all fields.'
    );

    header('Location: change_password.php');
    exit;

}

if ($newPassword != $confirmPassword) {

    setFlash(
        'danger',
        'Password confirmation does not match.'
    );

    header('Location: change_password.php');
    exit;

}

if (strlen($newPassword) < 8) {

    setFlash(
        'danger',
        'Password must be at least 8 characters.'
    );

    header('Location: change_password.php');
    exit;

}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT password_hash
    FROM users
    WHERE user_id = ?
");

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$user = $stmt
    ->get_result()
    ->fetch_assoc();

$stmt->close();

if (
    !password_verify(
        $currentPassword,
        $user['password_hash']
    )
) {

    setFlash(
        'danger',
        'Current password is incorrect.'
    );

    header('Location: change_password.php');
    exit;

}

$newHash = password_hash(
    $newPassword,
    PASSWORD_DEFAULT
);

$stmt = $conn->prepare("
    UPDATE users
    SET password_hash = ?
    WHERE user_id = ?
");

$stmt->bind_param(
    "si",
    $newHash,
    $user_id
);

$stmt->execute();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Audit Log
|--------------------------------------------------------------------------
*/

logAudit(

    'CHANGE PASSWORD',

    'Account',

    $user_id,

    'Changed account password.'

);

setFlash(

    'success',

    'Password updated successfully.'

);

header('Location: change_password.php');

exit;

?>