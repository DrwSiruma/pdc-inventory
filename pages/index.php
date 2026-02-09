<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
//     include 'process.login.php';
//     exit();
// }

if (strpos($page, 'admin/') === 0) {
    $admin_page = str_replace('admin/', '', $page);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($admin_page) {
            case 'edit-profile':
                include 'admin/process.profile-edit.php';
                break;
            case 'update-adpassw':
                include 'admin/process.admin-passw.php';
                break;
            case 'add-user':
                include 'admin/process.register.php';
                break;
            case 'edit-user':
                include 'admin/process.edit.user.php';
                break;
            case 'update-userpassw':
                include 'admin/process.reset.php';
                break;
            case 'add-category':
                include 'admin/process.add-category.php';
                break;
            case 'edit-category':
                include 'admin/process.edit-categ.php';
                break;
            case 'add-itemlist':
                include 'admin/process.add-itemlist.php';
                break;
            case 'ticket-approval':
                include 'admin/process.ticket-approval.php';
                break;
            case 'reject-ticket':
                include 'admin/process.ticket-decline.php';
                break;
            case 'resched-ticket':
                include 'admin/process.resched-ticket.php';
                break;
            case 'reassign-ticket':
                include 'admin/process.reassign-ticket.php';
                break;
            case 'edit-tc':
                include 'admin/process.edit-tc.php';
                break;
            case 'edit-pl':
                include 'admin/process.edit-pl.php';
                break;
            case 'update-treport':
                include 'admin/process.update-report.php';
                break;
        }
    } else {
        switch ($admin_page) {
            case 'dashboard':
                include 'admin/admin.dashboard.php';
                break;
            case 'logout':
                include '../includes/logout.php';
                break;
            default:
                include 'admin/admin.dashboard.php';
                break;
        }
    }
} elseif (strpos($page, 'user/') === 0) {
    $user_page = str_replace('user/', '', $page);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($user_page) {
            case 'edit-profile':
                include 'user/process.profile-edit.php';
                break;
            case 'open-ticket':
                include 'user/process.open-ticket.php';
                break;
            case 'track-ticket':
                include 'user/process.track-ticket.php';
                break;
        }
    } else {
        switch ($user_page) {
            case 'dashboard':
                include 'user/user.dashboard.php';
                break;
            case 'open-ticket':
                include 'user/user.open-ticket.php';
                break;
            case 'ticket':
                include 'user/user.ticket.php';
                break;
            case 'track-ticket':
                include 'user/user.track-ticket.php';
                break;
            case 'my-ticket':
                include 'user/user.my-ticket.php';
                break;
            case 'ticket-history':
                include 'user/user.ticket-history.php';
                break;
            case 'generate-it-report':
                include 'user/user.generate-report-it.php';
                break;
            case 'generate-maintenance-report':
                include 'user/user.generate-report-mt.php';
                break;
            case 'edit-it-report':
                include 'user/user.edit-report-it.php';
                break;
            case 'edit-maintenance-report':
                include 'user/user.edit-report-mt.php';
                break;
            case 'conversations':
                include 'user/user.conversations.php';
                break;
            case 'notification':
                include 'user/user.notification.php';
                break;
            case 'notif-data':
                include 'user/user.notif-data.php';
                break;
            case 'mark-read':
                include 'user/process.mark-read.php';
                break;
            case 'mark-all-read':
                include 'user/process.mark-all-read.php';
                break;
            case 'jquery3.6':
                include '../assets/js/jquery-3.6.0.min.js';
                break;
            case 'logout':
                include '../includes/logout.php';
                break;
            default:
                include 'user/user.dashboard.php';
                break;
        }
    }
} elseif (strpos($page, 'staff/') === 0) {
    $staff_page = str_replace('staff/', '', $page);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($staff_page) {
            case 'update-treport':
            include 'staff/process.update-report.php';
            break;
        }
    } else {
        switch ($staff_page) {
            case 'dashboard':
                include 'staff/staff.dashboard.php';
                break;
            case 'notifications':
                include 'staff/staff.notifications.php';
                break;
            case 'tickets':
                include 'staff/staff.tickets.php';
                break;
            case 'view-ticket':
                include 'staff/staff.view-ticket.php';
                break;
            case 'edit-report-it':
                include 'staff/staff.edit-report-it.php';
                break;
            case 'edit-report-mt':
                include 'staff/staff.edit-report-mt.php';
                break;
            case 'generate-it-report':
                include 'staff/staff.generate-report.php';
                break;
            case 'generate-maintenance-report':
                include 'staff/staff.generate-report-m.php';
                break;
            case 'mark-read':
                include 'staff/process.mark-read.php';
                break;
            case 'mark-all-read':
                include 'staff/process.mark-all-read.php';
                break;
            case 'notif-check':
                include 'staff/staff.chk-notif.php';
                break;
            case 'notif-data':
                include 'staff/staff.notif-data.php';
                break;
            case 'ticket-check':
                include 'staff/staff.chk-ticket.php';
                break;
            case 'ticket-data':
                include 'staff/staff.ticket-data.php';
                break;
            case 'jquery3.6':
                include '../assets/js/jquery-3.6.0.min.js';
                break;
            case 'logout':
                include '../includes/logout.php';
                break;
            default:
                include 'staff/staff.dashboard.php';
                break;
        }
    }
} else {
    switch ($page) {
        case 'login':
            include 'login.php';
            break;
        case 'login_process':
            include 'process.login.php';
            break;
        default:
            include 'login.php';
            break;
    }
}
?>