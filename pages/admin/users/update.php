<?php

/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
| Administrator - Update Existing User
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

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    header('Location: index.php');
    exit;

}

/*
|--------------------------------------------------------------------------
| Collect Data
|--------------------------------------------------------------------------
*/

$user_id      = (int) $_POST['user_id'];
$employee_no  = trim($_POST['employee_no']);
$full_name    = trim($_POST['full_name']);
$username     = trim($_POST['username']);
$email        = trim($_POST['email']);
$contact_no   = trim($_POST['contact_no']);
$role         = $_POST['role'];
$location_id  = !empty($_POST['location_id']) ? $_POST['location_id'] : NULL;
$status       = $_POST['status'];
$password     = trim($_POST['password']);

/*
|--------------------------------------------------------------------------
| Basic Validation
|--------------------------------------------------------------------------
*/

if (
    empty($employee_no) ||
    empty($full_name) ||
    empty($username)
) {

    setFlash('danger','Please complete all required fields.');

    header("Location: edit.php?id=".$user_id);

    exit;

}

/*
|--------------------------------------------------------------------------
| Check Duplicate Employee Number
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT user_id
    FROM users
    WHERE employee_no = ?
    AND user_id <> ?
");

$stmt->bind_param("si", $employee_no, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {

    setFlash('danger', 'Employee Number already exists.');

    header("Location: edit.php?id=".$user_id);

    exit;

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Check Duplicate Username
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT user_id
    FROM users
    WHERE username = ?
    AND user_id <> ?
");

$stmt->bind_param("si", $username, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {

    setFlash('danger', 'Username already exists.');

    header("Location: edit.php?id=".$user_id);

    exit;

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Protect Main Administrator
|--------------------------------------------------------------------------
*/

if ($user_id == 1) {

    $role = 'super_admin';

    $status = 'active';

    $stmt = $conn->prepare("
        SELECT location_id
        FROM users
        WHERE user_id = 1
    ");

    $stmt->execute();

    $stmt->bind_result($location_id);

    $stmt->fetch();

    $stmt->close();

}

/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
*/

if (!empty($password)) {

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE users
        SET
            employee_no=?,
            full_name=?,
            username=?,
            email=?,
            contact_no=?,
            role=?,
            location_id=?,
            status=?,
            password_hash=?
        WHERE user_id=?
    ");

    $stmt->bind_param(
        "ssssssissi",
        $employee_no,
        $full_name,
        $username,
        $email,
        $contact_no,
        $role,
        $location_id,
        $status,
        $password_hash,
        $user_id
    );

} else {

    $stmt = $conn->prepare("
        UPDATE users
        SET
            employee_no=?,
            full_name=?,
            username=?,
            email=?,
            contact_no=?,
            role=?,
            location_id=?,
            status=?
        WHERE user_id=?
    ");

    $stmt->bind_param(
        "ssssssisi",
        $employee_no,
        $full_name,
        $username,
        $email,
        $contact_no,
        $role,
        $location_id,
        $status,
        $user_id
    );

}

if ($stmt->execute()) {

    logAudit(

        'UPDATE',

        'User Management',

        $user_id,

        'Updated user: '.$full_name.' ('.$username.')'

    );

    setFlash('success', 'User updated successfully.');

} else {

    setFlash('danger', 'Failed to update user.');

}

$stmt->close();

header('Location: index.php');
exit;