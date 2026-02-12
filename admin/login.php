<?php
session_start();
require_once '../config/db_connect.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_logged_in'] = true;
            header("Location: dashboard.php");
            exit;
        } else { $error = "Invalid credentials."; }
    } catch (PDOException $e) { $error = "System error."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - John Hay Hotels</title>
    <link rel="icon" type="image/x-icon" href="../img/icon.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;600;700&display=swap');
        :root {
            --jh-primary: #2d4c31;
            --jh-primary-dark: #1e3522;
            --jh-accent-gold: #b5935b;
            --jh-bg-beige: #f4eee1;
            --jh-border: #d1c1ad;
            --jh-danger: #a94442;
            --jh-text-light: #8c7e6d;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--jh-primary);
            background-image: url('https://www.transparenttextures.com/patterns/pinstriped-suit.png');
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: var(--jh-bg-beige);
            padding: 3rem 3rem 2rem 3rem; /* Adjusted padding for footer */
            width: 100%;
            max-width: 420px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            text-align: center;
            border-top: 8px solid var(--jh-accent-gold);
            position: relative;
        }
        h2 { font-family: 'Playfair Display', serif; color: var(--jh-primary); margin-bottom: 2rem; font-size: 2.2rem; }
        input {
            width: 100%;
            padding: 1.1rem;
            margin-bottom: 1.2rem;
            border: 1px solid var(--jh-border);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
        }
        .btn-login {
            width: 100%;
            padding: 1.2rem;
            background: var(--jh-primary);
            color: white;
            border: none;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-bottom: 2rem;
        }
        .btn-login:hover { background: var(--jh-primary-dark); }
        .error-msg { color: var(--jh-danger); font-size: 0.85rem; margin-bottom: 1.5rem; font-weight: 600; }
        
        /* Footer Style */
        .login-footer {
            font-size: 0.65rem;
            color: var(--jh-text-light);
            line-height: 1.5;
            border-top: 1px solid rgba(0,0,0,0.05);
            padding-top: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-box">
        <img src="../img/icon.png" alt="John Hay Hotels" style="width: 80px; margin-bottom: 1.5rem;">
        <h2>Admin Login</h2>
        
        <?php if($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="login-footer">
            &copy; 2026 MIS Department<br>
            All Rights Reserved
        </div>
    </div>
</body>
</html>