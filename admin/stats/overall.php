<?php
require_once '../includes/auth.php';
require_once '../../config/db_connect.php';

// 1. Handle Date Range Presets
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

// 2. Fetch Overall Satisfaction Trend (MySQLi version)
$overall_trend_query = "SELECT DATE(submitted_at) as date, AVG(overall_rating) as avg_rating 
                        FROM guest_feedbacks 
                        WHERE DATE(submitted_at) BETWEEN ? AND ? 
                        GROUP BY DATE(submitted_at) 
                        ORDER BY DATE(submitted_at) ASC";
$stmt_trend = $conn->prepare($overall_trend_query);
$stmt_trend->bind_param("ss", $start_date, $end_date);
$stmt_trend->execute();
$trend_results = $stmt_trend->get_result()->fetch_all(MYSQLI_ASSOC);

$trend_labels = [];
$trend_values = [];
if (count($trend_results) > 0) {
    foreach ($trend_results as $row) {
        $trend_labels[] = date('M d', strtotime($row['date']));
        $trend_values[] = round($row['avg_rating'], 2);
    }
    $total_avg = array_sum($trend_values) / count($trend_values);
} else {
    $total_avg = 0;
}

// Logic for Badge Color
$badge_class = 'badge-good';
$status_text = 'Good';
if ($total_avg >= 9) { $badge_class = 'badge-excellent'; $status_text = 'Excellent'; }
elseif ($total_avg < 7) { $badge_class = 'badge-needs-improvement'; $status_text = 'Needs Work'; }

// 3. Fetch Detailed Front of House Stats
$foh_query = "SELECT 
    AVG(frontdesk) as fd, 
    AVG(reservations) as res, 
    AVG(telephone_operator) as tel, 
    AVG(valet) as val, 
    AVG(housekeeping) as hk, 
    AVG(accommodation) as acc, 
    AVG(safety) as sft, 
    AVG(security) as sec, 
    AVG(overall_service) as ov 
    FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?";
$stmt_foh = $conn->prepare($foh_query);
$stmt_foh->bind_param("ss", $start_date, $end_date);
$stmt_foh->execute();
$foh_stats = $stmt_foh->get_result()->fetch_assoc();

// 4. Fetch Detailed F&B Stats
$fnb_query = "SELECT 
    AVG(food_quality) as fq, 
    AVG(serving_time) as st, 
    AVG(wait_staff) as ws, 
    AVG(grooming) as gro, 
    AVG(behavior) as beh, 
    AVG(fnb_service) as ser, 
    AVG(bar) as br, 
    AVG(bartender) as bt 
    FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?";
$stmt_fnb = $conn->prepare($fnb_query);
$stmt_fnb->bind_param("ss", $start_date, $end_date);
$stmt_fnb->execute();
$fnb_stats = $stmt_fnb->get_result()->fetch_assoc();

// 5. Fetch First Stay Distribution
$first_stay_query = "SELECT first_stay, COUNT(*) as count FROM guest_feedbacks WHERE first_stay IS NOT NULL AND DATE(submitted_at) BETWEEN ? AND ? GROUP BY first_stay";
$stmt_stay = $conn->prepare($first_stay_query);
$stmt_stay->bind_param("ss", $start_date, $end_date);
$stmt_stay->execute();
$stay_res = $stmt_stay->get_result();
$first_stay_data = [];
while($row = $stay_res->fetch_assoc()) {
    $first_stay_data[$row['first_stay']] = $row['count'];
}

