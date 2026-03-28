<?php
session_start();
require_once '../config/db_connect.php';

// Security: Only 'manager' (or superadmin) can access this page
$user_role = isset($_SESSION['admin_role']) ? strtolower($_SESSION['admin_role']) : '';
if ($user_role !== 'manager' && $user_role !== 'superadmin') {
    header("Location: dashboard.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff'; 

    if (!empty($full_name) && !empty($username) && !empty($password)) {
        try {
            // MySQLi Check if user exists
            $check_sql = "SELECT id FROM admins WHERE username = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO admins (full_name, email, username, password, role, is_active) 
                        VALUES (?, ?, ?, ?, ?, 1)";
                $stmt = $conn->prepare($sql);
                // "sssss" means 5 strings
                $stmt->bind_param("sssss", $full_name, $email, $username, $hashed_password, $role);
                
                if ($stmt->execute()) {
                    $display_role = ($role === 'superadmin') ? 'Super Admin' : ucfirst($role);
                    $message = "New $display_role account created successfully!";
                } else {
                    $error = "Error: " . $conn->error;
                }
            }
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - John Hay Hotels</title>
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
            max-width: 600px; 
            width: 100%;
            margin: 0 auto;
        }

        h1 { font-family: 'Playfair Display', serif; color: var(--jh-primary); margin-bottom: 0.5rem; }
        
        .header-sub { 
            color: var(--jh-accent-gold); 
            margin-bottom: 2.5rem; 
            font-weight: 600; 
            letter-spacing: 1px; 
            text-align: center;
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

        .btn-save { 
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

        .btn-save:hover { background: var(--jh-accent-gold); }

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
            .main-content { 
                margin-left: 0; 
                padding: 1.5rem; 
            }
            .form-card {
                padding: 1.5rem;
            }
            h1 { font-size: 1.8rem; text-align: center; }
        }

        @media (max-width: 480px) {
            .header-sub { font-size: 0.8rem; }
            input, select { padding: 0.75rem; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div style="text-align: center;">
            <h1>New Admin Account</h1>
            <p class="header-sub">SYSTEM ACCESS AUTHORIZATION</p>
        </div>

        <div class="form-card">
            <?php if($message): ?> <div class="alert success"><i class="fas fa-check-circle"></i> <?= $message ?></div> <?php endif; ?>
            <?php if($error): ?> <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div> <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="Full Name">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Temporary Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Assigned Role</label>
                    <select name="role">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-save">REGISTER ACCOUNT</button>
            </form>
            <a href="manage_users.php" class="back-link">← RETURN TO MANAGEMENT</a>
        </div>
    </div>
</body>
</html>