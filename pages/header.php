<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'super_admin') {
        header("Location: admin/dashboard");
        exit();
    } elseif ($_SESSION['role'] == 'outlet') {
        header("Location: outlet/dashboard");
        exit();
    } elseif ($_SESSION['role'] == 'warehouse') {
        header("Location: ../warehouse/dashboard");
        exit();
    } elseif ($_SESSION['role'] == 'spectator') {
        header("Location: ../spectator/dashboard");
        exit();
    }
}

// Retrieve any error message from the session
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> -->
  <title>Dunkin' - Inventory</title>
  <!-- Favicons -->
  <link href="../img/favicon.png" rel="icon">
  <link href="../img/favicon.png" rel="apple-touch-icon">
  <!-- Bootstrap 4 -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/fontawesome/css/all.min.css" rel="stylesheet">
  <link href="../assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- MAIN CSS -->
  <link href="../assets/css/login.style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center">