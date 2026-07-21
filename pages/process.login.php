<?php

/*
|--------------------------------------------------------------------------
| Process Login
|--------------------------------------------------------------------------
| Authenticates the user and creates a secure session.
|--------------------------------------------------------------------------
*/

require_once '../includes/config.php';
require_once '../includes/connection.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/flash.php';
require_once '../includes/audit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/login.php');
}

/*
|--------------------------------------------------------------------------
| Get Login Credentials
|--------------------------------------------------------------------------
*/

$username = cleanInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {

    setFlash('error', 'Username and Password are required.');

    redirect('/pages/login.php');
}

/*
|--------------------------------------------------------------------------
| Find User
|--------------------------------------------------------------------------
*/

$sql = "SELECT *
        FROM users
        WHERE username = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    setFlash('error', 'Unable to connect to the database.');

    redirect('/pages/login.php');
}

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    setFlash('error', 'Invalid username or password.');

    redirect('/pages/login.php');
}

$user = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Check User Status
|--------------------------------------------------------------------------
*/

if ($user['status'] != STATUS_ACTIVE) {

    setFlash('error', 'Your account has been disabled.');

    redirect('/pages/login.php');
}

/*
|--------------------------------------------------------------------------
| Verify Password
|--------------------------------------------------------------------------
*/

if (!password_verify($password, $user['password_hash'])) {

    setFlash('error', 'Invalid username or password.');

    redirect('/pages/login.php');
}

/*
|--------------------------------------------------------------------------
| Regenerate Session ID
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);

/*
|--------------------------------------------------------------------------
| Store Session
|--------------------------------------------------------------------------
*/

$_SESSION['user_id']      = $user['user_id'];
$_SESSION['employee_no']  = $user['employee_no'];
$_SESSION['fullname']     = $user['full_name'];
$_SESSION['username']     = $user['username'];
$_SESSION['role']         = $user['role'];
$_SESSION['location_id']  = $user['location_id'];

/*
|--------------------------------------------------------------------------
| Update Last Login
|--------------------------------------------------------------------------
*/

$update = $conn->prepare("
    UPDATE users
    SET last_login = NOW()
    WHERE user_id = ?
");

if ($update) {

    $update->bind_param("i", $user['user_id']);
    $update->execute();
    $update->close();

}

/*
|--------------------------------------------------------------------------
| Audit Trail
|--------------------------------------------------------------------------
*/

logAudit(
    'LOGIN',
    'Authentication',
    null,
    'User logged into the system.'
);

/*
|--------------------------------------------------------------------------
| Redirect According to Role
|--------------------------------------------------------------------------
*/

switch ($user['role']) {

    case 'super_admin':
        redirect('/pages/admin/dashboard.php');
        break;

    case 'accounting':
        redirect('/pages/accounting/dashboard.php');
        break;

    case 'warehouse':
        redirect('/pages/warehouse/dashboard.php');
        break;

    case 'store':
        redirect('/pages/store/dashboard.php');
        break;

    case 'spectator':
        redirect('/pages/spectator/dashboard.php');
        break;

    default:

        session_unset();
        session_destroy();

        setFlash('error', 'Unauthorized account.');

        redirect('/pages/login.php');
        break;
}

$stmt->close();
$conn->close();

exit();