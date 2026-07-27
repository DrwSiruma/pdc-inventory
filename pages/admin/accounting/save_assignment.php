<?php

/*
|--------------------------------------------------------------------------
| Save Accounting Store Assignment
|--------------------------------------------------------------------------
| Saves the assigned stores of an Accounting user.
|--------------------------------------------------------------------------
*/

require_once '../../../includes/config.php';
require_once '../../../includes/connection.php';
require_once '../../../includes/session.php';
require_once '../../../includes/no_cache.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/flash.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/audit.php';

requireLogin();
requireRole('super_admin');

/*
|--------------------------------------------------------------------------
| Validate Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit;
}
$userId = isset($_POST['user_id'])
    ? (int)$_POST['user_id']
    : 0;
$stores = $_POST['stores'] ?? [];
if ($userId <= 0) {
    setFlash('danger', 'Invalid Accounting user.');
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Verify User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        full_name
    FROM users
    WHERE
        user_id = ?
        AND role = 'accounting'
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) {
    setFlash('danger', 'Accounting user not found.');
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Begin Transaction
|--------------------------------------------------------------------------
*/
$conn->begin_transaction();
try {

    /*
    |--------------------------------------------------------------------------
    | Remove Existing Assignments
    |--------------------------------------------------------------------------
    */
    $delete = $conn->prepare("
        DELETE
        FROM accounting_store_assignment
        WHERE user_id = ?
    ");
    $delete->bind_param("i", $userId);
    if (!$delete->execute()) {
        throw new Exception('Unable to remove previous assignments.');
    }
    $delete->close();

    /*
    |--------------------------------------------------------------------------
    | Save New Assignments
    |--------------------------------------------------------------------------
    */
    if (!empty($stores)) {
        $insert = $conn->prepare("
            INSERT INTO accounting_store_assignment
            (
                user_id,
                location_id
            )
            VALUES
            (
                ?,
                ?
            )
        ");
        foreach ($stores as $locationId) {
            $locationId = (int)$locationId;
            $insert->bind_param(
                "ii",
                $userId,
                $locationId
            );
            if (!$insert->execute()) {
                throw new Exception('Unable to save assigned stores.');
            }
        }
        $insert->close();
    }

    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    */
    if (!logAudit(
        'UPDATE ACCOUNTING STORE ASSIGNMENT',
        'Accounting',
        $userId,
        'Updated assigned stores for ' . $user['full_name']
    )) {
        throw new Exception('Unable to write audit trail.');
    }
    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */
    $conn->commit();
    setFlash(
        'success',
        'Store assignments successfully updated.'
    );
} catch (Exception $e) {
    $conn->rollback();
    setFlash(
        'danger',
        $e->getMessage()
    );
}

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: assignments.php?user_id=' . $userId);
exit;