<?php
session_start();
require_once '../config/db_connect.php';

// 1. Security Check: Only allow 'manager' or 'superadmin' role
$user_role = isset($_SESSION['admin_role']) ? strtolower($_SESSION['admin_role']) : '';
if ($user_role !== 'manager' && $user_role !== 'superadmin') {
    header("Location: dashboard.php");
    exit;
}

// 2. Fetch all admins using MySQLi
$query = "SELECT id, username, full_name, email, role, created_at, last_login, is_active FROM admins ORDER BY created_at DESC";
$result = $conn->query($query);

if (!$result) {
    die("System Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - John Hay Hotels</title>
    <link rel="icon" type="image/x-icon" href="../img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --jh-primary: #2d4c31;
            --jh-accent-gold: #b5935b;
            --jh-bg-beige: #f4eee1;
            --jh-border: #d1c1ad;
            --jh-text: #4a3c31;
            --sidebar-width: 320px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--jh-bg-beige);
            color: var(--jh-text);
            display: flex;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 3rem;
            min-height: 100vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            border-bottom: 2px solid var(--jh-border);
            padding-bottom: 1.5rem;
        }

        .header-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--jh-primary);
        }

        .header-title p {
            font-size: 0.9rem;
            color: var(--jh-accent-gold);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-create {
            background: var(--jh-primary);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 1px;
            border-radius: 4px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-create:hover {
            background: var(--jh-accent-gold);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            font-weight: 600;
        }
        .alert-success { background: #e8f0e9; color: #2d4c31; border-left: 5px solid #2d4c31; }
        .alert-danger { background: #f9e8e8; color: #a94442; border-left: 5px solid #a94442; }

        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: #f8f5f0;
            color: var(--jh-primary);
            padding: 1.2rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--jh-border);
        }

        td {
            padding: 1.2rem;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .role-badge {
            background: #e8f0e9;
            color: var(--jh-primary);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-active { color: #2d4c31; font-weight: 700; }
        .status-inactive { color: #a94442; font-weight: 700; }

        .action-links a {
            color: var(--jh-text);
            margin-right: 15px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: 0.2s;
        }

        .action-links a:hover { color: var(--jh-accent-gold); }
        .btn-delete:hover { color: #a94442 !important; }

        @media (max-width: 1024px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="header-title">
                <h1>Admin Management</h1>
                <p>Manage system users and access levels</p>
            </div>
            
            <a href="create_user.php" class="btn-create">
                <i class="fas fa-plus"></i> CREATE NEW ACCOUNT
            </a>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">Action completed successfully.</div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    if($_GET['error'] == 'protected_role') echo "Cannot delete a Manager or Superadmin account.";
                    elseif($_GET['error'] == 'self_delete') echo "You cannot delete your own account.";
                    else echo "An error occurred.";
                ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($admin = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: 700; color: var(--jh-accent-gold);">#<?= $admin['id'] ?></td>
                        <td><?= htmlspecialchars($admin['full_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($admin['username'] ?? '') ?></td>
                        <td><?= htmlspecialchars($admin['email'] ?? '') ?></td>
                        <td>
                            <span class="role-badge"><?= htmlspecialchars($admin['role'] ?? '') ?></span>
                        </td>
                        <td>
                            <?php if ($admin['is_active']): ?>
                                <span class="status-active"><i class="fas fa-check-circle"></i> Active</span>
                            <?php else: ?>
                                <span class="status-inactive"><i class="fas fa-times-circle"></i> Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 0.8rem; color: #888;">
                            <?= !empty($admin['last_login']) ? date('M d, Y h:i A', strtotime($admin['last_login'])) : 'Never' ?>
                        </td>
                        <td class="action-links">
                            <a href="edit_user.php?id=<?= $admin['id'] ?>" title="Edit User"><i class="fas fa-edit"></i></a>
                            
                            <?php 
                            $target_role = strtolower($admin['role'] ?? '');
                            // Prevent deleting self or other high-level admins
                            if ($admin['username'] !== $_SESSION['admin_username'] && $target_role !== 'manager' && $target_role !== 'superadmin'): 
                            ?>
                                <a href="delete_user.php?id=<?= $admin['id'] ?>" class="btn-delete" title="Delete User" 
                                   onclick="return confirm('Are you sure you want to delete this admin account?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>