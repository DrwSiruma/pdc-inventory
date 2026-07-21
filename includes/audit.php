<?php

/*
|--------------------------------------------------------------------------
| Audit Trail Manager
|--------------------------------------------------------------------------
| Records all user activities throughout the system.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/connection.php';

/*
|--------------------------------------------------------------------------
| Detect Device Type
|--------------------------------------------------------------------------
*/

function getDeviceType()
{
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (preg_match('/tablet|ipad/i', $agent)) {
        return 'Tablet';
    }

    if (preg_match('/mobile|android|iphone/i', $agent)) {
        return 'Mobile';
    }

    return 'Desktop';
}

/*
|--------------------------------------------------------------------------
| Detect Operating System
|--------------------------------------------------------------------------
*/

function getOperatingSystem()
{
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (preg_match('/windows/i', $agent)) return 'Windows';
    if (preg_match('/android/i', $agent)) return 'Android';
    if (preg_match('/iphone|ipad/i', $agent)) return 'iOS';
    if (preg_match('/macintosh|mac os/i', $agent)) return 'MacOS';
    if (preg_match('/linux/i', $agent)) return 'Linux';

    return 'Unknown';
}

/*
|--------------------------------------------------------------------------
| Detect Browser
|--------------------------------------------------------------------------
*/

function getBrowser()
{
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (preg_match('/Edg/i', $agent)) return 'Microsoft Edge';
    if (preg_match('/Chrome/i', $agent)) return 'Google Chrome';
    if (preg_match('/Firefox/i', $agent)) return 'Mozilla Firefox';
    if (preg_match('/Safari/i', $agent)) return 'Safari';
    if (preg_match('/Opera|OPR/i', $agent)) return 'Opera';

    return 'Unknown';
}

/*
|--------------------------------------------------------------------------
| Log Audit
|--------------------------------------------------------------------------
|
| Example:
|
| logAudit(
|     'LOGIN',
|     'Authentication',
|     null,
|     'User logged into the system.'
| );
|
|--------------------------------------------------------------------------
*/

function logAudit($action, $module, $reference_id = null, $description = '')
{
    global $conn;

    if (!isset($conn)) {
        return false;
    }

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $user_id = (int) $_SESSION['user_id'];

    $location_id = isset($_SESSION['location_id'])
        ? (int) $_SESSION['location_id']
        : null;

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $browser = getBrowser();

    $operating_system = getOperatingSystem();

    $device_type = getDeviceType();

    $sql = "
        INSERT INTO audit_logs
        (
            user_id,
            location_id,
            action,
            module,
            reference_id,
            description,
            ip_address,
            browser,
            operating_system,
            device_type
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        error_log("Audit Prepare Failed : " . $conn->error);

        return false;

    }

    $stmt->bind_param(
        "iississsss",
        $user_id,
        $location_id,
        $action,
        $module,
        $reference_id,
        $description,
        $ip_address,
        $browser,
        $operating_system,
        $device_type
    );

    if (!$stmt->execute()) {

        error_log("Audit Execute Failed : " . $stmt->error);

        $stmt->close();

        return false;

    }

    $stmt->close();

    return true;
}