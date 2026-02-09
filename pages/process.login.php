<?php
include('../includes/connection.php');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and Password are required.";
        header("Location: login");
        exit();
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['fullname'] = $user['full_name'];
                    $_SESSION['username'] = $user['username'];

                    // Log the login activity
                    logAudit(
                    $conn,
                    $user['user_id'],
                    'LOGIN',
                    'Authentication',
                    null,
                    'User logged in successfully'
                    );

                    if ($user['role'] === 'super_admin') {
                        header("Location: admin/dashboard");
                    } elseif ($user['role'] === 'store') {
                        header("Location: outlet/dashboard");
                    } elseif ($user['role'] === 'warehouse') {
                        header("Location: warehouse/dashboard");
                    } elseif ($user['role'] === 'spectator') {
                        header("Location: spectator/dashboard");
                    }
                    exit();
                } else {
                    $_SESSION['error'] = "Incorrect password.";
                }
            } else {
                $_SESSION['error'] = "Username not found.";
            }

            $stmt->close();
        } else {
            $_SESSION['error'] = "Database error: " . $conn->error;
        }

        header("Location: login");
        exit();
    }
} else {
    header("Location: login");
    exit();
}