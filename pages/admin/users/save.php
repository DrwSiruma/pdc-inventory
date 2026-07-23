<?php

/*
|--------------------------------------------------------------------------
| Save User
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Retrieve Form Data
|--------------------------------------------------------------------------
*/

$employee_no     = trim($_POST['employee_no']);
$full_name       = trim($_POST['full_name']);
$username        = trim($_POST['username']);
$email           = trim($_POST['email']);
$contact_no      = trim($_POST['contact_no']);
$role            = trim($_POST['role']);
$location_id     = !empty($_POST['location_id']) ? $_POST['location_id'] : null;
$status          = trim($_POST['status']);
$password        = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];

/*
|--------------------------------------------------------------------------
| Validate Required Fields
|--------------------------------------------------------------------------
*/

if (
    empty($employee_no) ||
    empty($full_name) ||
    empty($username) ||
    empty($role) ||
    empty($password) ||
    empty($confirmPassword)
) {
    setFlash('danger', 'Please complete all required fields.');
    header("Location: add.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Confirm Password
|--------------------------------------------------------------------------
*/

if ($password !== $confirmPassword) {
    setFlash('danger', 'Passwords do not match.');
    header("Location: add.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check Duplicate Employee Number
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("SELECT user_id FROM users WHERE employee_no = ?");
$stmt->bind_param("s", $employee_no);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    setFlash('danger', 'Employee Number already exists.');
    header("Location: add.php");
    exit;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Check Duplicate Username
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    setFlash('danger', 'Username already exists.');
    header("Location: add.php");
    exit;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Check Duplicate Email
|--------------------------------------------------------------------------
*/

if (!empty($email)) {

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        setFlash('danger', 'Email address already exists.');
        header("Location: add.php");
        exit;
    }

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Encrypt Password
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

/*
|--------------------------------------------------------------------------
| Save User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    INSERT INTO users (
        employee_no,
        full_name,
        username,
        email,
        contact_no,
        password_hash,
        role,
        location_id,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Prepare Failed: " . $conn->error);
}

$stmt->bind_param(
    "sssssssis",
    $employee_no,
    $full_name,
    $username,
    $email,
    $contact_no,
    $passwordHash,
    $role,
    $location_id,
    $status
);

if ($stmt->execute()) {
    logAudit(

        'CREATE',

        'User Management',

        $conn->insert_id,

        'Created new user: '.$full_name.' ('.$username.')'

    );

    setFlash('success', 'User successfully added.');
} else {
    setFlash('danger', 'Unable to save user.');
}

$stmt->close();

header("Location: index.php");
exit;