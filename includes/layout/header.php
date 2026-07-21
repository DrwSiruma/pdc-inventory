<?php

/*
|--------------------------------------------------------------------------
| Master Header
|--------------------------------------------------------------------------
| Shared by all pages.
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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= SYSTEM_SHORT_NAME ?> | <?= htmlspecialchars($pageTitle) ?></title>

    <link rel="icon"
          type="image/png"
          href="<?= BASE_URL ?>/assets/img/favicon.png">

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="<?= BASE_URL ?>/assets/vendor/bootstrap/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css">

    <!-- Main Styles -->
    <link rel="stylesheet"
          href="<?= BASE_URL ?>/assets/css/main.style.css">

    <!-- Admin Styles -->
    <link rel="stylesheet"
          href="<?= BASE_URL ?>/assets/css/admin.style.css">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

</head>

<body>

<div class="wrapper">