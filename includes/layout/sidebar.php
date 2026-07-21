<?php
switch ($_SESSION['role']) {

    case 'super_admin':
        include 'menus/admin.menu.php';
        break;

    case 'accounting':
        include 'menus/accounting.menu.php';
        break;

    case 'warehouse':
        include 'menus/warehouse.menu.php';
        break;

    case 'store':
        include 'menus/store.menu.php';
        break;

    case 'spectator':
        include 'menus/spectator.menu.php';
        break;
}