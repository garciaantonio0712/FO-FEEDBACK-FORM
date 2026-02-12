<?php
require_once 'includes/auth.php';
require_once '../config/db_connect.php';

// 1. Handle Date Range for Stats
$range = $_GET['range'] ?? 'month'; 
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if (empty($start_date) || empty($end_date)) {
    switch ($range) {
        case 'today':
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
            break;
        case 'week':
            $start_date = date('Y-m-d', strtotime('-7 days'));
            $end_date = date('Y-m-d');
            break;
        case 'month':
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
            break;
        case 'year':
            $start_date = date('Y-01-01');
            $end_date = date('Y-12-31');
            break;
        default:
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
    }
}

// 2. Fetch Filtered Main Stats
$stmt_total = $pdo->prepare("SELECT COUNT(*) FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?");
$stmt_total->execute([$start_date, $end_date]);
$total_feedbacks = $stmt_total->fetchColumn();

$stmt_avg = $pdo->prepare("SELECT ROUND(AVG(overall_service),2) FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?");
$stmt_avg->execute([$start_date, $end_date]);
$avg_rating = $stmt_avg->fetchColumn() ?: '0.00';

$stmt_recent = $pdo->prepare("SELECT COUNT(*) FROM guest_feedbacks WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt_recent->execute();
$recent_count = $stmt_recent->fetchColumn();

// 3. Fetch Departmental "Health"
$dept_health = $pdo->query("SELECT AVG(frontdesk) as fd, AVG(housekeeping) as hk, AVG(fnb_service) as fnb FROM guest_feedbacks")->fetch(PDO::FETCH_ASSOC);

// 4. Fetch Recent Comments Feed
$recent_feedbacks = $pdo->query("SELECT guest_name, overall_service, submitted_at, other_comments FROM guest_feedbacks ORDER BY submitted_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Executive Dashboard - John Hay Hotels</title>
    
    <meta http-equiv="refresh" content="300"> 
    <link rel="icon" type="image/x-icon" href="../img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@700;800&family=Playfair+Display:wght@700&family=Montserrat:wght@400;600&display=swap');
        
        :root {
            --jh-primary: #2d4c31;
            --jh-accent-gold: #b5935b;
            --jh-bg-beige: #f4eee1;
            --jh-card-white: #ffffff;
            --jh-text-brown: #4a3c31;
            --jh-border: #d1c1ad;
            --sidebar-width: 320px;
            --font-header: 'Kumbh Sans', sans-serif;
        }

        body { 
            font-family: 'Montserrat', sans-serif; 
            background: var(--jh-bg-beige); 
            margin: 0; 
            display: flex; 
            color: var(--jh-text-brown); 
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        #sidebar-wrapper {
            width: var(--sidebar-width);
            flex-shrink: 0;
            transition: transform 0.3s ease;
            position: relative;
            z-index: 1000;
        }

        .main-content { 
            flex-grow: 1;
            padding: 2rem 3rem; 
            min-width: 0;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            margin-bottom: 2rem; 
            flex-wrap: wrap; 
            gap: 1rem; 
        }
        
        header h1 { 
            font-family: var(--font-header); 
            font-weight: 800; 
            color: var(--jh-primary); 
            font-size: 2.8rem; 
            margin: 0; 
        }

        .mobile-toggle {
            display: none;
            background: var(--jh-primary);
            color: white;
            border: none;
            padding: 12px 15px;
            font-size: 1.2rem;
            cursor: pointer;
            border-radius: 4px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .filter-section { 
            background: white; 
            padding: 1.5rem 2rem; 
            margin-bottom: 2rem; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.03); 
            border-radius: 8px; 
        }
        
        .filter-group { 
            display: flex; 
            align-items: center; 
            gap: 1.5rem; 
            flex-wrap: wrap; 
        }
        
        .quick-presets { 
            display: flex; 
            gap: 0.5rem; 
            border-right: 1px solid #eee; 
            padding-right: 1.5rem; 
            flex-wrap: wrap; 
        }
        
        .btn-preset { 
            text-decoration: none; 
            padding: 8px 16px; 
            font-size: 0.75rem; 
            font-family: var(--font-header);
            font-weight: 700; 
            color: var(--jh-text-brown); 
            border: 1px solid var(--jh-border); 
            border-radius: 20px;
            transition: 0.3s;
        }
        
        .btn-preset.active { 
            background: var(--jh-primary); 
            color: white; 
            border-color: var(--jh-primary); 
        }
        
        .btn-preset:hover:not(.active) { 
            background: #f9f9f9; 
            border-color: var(--jh-accent-gold); 
        }

        .custom-range { 
            display: flex; 
            align-items: center; 
            gap: 0.8rem; 
            flex-wrap: wrap; 
        }
        
        .custom-range label { 
            font-family: var(--font-header);
            font-size: 0.75rem; 
            font-weight: 700; 
            text-transform: uppercase; 
            color: var(--jh-accent-gold); 
        }
        
        .custom-range input { 
            border: 1px solid var(--jh-border); 
            padding: 8px 12px; 
            font-family: 'Montserrat'; 
            border-radius: 4px; 
            font-size: 0.85rem; 
        }
        
        .btn-filter { 
            background: var(--jh-primary); 
            color: white; 
            border: none; 
            padding: 9px 20px; 
            font-family: var(--font-header);
            font-weight: 700; 
            cursor: pointer; 
            border-radius: 4px; 
            font-size: 0.85rem; 
        }

        .stats-row { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 2rem; 
            margin-bottom: 3rem; 
        }
        
        .stat-card { 
            background: var(--jh-card-white); 
            padding: 2rem; 
            border-top: 5px solid var(--jh-accent-gold);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05); 
            text-align: center;
            border-radius: 4px;
        }
        
        .stat-card h3 { 
            font-family: var(--font-header);
            font-size: 0.85rem; 
            font-weight: 700;
            text-transform: uppercase; 
            letter-spacing: 2px; 
            color: #8e8379; 
            margin-bottom: 1rem; 
        }
        
        .stat-number { 
            font-family: var(--font-header); 
            font-size: 3.8rem; 
            color: var(--jh-primary); 
            font-weight: 800; 
            line-height: 1; 
            letter-spacing: -1px; 
            margin-bottom: 5px; 
        }
        
        .stat-label { 
            font-family: var(--font-header);
            font-size: 0.8rem; 
            color: var(--jh-accent-gold); 
            font-weight: 700; 
        }

        .dashboard-grid { 
            display: grid; 
            grid-template-columns: 2fr 1fr; 
            gap: 2rem; 
            flex-grow: 1;
        }
        
        .panel { 
            background: white; 
            padding: 2rem; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.05); 
            border-radius: 8px;
        }
        
        .panel h2 { 
            font-family: var(--font-header); 
            font-weight: 700;
            font-size: 1.6rem; 
            color: var(--jh-primary); 
            margin-top: 0; 
        }
        
        .feed-item { 
            padding: 1.2rem 0; 
            border-bottom: 1px solid #f0f0f0; 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            gap: 1rem; 
        }
        
        .feed-info strong { 
            display: block; 
            font-family: var(--font-header);
            font-weight: 700;
            color: var(--jh-primary); 
            font-size: 0.95rem; 
            margin-bottom: 3px;
        }
        
        .feed-info span { 
            font-size: 0.75rem; 
            color: #999; 
        }
        
        .rating-badge { 
            padding: 6px 14px; 
            border-radius: 20px; 
            font-family: var(--font-header);
            font-size: 0.75rem; 
            font-weight: 700; 
            color: white; 
            white-space: nowrap; 
        }
        
        .bg-gold { background: var(--jh-accent-gold); }
        .bg-green { background: var(--jh-primary); }

        .health-item { margin-bottom: 1.5rem; }
        .health-label { 
            display: flex; 
            justify-content: space-between; 
            font-family: var(--font-header);
            font-size: 0.85rem; 
            font-weight: 700; 
            margin-bottom: 0.5rem; 
        }
        
        .progress-bg { 
            background: #f0f0f0; 
            height: 10px; 
            border-radius: 5px; 
            overflow: hidden; 
        }
        
        .progress-fill { 
            background: var(--jh-primary); 
            height: 100%; 
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); 
        }

        .btn-action {
            display: inline-block; 
            padding: 12px 24px; 
            background: var(--jh-primary); 
            color: white;
            text-decoration: none; 
            font-family: var(--font-header);
            font-size: 0.8rem; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            border-radius: 4px;
            transition: 0.3s;
        }
        
        .btn-action:hover {
            background: #1e3321;
            transform: translateY(-2px);
        }

        .dashboard-footer {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--jh-border);
            text-align: center;
            color: #8e8379;
            font-family: var(--font-header);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 1024px) {
            #sidebar-wrapper { 
                position: fixed;
                left: -320px; /* Hide by default on mobile */
                top: 0;
                height: 100vh;
                width: 320px !important; 
            }
            
            body.mobile-sidebar-active #sidebar-wrapper {
                transform: translateX(320px);
            }

            .main-content { 
                padding: 6rem 1.5rem 2rem; 
                width: 100%;
            }
            
            .mobile-toggle { display: block; }
            .dashboard-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            header h1 { font-size: 2rem; }
            .quick-presets { border-right: none; padding-right: 0; margin-bottom: 1rem; width: 100%; }
            .filter-group { flex-direction: column; align-items: flex-start; }
            .stat-number { font-size: 3rem; }
        }
    </style>
