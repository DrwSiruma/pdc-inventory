<?php

/*
|--------------------------------------------------------------------------
| Logout User
|--------------------------------------------------------------------------
| Terminates the current session and records the logout activity.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/audit.php';

/*
|--------------------------------------------------------------------------
| Record Logout Activity
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['user_id'])) {

    logAudit(
        'LOGOUT',
        'Authentication',
        null,
        'User logged out from the system.'
    );

}

/*
|--------------------------------------------------------------------------
| Destroy Session
|--------------------------------------------------------------------------
*/

session_unset();

session_destroy();

/*
|--------------------------------------------------------------------------
| Redirect to Login
|--------------------------------------------------------------------------
*/

redirect('/pages/login.php');

?>