// 6. Fetch Purpose of Stay Distribution
$purpose_query = "SELECT purpose_of_stay, COUNT(*) as count FROM guest_feedbacks WHERE purpose_of_stay IS NOT NULL AND DATE(submitted_at) BETWEEN ? AND ? GROUP BY purpose_of_stay";
$stmt_purpose = $conn->prepare($purpose_query);
$stmt_purpose->bind_param("ss", $start_date, $end_date);
$stmt_purpose->execute();
$purp_res = $stmt_purpose->get_result();
$purpose_data = [];
while($row = $purp_res->fetch_assoc()) {
    $purpose_data[$row['purpose_of_stay']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overall Statistics - John Hay Hotels</title>
    <meta http-equiv="refresh" content="300">
    <link rel="icon" type="image/x-icon" href="../../img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@700;800;900&family=Montserrat:wght@400;600;700&display=swap');
        
        :root {
            --jh-primary: #2d4c31;
            --jh-accent-gold: #b5935b;
            --jh-bg-beige: #f4eee1;
            --jh-card-white: #ffffff;
            --jh-text-brown: #4a3c31;
            --jh-border: #d1c1ad;
            --sidebar-width: 320px;
            --sidebar-collapsed-width: 80px;
            --success-green: #27ae60;
            --warning-orange: #e67e22;
            --danger-red: #c0392b;
        }

        * { box-sizing: border-box; }

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
            transition: all 0.3s ease;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 1000;
            background: var(--jh-card-white);
            border-right: 1px solid var(--jh-border);
        }

        .main-content { 
            flex: 1;
            padding: 1.5rem 2rem; 
            transition: all 0.3s ease;
            min-width: 0;
            width: 100%;
            display: flex;
            flex-direction: column;
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
        }

        header { margin-bottom: 1.5rem; }
        header h1 { 
            font-family: 'Kumbh Sans', sans-serif; 
            font-weight: 900; 
            color: var(--jh-primary); 
            font-size: clamp(1.8rem, 5vw, 2.8rem); 
            margin: 0; 
            letter-spacing: -1px;
        }

        .subtitle { 
            font-family: 'Kumbh Sans', sans-serif;
            font-weight: 800;
            color: var(--jh-accent-gold); 
            text-transform: uppercase; 
            letter-spacing: 3px; 
            font-size: 0.85rem; 
            margin-bottom: 2px; 
        }

        .filter-section { background: white; padding: 1rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-radius: 8px; }
        .filter-group { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .quick-presets { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        .btn-preset { text-decoration: none; padding: 6px 12px; font-size: 0.7rem; font-weight: 700; color: var(--jh-text-brown); border: 1px solid var(--jh-border); border-radius: 20px; transition: 0.3s; }
        .btn-preset.active { background: var(--jh-primary); color: white; border-color: var(--jh-primary); }
        .custom-range { display: flex; align-items: center; gap: 0.5rem; }
        .custom-range input { border: 1px solid var(--jh-border); padding: 6px 10px; font-family: 'Montserrat'; border-radius: 4px; font-size: 0.8rem; }
        .btn-filter { background: var(--jh-primary); color: white; border: none; padding: 7px 15px; font-weight: 800; cursor: pointer; border-radius: 4px; font-size: 0.8rem; }

        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 1.2rem; 
        }

        .chart-card { background: var(--jh-card-white); padding: 1.2rem; box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-radius: 8px; position: relative; }
        .chart-card.full-width { grid-column: span 2; }
        
        .chart-card h3 { 
            font-family: 'Kumbh Sans', sans-serif;
            font-weight: 800;
            font-size: 0.9rem; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            margin-bottom: 1.2rem; 
            color: #8e8379;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-card h3 i { color: var(--jh-accent-gold); }

        .satisfaction-score-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .score-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .score-value { font-size: 1.8rem; font-weight: 900; line-height: 1; }
        .score-label { font-size: 0.6rem; text-transform: uppercase; font-weight: 700; }

        .badge-excellent { background: var(--success-green); }
        .badge-good { background: var(--jh-accent-gold); }
        .badge-needs-improvement { background: var(--danger-red); }

        .score-info h2 { margin: 0; font-family: 'Kumbh Sans'; color: var(--jh-primary); }
        .score-info p { margin: 0; font-size: 0.85rem; font-weight: 600; opacity: 0.7; }

        .chart-container { position: relative; height: 320px; width: 100%; }
        .chart-container.large { height: 420px; }

        .dashboard-footer {
            margin-top: 3rem;
            padding: 1.5rem 0;
            border-top: 1px solid var(--jh-border);
            text-align: center;
            color: #8e8379;
            font-size: 0.75rem;
            font-weight: 700;
        }

        @media (max-width: 1024px) {
            #sidebar-wrapper { 
                position: fixed;
                left: -100%; 
                top: 0;
                height: 100vh;
                display: block; 
                z-index: 2000;
            }
            body.mobile-sidebar-active #sidebar-wrapper {
                left: 0;
            }
            .main-content { padding: 5rem 1rem 1.5rem; }
            .mobile-toggle { display: block; }
            .stats-grid { grid-template-columns: 1fr; }
            .chart-card.full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <div id="sidebar-wrapper">
        <?php include '../includes/sidebar.php'; ?>
    </div>

    <div class="main-content">
        <header>
            <p class="subtitle">Experience Metrics</p>
            <h1>Overall Statistics</h1>
        </header>

        <div class="filter-section">
            <div class="filter-group">
                <div class="quick-presets">
                    <a href="?range=today" class="btn-preset <?= $range == 'today' ? 'active' : '' ?>">Today</a>
                    <a href="?range=week" class="btn-preset <?= $range == 'week' ? 'active' : '' ?>">Week</a>
                    <a href="?range=month" class="btn-preset <?= $range == 'month' ? 'active' : '' ?>">Month</a>
                    <a href="?range=year" class="btn-preset <?= $range == 'year' ? 'active' : '' ?>">Year</a>
                </div>
                <form method="GET" class="custom-range">
                    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
                    <button type="submit" class="btn-filter">Update</button>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="chart-card full-width">
                <div class="satisfaction-score-container">
                    <div class="score-circle <?= $badge_class ?>">
                        <span class="score-value"><?= number_format($total_avg, 1) ?></span>
                        <span class="score-label">Avg Rating</span>
                    </div>
                    <div class="score-info">
                        <h2>Overall Satisfaction Trend</h2>
                        <p>Currently performing at <span style="color: <?= ($total_avg >= 9) ? 'var(--success-green)' : 'var(--jh-accent-gold)' ?>"><?= $status_text ?></span> levels.</p>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="overallTrendChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3><i class="fas fa-concierge-bell"></i> Front of House Performance</h3>
                <div class="chart-container large"><canvas id="fohChart"></canvas></div>
            </div>

            <div class="chart-card">
                <h3><i class="fas fa-utensils"></i> Food & Beverage Performance</h3>
                <div class="chart-container large"><canvas id="fnbChart"></canvas></div>
            </div>

            <div class="chart-card">
                <h3><i class="fas fa-users"></i> Guest Retention</h3>
                <div class="chart-container"><canvas id="stayDoughnut"></canvas></div>
            </div>

            <div class="chart-card">
                <h3><i class="fas fa-briefcase"></i> Purpose of Stay</h3>
                <div class="chart-container"><canvas id="purposeChart"></canvas></div>
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

        Chart.defaults.font.family = "'Montserrat', sans-serif";
        Chart.defaults.color = '#4a3c31';
        Chart.defaults.font.weight = '600';

        // 1. Overall Trend
        const ctxTrend = document.getElementById('overallTrendChart').getContext('2d');
        const gradient = ctxTrend.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(45, 76, 49, 0.4)');
        gradient.addColorStop(1, 'rgba(45, 76, 49, 0)');

        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: <?= json_encode($trend_labels) ?>,
                datasets: [{
                    label: 'Rating',
                    data: <?= json_encode($trend_values) ?>,
                    borderColor: '#2d4c31',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#b5935b',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 1, max: 10, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. FOH Horizontal Bar
        new Chart(document.getElementById('fohChart'), {
            type: 'bar',
            data: {
                labels: ['Front Desk', 'Reservations', 'Telephone', 'Valet', 'Housekeeping', 'Accommodation', 'Safety', 'Security', 'Overall Service'],
                datasets: [{
                    data: [
                        <?= number_format($foh_stats['fd'] ?? 0, 2) ?>, 
                        <?= number_format($foh_stats['res'] ?? 0, 2) ?>, 
                        <?= number_format($foh_stats['tel'] ?? 0, 2) ?>, 
                        <?= number_format($foh_stats['val'] ?? 0, 2) ?>, 
                        <?= number_format($foh_stats['hk'] ?? 0, 2) ?>, 
                        <?= number_format($foh_stats['acc'] ?? 0, 2) ?>, 
                        <?= number_format($foh_stats['sft'] ?? 0, 2) ?>, 
                        <?= number_format($foh_stats['sec'] ?? 0, 2) ?>, 
                        <?= number_format($foh_stats['ov'] ?? 0, 2) ?>
                    ],
                    backgroundColor: '#2d4c31',
                    borderRadius: 5
                }]
            },
            options: { 
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, max: 3 } }
            }
        });

        // 3. F&B Horizontal Bar
        new Chart(document.getElementById('fnbChart'), {
            type: 'bar',
            data: {
                labels: ['Food Quality', 'Serving Time', 'Wait Staff', 'Grooming', 'Behavior', 'Service', 'Bar', 'Bartender'],
                datasets: [{
                    data: [
                        <?= number_format($fnb_stats['fq'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_stats['st'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_stats['ws'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_stats['gro'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_stats['beh'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_stats['ser'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_stats['br'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_stats['bt'] ?? 0, 2) ?>
                    ],
                    backgroundColor: '#b5935b',
                    borderRadius: 5
                }]
            },
            options: { 
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, max: 3 } }
            }
        });

        // 4. Retention
        new Chart(document.getElementById('stayDoughnut'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($first_stay_data)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($first_stay_data)) ?>,
                    backgroundColor: ['#2d4c31', '#b5935b'],
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // 5. Purpose
        new Chart(document.getElementById('purposeChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($purpose_data)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($purpose_data)) ?>,
                    backgroundColor: '#2d4c31',
                    borderRadius: 5
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>