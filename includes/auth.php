<?php

/*
|--------------------------------------------------------------------------
| Authentication Manager
|--------------------------------------------------------------------------
| Handles Login Verification and Role Authorization
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/flash.php';

/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

function requireLogin()
{
    if (empty($_SESSION['user_id'])) {

        setFlash('error', 'Please login first.');

        redirect('/pages/login.php');
    }
}

/*
|--------------------------------------------------------------------------
| Check Role
|--------------------------------------------------------------------------
*/

function requireRole($roles)
{
    requireLogin();

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!in_array($_SESSION['role'], $roles)) {

        redirect('/pages/unauthorized.php');

    }
}

/*
|--------------------------------------------------------------------------
| Check if Logged In
|--------------------------------------------------------------------------
*/

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/*
|--------------------------------------------------------------------------
| Get Logged User
|--------------------------------------------------------------------------
*/

function currentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [

        'user_id'      => $_SESSION['user_id'],
        'employee_no'  => $_SESSION['employee_no'],
        'fullname'     => $_SESSION['fullname'],
        'username'     => $_SESSION['username'],
        'role'         => $_SESSION['role'],
        'location_id'  => $_SESSION['location_id']

    ];
}

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

function logoutUser()
{

    session_unset();

    session_destroy();

    redirect('/pages/login.php');

}