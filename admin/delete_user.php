<?php
session_start();
require_once '../config/db_connect.php';

// 1. Security Check: Only 'manager' or 'superadmin' can perform deletions
$user_role = isset($_SESSION['admin_role']) ? strtolower($_SESSION['admin_role']) : '';
if ($user_role !== 'manager' && $user_role !== 'superadmin') {
    header("Location: dashboard.php");
    exit;
}

$admin_id = $_GET['id'] ?? null;

if ($admin_id) {
    try {
        // 2. Fetch the target user to check their role before deleting
        $stmt = $conn->prepare("SELECT role, username FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $target_user = $result->fetch_assoc();

        if ($target_user) {
            $target_role = strtolower($target_user['role']);

            // 3. Protection: Do not allow deleting yourself
            if ($target_user['username'] === $_SESSION['admin_username']) {
                header("Location: manage_users.php?error=self_delete");
                exit;
            }

            // 4. Protection: Do not allow deleting Managers or Superadmins
            if ($target_role === 'manager' || $target_role === 'superadmin') {
                header("Location: manage_users.php?error=protected_role");
                exit;
            }

            // 5. If all checks pass, delete the user
            $deleteStmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
            $deleteStmt->bind_param("i", $admin_id);
            $deleteStmt->execute();
            
            header("Location: manage_users.php?success=deleted");
            exit;
        }
    } catch (Exception $e) {
        die("System Error: " . $e->getMessage());
    }
}

header("Location: manage_users.php");
exit;
?>