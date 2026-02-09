<?php
include('connection.php');
session_start();

function logAudit($conn, $user_id, $action, $module, $reference_id = null, $description = '') {

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $sql = "INSERT INTO audit_logs 
            (user_id, action, module, reference_id, description, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Audit Log Error - Prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param(
        "ississ",
        $user_id,
        $action,
        $module,
        $reference_id,
        $description,
        $ip
    );

    if (!$stmt->execute()) {
        error_log("Audit Log Error - Execute failed: " . $stmt->error);
        return false;
    }

    $stmt->close();
    return true;
}

// Log logout BEFORE destroying session
if (isset($_SESSION['user_id'])) {
    logAudit(
        $conn,
        $_SESSION['user_id'],
        'LOGOUT',
        'Authentication',
        null,
        'User logged out successfully'
    );
}

session_unset();
session_destroy();

header("Location: /pdc-inventory/pages/login");
exit();
?>