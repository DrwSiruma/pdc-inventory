<?php

/*
|--------------------------------------------------------------------------
| Store Permission Helper
|--------------------------------------------------------------------------
| Centralized store permission handler for Accounting users.
|
| Super Admin
|     - Can access all stores
|
| Accounting
|     - Can only access assigned stores
|
| Store
|     - Uses users.location_id
|
| Warehouse
|     - Uses users.location_id
|--------------------------------------------------------------------------
*/

if (!function_exists('getAssignedStoreIds')) {
    function getAssignedStoreIds(mysqli $conn, int $userId): array
    {
        $stores = [];
        $stmt = $conn->prepare("
            SELECT
                location_id
            FROM accounting_store_assignment
            WHERE user_id = ?
            ORDER BY location_id
        ");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $stores[] = (int)$row['location_id'];
        }
        $stmt->close();
        return $stores;
    }
}
if (!function_exists('getAccessibleStoreIds')) {
    function getAccessibleStoreIds(mysqli $conn): array
    {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        $userId = (int)$_SESSION['user_id'];
        $stmt = $conn->prepare("
            SELECT
                role,
                location_id
            FROM users
            WHERE user_id = ?
            LIMIT 1
        ");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$user) {
            return [];
        }
        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */
        if ($user['role'] == 'super_admin') {
            $stores = [];
            $query = $conn->query("
                SELECT
                    location_id
                FROM locations
                WHERE
                    location_type='store'
                    AND status='active'
                ORDER BY location_name
            ");
            while ($row = $query->fetch_assoc()) {
                $stores[] = (int)$row['location_id'];
            }
            return $stores;
        }
        /*
        |--------------------------------------------------------------------------
        | Accounting
        |--------------------------------------------------------------------------
        */
        if ($user['role'] == 'accounting') {
            return getAssignedStoreIds($conn, $userId);
        }
        /*
        |--------------------------------------------------------------------------
        | Store User
        |--------------------------------------------------------------------------
        */
        if ($user['role'] == 'store') {
            return [(int)$user['location_id']];
        }
        /*
        |--------------------------------------------------------------------------
        | Warehouse
        |--------------------------------------------------------------------------
        */
        if ($user['role'] == 'warehouse') {
            return [(int)$user['location_id']];
        }
        return [];
    }
}

if (!function_exists('buildStoreWhereClause')) {
    function buildStoreWhereClause(array $storeIds, string $column = 'location_id'): string
    {
        if (empty($storeIds)) {
            return " AND 1 = 0 ";
        }
        $ids = array_map('intval', $storeIds);
        return " AND {$column} IN (" . implode(',', $ids) . ") ";
    }
}

if (!function_exists('userCanAccessStore')) {
    function userCanAccessStore(mysqli $conn, int $locationId): bool
    {
        $stores = getAccessibleStoreIds($conn);

        return in_array($locationId, $stores);
    }
}

if (!function_exists('enforceStorePermission')) {
    function enforceStorePermission(mysqli $conn, int $locationId): void
    {
        if (!userCanAccessStore($conn, $locationId)) {
            setFlash(
                'danger',
                'You do not have permission to access this store.'
            );
            header('Location: ' . BASE_URL . '/pages/accounting/dashboard.php');
            exit;
        }
    }
}