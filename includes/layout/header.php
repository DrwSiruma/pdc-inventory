<?php

/*
|--------------------------------------------------------------------------
| Master Header
|--------------------------------------------------------------------------
| Shared Header Layout
|--------------------------------------------------------------------------
*/

if (!isset($pageTitle)) {
    $pageTitle = SYSTEM_SHORT_NAME;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= SYSTEM_SHORT_NAME; ?> | <?= htmlspecialchars($pageTitle); ?></title>

    <!-- Favicon -->
    <link rel="icon"
          type="image/png"
          href="<?= BASE_URL ?>/assets/img/favicon.png">

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="<?= BASE_URL ?>/assets/vendor/bootstrap/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <!-- Layout CSS -->
    <link rel="stylesheet"
          href="<?= BASE_URL ?>/assets/css/layout.style.css">

    <?php
    /*
    |--------------------------------------------------------------------------
    | Module CSS
    |--------------------------------------------------------------------------
    | Automatically load CSS based on current role.
    |--------------------------------------------------------------------------
    */

    if(isset($_SESSION['role'])){

        switch($_SESSION['role']){

            case 'super_admin':
                echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/admin.style.css">';
                break;

            case 'accounting':
                echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/accounting.style.css">';
                break;

            case 'warehouse':
                echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/warehouse.style.css">';
                break;

            case 'store':
                echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/store.style.css">';
                break;

            case 'spectator':
                echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/css/spectator.style.css">';
                break;

        }

    }

    ?>

</head>

<body>

<div class="wrapper">