</head>
<body>

    <button class="mobile-toggle" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
        <i class="fas fa-bars"></i>
    </button>

    <div id="sidebar-wrapper">
        <?php include 'includes/sidebar.php'; ?>
    </div>

    <div class="main-content">
        <header>
            <div>
                <h1>Dashboard</h1>
                <p style="font-family: var(--font-header); color: var(--jh-accent-gold); font-weight: 700; letter-spacing: 1px; margin-top: 5px;">
                    WELCOME, <?= strtoupper(htmlspecialchars($_SESSION['admin_username'] ?? 'ADMIN')) ?>
                </p>
            </div>
            <a href="stats/overall.php" class="btn-action">View Full Analytics</a>
        </header>

        <div class="filter-section">
            <div class="filter-group">
                <div class="quick-presets">
                    <a href="?range=today" class="btn-preset <?= $range == 'today' ? 'active' : '' ?>">Today</a>
                    <a href="?range=week" class="btn-preset <?= $range == 'week' ? 'active' : '' ?>">Last 7 Days</a>
                    <a href="?range=month" class="btn-preset <?= $range == 'month' ? 'active' : '' ?>">This Month</a>
                    <a href="?range=year" class="btn-preset <?= $range == 'year' ? 'active' : '' ?>">This Year</a>
                </div>
                
                <form method="GET" class="custom-range">
                    <label>Or Custom:</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
                    <button type="submit" class="btn-filter">Apply</button>
                </form>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <h3>Total Responses</h3>
                <div class="stat-number"><?= number_format($total_feedbacks) ?></div>
                <div class="stat-label">
                    <?php 
                        if(isset($_GET['start_date']) && !empty($_GET['start_date'])) echo "Custom Range";
                        elseif($range == 'month') echo "Current Month";
                        elseif($range == 'week') echo "Last 7 Days";
                        elseif($range == 'today') echo "Today's Total";
                        else echo "Full Year";
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Global Satisfaction</h3>
                <div class="stat-number"><?= $avg_rating ?></div>
                <div class="stat-label">Period Avg / 3.00</div>
            </div>
            <div class="stat-card">
                <h3>Recent Activity</h3>
                <div class="stat-number"><?= number_format($recent_count) ?></div>
                <div class="stat-label">Last 7 Days </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="panel">
                <h2>Recent Guest Activity</h2>
                <div style="margin-top: 1rem;">
                    <?php if (empty($recent_feedbacks)): ?>
                        <p style="color: #999; font-style: italic;">No feedback recorded for this period.</p>
                    <?php else: ?>
                        <?php foreach($recent_feedbacks as $row): ?>
                        <div class="feed-item">
                            <div class="feed-info">
                                <strong><?= htmlspecialchars($row['guest_name'] ?: 'Anonymous Guest') ?></strong>
                                <span><?= date('M d, Y', strtotime($row['submitted_at'])) ?></span>
                                <p style="margin: 8px 0 0; font-size: 0.85rem; color: #555; font-style: italic; line-height: 1.5;">
                                    "<?= htmlspecialchars(mb_strimwidth($row['other_comments'] ?: 'No comment provided.', 0, 100, "...")) ?>"
                                </p>
                            </div>
                            <div class="rating-badge <?= $row['overall_service'] >= 2.5 ? 'bg-green' : 'bg-gold' ?>">
                                <?= number_format($row['overall_service'], 1) ?>/3
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel">
                <h2>Department Health</h2>
                <p style="font-family: var(--font-header); font-size: 0.8rem; color: #888; margin-bottom: 2rem;">Real-time performance averages</p>
                
                <?php 
                    $departments = [
                        'Front Desk' => $dept_health['fd'] ?? 0,
                        'Housekeeping' => $dept_health['hk'] ?? 0,
                        'F&B Service' => $dept_health['fnb'] ?? 0
                    ];

                    foreach ($departments as $name => $score):
                        $percent = ($score / 3) * 100;
                ?>
                <div class="health-item">
                    <div class="health-label"><span><?= $name ?></span> <span><?= number_format($percent, 0) ?>%</span></div>
                    <div class="progress-bg"><div class="progress-fill" style="width: <?= $percent ?>%"></div></div>
                </div>
                <?php endforeach; ?>

                <div style="background: var(--jh-bg-beige); padding: 1.5rem; margin-top: 2rem; border-left: 4px solid var(--jh-primary); border-radius: 0 4px 4px 0;">
                    <p style="font-size: 0.8rem; margin: 0; line-height: 1.6; color: var(--jh-text-brown);">
                        <strong style="font-family: var(--font-header);">Admin Tip:</strong> Focus on departments below 80% to maintain John Hay Hotels' luxury standards.
                    </p>
                </div>
            </div>
        </div>

        <footer class="dashboard-footer">
            &copy; 2026 MIS Department, All Rights Reserved
        </footer>
    </div>

    <script>
        function toggleMobileMenu() {
            document.body.classList.toggle('mobile-sidebar-active');
        }

        window.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar-wrapper');
            const toggleBtn = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 1024 && document.body.classList.contains('mobile-sidebar-active')) {
                if (sidebar && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    document.body.classList.remove('mobile-sidebar-active');
                }
            }
        });
    </script>
</body>
</html>