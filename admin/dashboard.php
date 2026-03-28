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
// Total Feedbacks
$stmt_total = $conn->prepare("SELECT COUNT(*) FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?");
$stmt_total->bind_param("ss", $start_date, $end_date);
$stmt_total->execute();
$res_total = $stmt_total->get_result();
$total_feedbacks = $res_total->fetch_row()[0];

// Average Rating
$stmt_avg = $conn->prepare("SELECT ROUND(AVG(overall_rating),1) FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?");
$stmt_avg->bind_param("ss", $start_date, $end_date);
$stmt_avg->execute();
$res_avg = $stmt_avg->get_result();
$avg_rating = $res_avg->fetch_row()[0] ?: '0.0';

// Recent Count
$stmt_recent = $conn->prepare("SELECT COUNT(*) FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?");
$stmt_recent->bind_param("ss", $start_date, $end_date);
$stmt_recent->execute();
$res_recent = $stmt_recent->get_result();
$recent_count = $res_recent->fetch_row()[0];

/**
 * 3. Fetch Departmental & Category Scores
 */
$stmt_health = $conn->prepare("SELECT 
    AVG(frontdesk) as fd, 
    AVG(reservations) as res, 
    AVG(telephone_operator) as tel, 
    AVG(valet) as val, 
    AVG(housekeeping) as hk, 
    AVG(accommodation) as acc, 
    AVG(safety) as sf, 
    AVG(security) as sec, 
    AVG(food_quality) as fq, 
    AVG(serving_time) as st, 
    AVG(wait_staff) as ws, 
    AVG(grooming) as gr, 
    AVG(behavior) as bh, 
    AVG(overall_service) as svc, 
    AVG(bar) as bar, 
    AVG(bartender) as bt 
    FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?");
$stmt_health->bind_param("ss", $start_date, $end_date);
$stmt_health->execute();
$res_health = $stmt_health->get_result();
$dept_health = $res_health->fetch_assoc();

// 4. Fetch Recent Comments Feed
$stmt_feed = $conn->prepare("SELECT guest_name, overall_rating, submitted_at, other_comments FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ? ORDER BY submitted_at DESC LIMIT 5");
$stmt_feed->bind_param("ss", $start_date, $end_date);
$stmt_feed->execute();
$res_feed = $stmt_feed->get_result();
$recent_feedbacks = $res_feed->fetch_all(MYSQLI_ASSOC);

/**
 * Helper function to determine rating label and color
 * Based on a 3-point scale
 */
function getRatingStatus($score) {
    if ($score <= 0) return ['label' => 'No Data', 'color' => '#999', 'bg' => '#f0f0f0'];
    if ($score >= 2.5) return ['label' => 'Excellent', 'color' => 'white', 'bg' => '#2d4c31']; // Green
    if ($score >= 1.8) return ['label' => 'Good', 'color' => 'white', 'bg' => '#b5935b'];      // Gold
    return ['label' => 'Needs Improvement', 'color' => 'white', 'bg' => '#a33b3b'];          // Red
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Executive Dashboard - John Hay Hotels</title>
    
    <meta http-equiv="refresh" content="60"> 
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
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); 
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

        /* Updated Score Styling */
        .score-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f9f9f9;
        }

        .score-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--jh-text-brown);
        }

        .score-meta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .score-value {
            font-family: var(--font-header);
            font-weight: 800;
            font-size: 1rem;
            color: var(--jh-primary);
        }

        .score-badge {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            min-width: 80px;
            text-align: center;
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

        @media (max-width: 1024px) {
            #sidebar-wrapper { 
                position: fixed;
                left: -320px; 
                top: 0;
                height: 100vh;
                width: 320px !important; 
            }
            body.mobile-sidebar-active #sidebar-wrapper { transform: translateX(320px); }
            .main-content { padding: 6rem 1.5rem 2rem; width: 100%; }
            .mobile-toggle { display: block; }
            .dashboard-grid { grid-template-columns: 1fr; }
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
                <div class="stat-label">Feedbacks Found</div>
            </div>
            <div class="stat-card">
                <h3>Global Satisfaction</h3>
                <div class="stat-number"><?= $avg_rating ?></div>
                <div class="stat-label">Period Avg / 10.0</div>
            </div>
            <div class="stat-card">
                <h3>Period Activity</h3>
                <div class="stat-number"><?= number_format($recent_count) ?></div>
                <div class="stat-label">Responses in Period</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="panel">
                <h2>Reception & Rooms</h2>
                <p style="font-family: var(--font-header); font-size: 0.8rem; color: #888; margin-bottom: 2rem;">Average Score (Max: 3.0)</p>
                
                <?php 
                    $departments = [
                        'Front Desk' => $dept_health['fd'] ?? 0,
                        'Reservations' => $dept_health['res'] ?? 0,
                        'Telephone Operator' => $dept_health['tel'] ?? 0,
                        'Valet' => $dept_health['val'] ?? 0,
                        'Housekeeping' => $dept_health['hk'] ?? 0,
                        'Accommodation' => $dept_health['acc'] ?? 0,
                        'Safety' => $dept_health['sf'] ?? 0,
                        'Security' => $dept_health['sec'] ?? 0
                    ];

                    foreach ($departments as $name => $score):
                        $status = getRatingStatus($score);
                ?>
                <div class="score-item">
                    <span class="score-name"><?= $name ?></span>
                    <div class="score-meta">
                        <span class="score-value"><?= number_format($score, 1) ?></span>
                        <span class="score-badge" style="background: <?= $status['bg'] ?>; color: <?= $status['color'] ?>;">
                            <?= $status['label'] ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="panel">
                <h2>Service Quality</h2>
                <p style="font-family: var(--font-header); font-size: 0.8rem; color: #888; margin-bottom: 2rem;">Average Score (Max: 3.0)</p>
                
                <?php 
                    $categories = [
                        'Food Quality' => $dept_health['fq'] ?? 0,
                        'Serving Time' => $dept_health['st'] ?? 0,
                        'Wait Staff' => $dept_health['ws'] ?? 0,
                        'Grooming' => $dept_health['gr'] ?? 0,
                        'Behavior' => $dept_health['bh'] ?? 0,
                        'Overall Service' => $dept_health['svc'] ?? 0,
                        'Bar' => $dept_health['bar'] ?? 0,
                        'Bartender' => $dept_health['bt'] ?? 0
                    ];

                    foreach ($categories as $name => $score):
                        $status = getRatingStatus($score);
                ?>
                <div class="score-item">
                    <span class="score-name"><?= $name ?></span>
                    <div class="score-meta">
                        <span class="score-value"><?= number_format($score, 1) ?></span>
                        <span class="score-badge" style="background: <?= $status['bg'] ?>; color: <?= $status['color'] ?>;">
                            <?= $status['label'] ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="panel" style="grid-column: 1 / -1;">
                <h2>Recent Guest Activity</h2>
                <div style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <?php if (empty($recent_feedbacks)): ?>
                        <p style="color: #999; font-style: italic;">No feedback recorded for this period.</p>
                    <?php else: ?>
                        <?php foreach($recent_feedbacks as $row): ?>
                        <div class="feed-item" style="border: 1px solid #f0f0f0; padding: 1.5rem; border-radius: 8px;">
                            <div class="feed-info">
                                <strong><?= htmlspecialchars($row['guest_name'] ?: 'Anonymous Guest') ?></strong>
                                <span><?= date('M d, Y', strtotime($row['submitted_at'])) ?></span>
                                <p style="margin: 8px 0 0; font-size: 0.85rem; color: #555; font-style: italic; line-height: 1.5;">
                                    "<?= htmlspecialchars(mb_strimwidth($row['other_comments'] ?: 'No comment provided.', 0, 100, "...")) ?>"
                                </p>
                            </div>
                            <div class="rating-badge <?= $row['overall_rating'] >= 7.5 ? 'bg-green' : 'bg-gold' ?>">
                                <?= number_format($row['overall_rating'], 1) ?>/10
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <footer class="dashboard-footer">
            &copy; 2026 MIS Department, All Rights Reserved
        </footer>
    </div>

    <script>
        function toggleMobileMenu() { document.body.classList.toggle('mobile-sidebar-active'); }
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