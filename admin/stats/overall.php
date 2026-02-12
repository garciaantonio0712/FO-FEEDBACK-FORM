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

// 2. Fetch Departmental Averages
$dept_query = "SELECT AVG(frontdesk) as fd, AVG(housekeeping) as hk, AVG(accommodation) as acc, AVG(overall_service) as ov, AVG(fnb_service) as fnb FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?";
$stmt_dept = $pdo->prepare($dept_query);
$stmt_dept->execute([$start_date, $end_date]);
$dept_stats = $stmt_dept->fetch(PDO::FETCH_ASSOC);

// 3. Fetch F&B Detail Averages
$fnb_detail_query = "SELECT AVG(food_quality) as fq, AVG(serving_time) as st, AVG(wait_staff) as ws, AVG(bar) as bar FROM guest_feedbacks WHERE DATE(submitted_at) BETWEEN ? AND ?";
$stmt_fnb = $pdo->prepare($fnb_detail_query);
$stmt_fnb->execute([$start_date, $end_date]);
$fnb_details = $stmt_fnb->fetch(PDO::FETCH_ASSOC);

// 4. Fetch First Stay Distribution
$first_stay_query = "SELECT first_stay, COUNT(*) as count FROM guest_feedbacks WHERE first_stay IS NOT NULL AND DATE(submitted_at) BETWEEN ? AND ? GROUP BY first_stay";
$stmt_stay = $pdo->prepare($first_stay_query);
$stmt_stay->execute([$start_date, $end_date]);
$first_stay_data = $stmt_stay->fetchAll(PDO::FETCH_KEY_PAIR);

