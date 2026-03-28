<style>
    :root {
        --jh-primary: #2d4c31;
        --jh-accent-gold: #b5935b;
        --jh-sidebar-bg: #eaddca;
        --jh-text-brown: #4a3c31;
        --jh-text-muted: #8e8379;
        --jh-border: #d1c1ad;
        --sidebar-width: 320px;
    }

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: var(--sidebar-width);
        background: var(--jh-sidebar-bg);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        border-right: 1px solid var(--jh-border);
        box-shadow: 2px 0 15px rgba(74, 60, 49, 0.1);
        font-family: 'Montserrat', sans-serif;
        transition: left 0.3s ease;
        overflow: hidden;
    }

    .sidebar-header { 
        padding: 4rem 1rem 2rem 1rem; 
        text-align: center; 
        position: relative; 
    }
    
    .sidebar-logo { 
        width: 240px; 
        height: auto; 
        margin-bottom: 1rem; 
        display: inline-block;
    }

    .sidebar-nav { flex: 1; margin-top: 1.5rem; }
    .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
    
    .sidebar-nav a {
        display: flex;
        align-items: center;
        padding: 1.2rem 1.5rem; 
        color: var(--jh-text-brown);
        text-decoration: none; 
        font-size: 0.85rem; 
        font-weight: 600;
        text-transform: uppercase; 
        letter-spacing: 1.5px; 
        transition: all 0.3s ease;
        border-right: 5px solid transparent;
        white-space: nowrap;
    }

    .sidebar-nav i {
        width: 50px;
        font-size: 1.1rem;
        text-align: center;
    }

    .sidebar-nav a:hover, .sidebar-nav a.active {
        background: rgba(45, 76, 49, 0.08); 
        color: var(--jh-primary); 
        border-right: 5px solid var(--jh-primary);
    }

    .nav-divider {
        height: 1px;
        background: var(--jh-border);
        margin: 1rem 2rem;
        opacity: 0.5;
    }

    .sidebar-footer { padding: 2.5rem; }
    .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 0.9rem; 
        border: 1px solid #a94442;
        color: #a94442; 
        text-decoration: none; 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.8rem;
    }

    @media (max-width: 1024px) {
        .sidebar { left: calc(-1 * var(--sidebar-width)); }
        body.mobile-sidebar-active .sidebar { left: 0; }
    }
</style>

<?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    $current = basename($_SERVER['PHP_SELF']);
    $is_in_stats = (basename(dirname($_SERVER['PHP_SELF'])) == 'stats');
    
    /** * Role Check Change: Now allows both 'manager' and 'superadmin'
     */
    $user_role = isset($_SESSION['admin_role']) ? strtolower($_SESSION['admin_role']) : '';
    $has_access = ($user_role === 'manager' || $user_role === 'superadmin');

    $logo_path = $is_in_stats ? '../../img/logo.png' : '../img/logo.png';
    $admin_link = $is_in_stats ? '../dashboard.php' : 'dashboard.php';
    $stats_link = $is_in_stats ? 'overall.php' : 'stats/overall.php';
    $records_link = $is_in_stats ? '../view_feedbacks.php' : 'view_feedbacks.php';
    $manage_users_link = $is_in_stats ? '../manage_users.php' : 'manage_users.php';
    $logout_link = $is_in_stats ? '../logout.php' : 'logout.php';
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <img src="<?= $logo_path ?>" alt="John Hay Hotels" class="sidebar-logo">
        <p class="admin-portal-text" style="font-size: 0.75rem; letter-spacing: 2.5px; color: var(--jh-text-muted); text-transform: uppercase; font-weight: 700; margin-top: 1rem;">
            Admin Portal
        </p>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?= $admin_link ?>" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?= $stats_link ?>" class="<?= $current === 'overall.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Overall Statistics</span>
                </a>
            </li>
            <li>
                <a href="<?= $records_link ?>" class="<?= $current === 'view_feedbacks.php' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-text">Guest Records</span>
                </a>
            </li>

            <?php if ($has_access): ?>
                <div class="nav-divider"></div>
                <li>
                    <a href="<?= $manage_users_link ?>" class="<?= ($current === 'manage_users.php' || $current === 'create_user.php') ? 'active' : '' ?>">
                        <i class="fas fa-user-shield"></i>
                        <span class="nav-text">Admin Management</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= $logout_link ?>" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>