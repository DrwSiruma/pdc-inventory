<?php

/*
|--------------------------------------------------------------------------
| Flash Message Helper
|--------------------------------------------------------------------------
| Stores temporary messages in session.
|---------------------------------------------------------------------------
*/

require_once __DIR__ . '/session.php';

/*
|--------------------------------------------------------------------------
| Set Flash Message
|--------------------------------------------------------------------------
*/

function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/*
|--------------------------------------------------------------------------
| Display Flash Message
|--------------------------------------------------------------------------
*/

function showFlash()
{
    if (!isset($_SESSION['flash'])) {
        return;
    }

    $type = $_SESSION['flash']['type'];
    $message = $_SESSION['flash']['message'];

    $class = 'alert-info';

    switch ($type) {

        case 'success':
            $class = 'alert-success';
            break;

        case 'danger':
        case 'error':
            $class = 'alert-danger';
            break;

        case 'warning':
            $class = 'alert-warning';
            break;

        case 'info':
            $class = 'alert-info';
            break;
    }

    echo '
    <div class="alert '.$class.' alert-dismissible fade show shadow-sm" role="alert">
        '.$message.'
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
    ';

    unset($_SESSION['flash']);
}