// 5. Fetch Purpose of Stay Distribution
$purpose_query = "SELECT purpose_of_stay, COUNT(*) as count FROM guest_feedbacks WHERE purpose_of_stay IS NOT NULL AND DATE(submitted_at) BETWEEN ? AND ? GROUP BY purpose_of_stay";
$stmt_purpose = $pdo->prepare($purpose_query);
$stmt_purpose->execute([$start_date, $end_date]);
$purpose_data = $stmt_purpose->fetchAll(PDO::FETCH_KEY_PAIR);
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
        /* Import Kumbh Sans with multiple weights */
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

        /* Sidebar Logic */
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

        body.sidebar-hidden #sidebar-wrapper {
            width: var(--sidebar-collapsed-width);
        }

        .main-content { 
            flex: 1;
            padding: 1.5rem 2rem; 
            transition: all 0.3s ease;
            min-width: 0; /* Prevents flex items from overflowing */
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Hamburger Styles */
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

        header { margin-bottom: 1.5rem; }
        
        /* Ultra Bold Main Title */
        header h1 { 
            font-family: 'Kumbh Sans', sans-serif; 
            font-weight: 900; 
            color: var(--jh-primary); 
            font-size: clamp(1.8rem, 5vw, 2.8rem); 
            margin: 0; 
            letter-spacing: -1px;
            text-shadow: 0.5px 0.5px 0px rgba(0,0,0,0.1);
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

        /* Filter Section */
        .filter-section { background: white; padding: 1rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-radius: 8px; }
        .filter-group { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .quick-presets { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        .btn-preset { text-decoration: none; padding: 6px 12px; font-size: 0.7rem; font-weight: 700; color: var(--jh-text-brown); border: 1px solid var(--jh-border); border-radius: 20px; transition: 0.3s; white-space: nowrap; }
        .btn-preset.active { background: var(--jh-primary); color: white; border-color: var(--jh-primary); }
        
        .custom-range { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .custom-range input { border: 1px solid var(--jh-border); padding: 6px 10px; font-family: 'Montserrat'; font-weight: 600; border-radius: 4px; font-size: 0.8rem; flex: 1; min-width: 130px; }
        .btn-filter { background: var(--jh-primary); color: white; border: none; padding: 7px 15px; font-weight: 800; cursor: pointer; border-radius: 4px; font-size: 0.8rem; text-transform: uppercase; }

        /* Grid and Charts */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 1.2rem; 
            width: 100%;
            max-width: 100%; 
            flex-grow: 1;
        }
        .chart-card { background: var(--jh-card-white); padding: 1.2rem; box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-top: 5px solid var(--jh-accent-gold); border-radius: 4px; min-width: 0; }
        .chart-card.full-width { grid-column: span 2; }
        
        .chart-card h3 { 
            font-family: 'Kumbh Sans', sans-serif;
            font-weight: 800;
            font-size: 1rem; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            margin-bottom: 1.2rem; 
            color: var(--jh-primary); 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 0.8rem; 
        }
        
        .chart-container { 
            position: relative; 
            height: 250px; 
            width: 100%; 
        }

        .dashboard-footer {
            margin-top: 3rem;
            padding: 1.5rem 0;
            border-top: 1px solid var(--jh-border);
            text-align: center;
            color: #8e8379;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Tablet/Mobile Breakpoints */
        @media (max-width: 1024px) {
            #sidebar-wrapper { 
                position: fixed;
                left: -320px;
                height: 100vh;
                width: 280px;
            }
            body.mobile-sidebar-active #sidebar-wrapper { left: 0; }
            .main-content { padding: 5rem 1rem 1.5rem; }
            .mobile-toggle { display: block; }
            .stats-grid { grid-template-columns: 1fr; }
            .chart-card.full-width { grid-column: span 1; }
            .filter-group { flex-direction: column; align-items: stretch; }
            .quick-presets { justify-content: center; }
            .custom-range { justify-content: center; }
        }

        @media (max-width: 480px) {
            .chart-container { height: 200px; } 
            .btn-preset { padding: 6px 8px; font-size: 0.6rem; }
            .custom-range input { font-size: 0.75rem; width: 100%; }
            .btn-filter { width: 100%; margin-top: 5px; }
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
        <i class="fas fa-bars"></i>
    </button>

    <div id="sidebar-wrapper">
        <?php include '../includes/sidebar.php'; ?>
    </div>

    <div class="main-content">
        <header>
            <p class="subtitle">Analytical Insights</p>
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
                <h3>Departmental Performance (Scale 1-3)</h3>
                <div class="chart-container"><canvas id="deptChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>F&B Service Quality</h3>
                <div class="chart-container"><canvas id="fnbRadarChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Guest Retention</h3>
                <div class="chart-container"><canvas id="stayDoughnut"></canvas></div>
            </div>
            <div class="chart-card full-width">
                <h3>Purpose of Stay Analysis</h3>
                <div class="chart-container"><canvas id="purposeChart"></canvas></div>
            </div>
        </div>

        <footer class="dashboard-footer">
            &copy; 2026 MIS Department, All Rights Reserved
        </footer>
    </div>

    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-hidden');
            const icon = document.getElementById('toggle-icon');
            if(icon) {
                if(document.body.classList.contains('sidebar-hidden')) {
                    icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
                } else {
                    icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
                }
            }
        }

        function toggleMobileMenu() {
            document.body.classList.toggle('mobile-sidebar-active');
        }

        window.addEventListener('click', function(e) {
            const sidebarWrapper = document.getElementById('sidebar-wrapper');
            const toggleBtn = document.querySelector('.mobile-toggle');
            if (window.innerWidth <= 1024 && document.body.classList.contains('mobile-sidebar-active')) {
                if (!sidebarWrapper.contains(e.target) && !toggleBtn.contains(e.target)) {
                    document.body.classList.remove('mobile-sidebar-active');
                }
            }
        });

        // Chart.js Global Defaults
        Chart.defaults.font.family = "'Montserrat', sans-serif";
        Chart.defaults.color = '#4a3c31';
        Chart.defaults.font.size = 11; 
        Chart.defaults.font.weight = '700';

        // 1. Dept Bar Chart
        new Chart(document.getElementById('deptChart'), {
            type: 'bar',
            data: {
                labels: ['Front Desk', 'Housekeeping', 'Accommodation', 'F&B Service', 'Overall'],
                datasets: [{
                    label: 'Avg Rating',
                    data: [
                        <?= number_format($dept_stats['fd'] ?? 0, 2) ?>, 
                        <?= number_format($dept_stats['hk'] ?? 0, 2) ?>, 
                        <?= number_format($dept_stats['acc'] ?? 0, 2) ?>, 
                        <?= number_format($dept_stats['fnb'] ?? 0, 2) ?>,
                        <?= number_format($dept_stats['ov'] ?? 0, 2) ?>
                    ],
                    backgroundColor: '#2d4c31',
                    borderRadius: 4
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false, 
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        max: 3,
                        ticks: { font: { weight: '800' } }
                    },
                    x: {
                        ticks: { font: { weight: '800' } }
                    }
                }, 
                plugins: { legend: { display: false } } 
            }
        });

        // 2. F&B Radar Chart
        new Chart(document.getElementById('fnbRadarChart'), {
            type: 'radar',
            data: {
                labels: ['Food Quality', 'Serving Time', 'Wait Staff', 'Bar'],
                datasets: [{
                    label: 'Score',
                    data: [
                        <?= number_format($fnb_details['fq'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_details['st'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_details['ws'] ?? 0, 2) ?>, 
                        <?= number_format($fnb_details['bar'] ?? 0, 2) ?>
                    ],
                    fill: true,
                    backgroundColor: 'rgba(181, 147, 91, 0.2)',
                    borderColor: '#b5935b'
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false, 
                scales: { 
                    r: { 
                        beginAtZero: true, 
                        max: 3, 
                        ticks: { display: false },
                        pointLabels: { font: { weight: '800', size: 10 } }
                    } 
                } 
            }
        });

        // 3. Guest Retention Doughnut
        new Chart(document.getElementById('stayDoughnut'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($first_stay_data)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($first_stay_data)) ?>,
                    backgroundColor: ['#2d4c31', '#b5935b']
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false, 
                plugins: { 
                    legend: { 
                        position: 'bottom', 
                        labels: { 
                            boxWidth: 12, 
                            padding: 10, 
                            font: { size: 10, weight: '800' } 
                        } 
                    } 
                } 
            }
        });

        // 4. Purpose Horizontal Bar
        new Chart(document.getElementById('purposeChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($purpose_data)) ?>,
                datasets: [{
                    label: 'Guest Count',
                    data: <?= json_encode(array_values($purpose_data)) ?>,
                    backgroundColor: '#2d4c31',
                    borderRadius: 4
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false, 
                indexAxis: 'y', 
                scales: {
                    x: { ticks: { font: { weight: '800' } } },
                    y: { ticks: { font: { weight: '800' } } }
                },
                plugins: { legend: { display: false } } 
            }
        });
    </script>
</body>
</html>