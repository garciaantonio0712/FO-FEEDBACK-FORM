<?php
session_start();
require_once '../config/db_connect.php';

// Security: Only 'manager' or 'superadmin' can access this page
$user_role = isset($_SESSION['admin_role']) ? strtolower($_SESSION['admin_role']) : '';
if ($user_role !== 'manager' && $user_role !== 'superadmin') {
    header("Location: dashboard.php");
    exit;
}

$message = '';
$error = '';
$admin_id = $_GET['id'] ?? null;

if (!$admin_id) {
    header("Location: manage_users.php");
    exit;
}

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: manage_users.php");
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? 'staff';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if (!empty($full_name) && !empty($username)) {
        // Update basic info
        $sql = "UPDATE admins SET full_name = ?, email = ?, username = ?, role = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $full_name, $email, $username, $role, $is_active, $admin_id);
        
        if ($stmt->execute()) {
            // Update password only if provided
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $passStmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $passStmt->bind_param("si", $hashed_password, $admin_id);
                $passStmt->execute();
            }

            $message = "Account for " . htmlspecialchars($full_name) . " updated successfully!";
            
            // Refresh local user data to show updated values in form
            $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Database Error: " . $conn->error;
        }
    } else {
        $error = "Full Name and Username are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin - John Hay Hotels</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600;700&display=swap');
        
        :root {
            --jh-primary: #2d4c31;
            --jh-accent-gold: #b5935b;
            --jh-bg-beige: #f4eee1;
            --jh-border: #d1c1ad;
            --sidebar-width: 320px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Montserrat', sans-serif; 
            background: var(--jh-bg-beige); 
            display: flex; 
            min-height: 100vh;
        }

        .main-content { 
            margin-left: var(--sidebar-width); 
            flex: 1; 
            padding: 3rem; 
            transition: all 0.3s ease;
        }

        .form-card { 
            background: white; 
            padding: 2.5rem; 
            border-radius: 8px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            max-width: 650px; 
            width: 100%;
            margin: 0 auto;
        }

        h1 { font-family: 'Playfair Display', serif; color: var(--jh-primary); margin-bottom: 0.5rem; text-align: center; }
        
        .header-sub { 
            color: var(--jh-accent-gold); 
            margin-bottom: 2.5rem; 
            font-weight: 600; 
            letter-spacing: 1px; 
            text-align: center;
            text-transform: uppercase;
        }

        .form-group { margin-bottom: 1.5rem; }
        
        label { 
            display: block; 
            margin-bottom: 0.5rem; 
            font-weight: 700; 
            font-size: 0.8rem; 
            color: var(--jh-primary); 
            text-transform: uppercase; 
        }

        input, select { 
            width: 100%; 
            padding: 0.9rem; 
            border: 1px solid var(--jh-border); 
            font-family: 'Montserrat', sans-serif; 
            border-radius: 4px; 
            font-size: 1rem;
        }

        .helper-text {
            font-size: 0.75rem;
            color: #888;
            margin-top: 0.4rem;
            font-style: italic;
        }

        .status-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1rem;
            background: #f9f6f0;
            border-radius: 4px;
            border: 1px solid var(--jh-border);
        }

        .status-toggle input { width: auto; }

        .btn-update { 
            background: var(--jh-primary); 
            color: white; 
            padding: 1.1rem; 
            border: none; 
            font-weight: 700; 
            cursor: pointer; 
            transition: 0.3s; 
            width: 100%; 
            letter-spacing: 2px; 
            margin-top: 1rem;
        }

        .btn-update:hover { background: var(--jh-accent-gold); }

        .alert { 
            padding: 1rem; 
            margin-bottom: 1.5rem; 
            border-radius: 4px; 
            font-weight: 600; 
            font-size: 0.85rem; 
        }
        .success { background: #e8f0e9; color: #2d4c31; border-left: 5px solid #2d4c31; }
        .error { background: #f9e8e8; color: #a94442; border-left: 5px solid #a94442; }

        .back-link {
            display: block; 
            text-align: center; 
            margin-top: 2rem; 
            color: var(--jh-accent-gold); 
            text-decoration: none; 
            font-size: 0.75rem; 
            font-weight: 700; 
            letter-spacing: 1px;
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
            .form-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <h1>Modify Admin Profile</h1>
        <p class="header-sub">Editing Account: <?= htmlspecialchars($user['username']) ?></p>

        <div class="form-card">
            <?php if($message): ?> <div class="alert success"><i class="fas fa-check-circle"></i> <?= $message ?></div> <?php endif; ?>
            <?php if($error): ?> <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div> <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Update Password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current password">
                    <p class="helper-text">Only fill this out if you wish to reset the password.</p>
                </div>

                <div class="form-group">
                    <label>Assigned Role</label>
                    <select name="role">
                        <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                        <option value="manager" <?= $user['role'] == 'manager' ? 'selected' : '' ?>>Manager</option>
                        <option value="superadmin" <?= $user['role'] == 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Account Status</label>
                    <div class="status-toggle">
                        <input type="checkbox" name="is_active" id="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
                        <label for="is_active" style="margin-bottom: 0; cursor: pointer;">This account is Active</label>
                    </div>
                </div>

                <button type="submit" class="btn-update">SAVE CHANGES</button>
            </form>
            <a href="manage_users.php" class="back-link">← DISCARD AND RETURN</a>
        </div>
    </div>
</body>
</html>