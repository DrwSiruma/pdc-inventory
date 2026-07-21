<?php

/*
|--------------------------------------------------------------------------
| Admin Dashboard (Temporary Mockup)
|--------------------------------------------------------------------------
| Used for testing authentication, sessions and logout.
|--------------------------------------------------------------------------
*/

require_once '../../includes/config.php';
require_once '../../includes/connection.php';
require_once '../../includes/session.php';
require_once '../../includes/no_cache.php';
require_once '../../includes/functions.php';
require_once '../../includes/flash.php';
require_once '../../includes/auth.php';

requireRole('super_admin');

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>

        Admin Dashboard

    </title>

    <link rel="icon"
          href="../../assets/img/favicon.png">

    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css"
          rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap"
          rel="stylesheet">

    <style>

        body{

            background:#f4f5f9;

            font-family:'Rubik',sans-serif;

        }

        .card{

            border:none;

            border-radius:15px;

        }

        .profile-box{

            background:white;

            padding:30px;

            border-radius:15px;

            box-shadow:0 5px 15px rgba(0,0,0,.08);

        }

        .badge-role{

            font-size:15px;

            padding:8px 15px;

        }

        .info-table td{

            padding:8px;

        }

    </style>

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-9">

            <?php showFlash(); ?>

            <div class="profile-box">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h2 class="mb-1">

                            Welcome,

                            <?= htmlspecialchars($_SESSION['fullname']); ?>

                        </h2>

                        <small class="text-muted">

                            <?= SYSTEM_NAME ?>

                        </small>

                    </div>

                    <a href="../../includes/logout.php"
                       class="btn btn-danger">

                        <i class="bi bi-box-arrow-right"></i>

                        Logout

                    </a>

                </div>

                <hr>

                <div class="row">

                    <div class="col-md-6">

                        <table class="table info-table">

                            <tr>

                                <td width="170">

                                    <strong>User ID</strong>

                                </td>

                                <td>

                                    <?= $_SESSION['user_id']; ?>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <strong>Employee No</strong>

                                </td>

                                <td>

                                    <?= $_SESSION['employee_no']; ?>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <strong>Full Name</strong>

                                </td>

                                <td>

                                    <?= htmlspecialchars($_SESSION['fullname']); ?>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <strong>Username</strong>

                                </td>

                                <td>

                                    <?= htmlspecialchars($_SESSION['username']); ?>

                                </td>

                            </tr>

                        </table>

                    </div>

                    <div class="col-md-6">

                        <table class="table info-table">

                            <tr>

                                <td width="170">

                                    <strong>Role</strong>

                                </td>

                                <td>

                                    <span class="badge bg-success badge-role">

                                        <?= strtoupper($_SESSION['role']); ?>

                                    </span>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <strong>Location ID</strong>

                                </td>

                                <td>

                                    <?= $_SESSION['location_id']; ?>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <strong>Session ID</strong>

                                </td>

                                <td style="font-size:12px;">

                                    <?= session_id(); ?>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <strong>Server Time</strong>

                                </td>

                                <td>

                                    <?= date('F d, Y h:i:s A'); ?>

                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

                <hr>

                <div class="alert alert-success mb-0">

                    <h5>

                        <i class="bi bi-check-circle-fill"></i>

                        Authentication Successful

                    </h5>

                    <p class="mb-0">

                        If you can see this page,

                        your login,

                        session,

                        authorization,

                        and role validation are working correctly.

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>