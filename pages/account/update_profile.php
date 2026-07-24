<?php

/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/

require_once '../../includes/config.php';
require_once '../../includes/connection.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/flash.php';
require_once '../../includes/auth.php';
require_once '../../includes/audit.php';

requireLogin();

$user_id = $_SESSION['user_id'];

$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$contact_no = trim($_POST['contact_no']);

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (empty($full_name)) {

    setFlash(
        'danger',
        'Full Name is required.'
    );

    header('Location: profile.php');
    exit;

}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    setFlash(
        'danger',
        'Invalid email address.'
    );

    header('Location: profile.php');
    exit;

}

/*
|--------------------------------------------------------------------------
| Check Duplicate Email
|--------------------------------------------------------------------------
*/

if (!empty($email)) {

    $stmt = $conn->prepare("

        SELECT user_id

        FROM users

        WHERE email = ?

        AND user_id != ?

        LIMIT 1

    ");

    $stmt->bind_param(
        "si",
        $email,
        $user_id
    );

    $stmt->execute();

    $duplicate = $stmt->get_result();

    if ($duplicate->num_rows > 0) {

        $stmt->close();

        setFlash(
            'danger',
            'Email address is already being used by another user.'
        );

        header('Location: profile.php');
        exit;

    }

    $stmt->close();

}

/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    UPDATE users

    SET

        full_name = ?,
        email = ?,
        contact_no = ?

    WHERE user_id = ?

");

$stmt->bind_param(

    "sssi",

    $full_name,
    $email,
    $contact_no,
    $user_id

);

$stmt->execute();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Update Session
|--------------------------------------------------------------------------
*/

$_SESSION['fullname'] = $full_name;

/*
|--------------------------------------------------------------------------
| Refresh Session
|--------------------------------------------------------------------------
|
| Update session values so the changes are immediately reflected
| throughout the system (Topbar, Sidebar, etc.)
|
|--------------------------------------------------------------------------
*/

$_SESSION['fullname'] = $full_name;

if (!empty($email)) {

    $_SESSION['email'] = $email;

}

$_SESSION['contact_no'] = $contact_no;

/*
|--------------------------------------------------------------------------
| Audit Trail
|--------------------------------------------------------------------------
*/

logAudit(

    'UPDATE',

    'My Profile',

    $user_id,

    'Updated personal profile information.'

);

/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

setFlash(

    'success',

    'Your profile has been updated successfully.'

);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: profile.php');

exit;