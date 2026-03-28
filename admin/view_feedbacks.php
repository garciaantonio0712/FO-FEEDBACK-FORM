<?php
require_once 'includes/auth.php';
require_once '../config/db_connect.php';

// 1. Get Search Query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 2. Build the SQL Query
$search_sql = "";
$params = [];
$types = "";

if ($search !== '') {
    $search_sql = " WHERE (guest_name LIKE ? 
                    OR email LIKE ? 
                    OR room_no LIKE ? 
                    OR submitted_at LIKE ?";
    
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param, $search_param];
    $types = "ssss"; // string types for the first 4 params

    $s_lower = strtolower($search);
    if ($s_lower === 'excellent') {
        $search_sql .= " OR overall_service >= 2.5";
    } elseif ($s_lower === 'good') {
        $search_sql .= " OR (overall_service >= 1.6 AND overall_service < 2.5)";
    } elseif ($s_lower === 'poor') {
        $search_sql .= " OR overall_service < 1.6";
    } else {
        $search_sql .= " OR overall_service LIKE ? 
                        OR frontdesk LIKE ? 
                        OR food_quality LIKE ?";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "sss"; // add 3 more strings
    }
    $search_sql .= ")";
}

// 4. Pagination Setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 5. Get Total Count for Pagination (MySQLi version)
$count_query = "SELECT COUNT(*) as total FROM guest_feedbacks" . $search_sql;
$total_stmt = $conn->prepare($count_query);
if ($search !== '') {
    $total_stmt->bind_param($types, ...$params);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// 6. Fetch Feedbacks for Screen
$fetch_query = "SELECT * FROM guest_feedbacks" . $search_sql . " ORDER BY submitted_at DESC LIMIT ? OFFSET ?";
$fetch_stmt = $conn->prepare($fetch_query);

if ($search !== '') {
    // Append pagination types
    $types_paginated = $types . "ii";
    $params_paginated = array_merge($params, [$limit, $offset]);
    $fetch_stmt->bind_param($types_paginated, ...$params_paginated);
} else {
    $fetch_stmt->bind_param("ii", $limit, $offset);
}

$fetch_stmt->execute();
$feedbacks_result = $fetch_stmt->get_result();
$feedbacks = $feedbacks_result->fetch_all(MYSQLI_ASSOC);

// 7. Fetch ALL matching records for the JS Print/PDF Function
$all_query = "SELECT * FROM guest_feedbacks" . $search_sql . " ORDER BY submitted_at DESC";
$all_stmt = $conn->prepare($all_query);
if ($search !== '') {
    $all_stmt->bind_param($types, ...$params);
}
$all_stmt->execute();
$all_result = $all_stmt->get_result();
$all_records = $all_result->fetch_all(MYSQLI_ASSOC);
$all_feedbacks_json = json_encode($all_records);

function getRatingLabel($score) {
    if ($score >= 2.5) return ['text' => 'Excellent', 'class' => 'bg-green'];
    if ($score >= 1.6) return ['text' => 'Good', 'class' => 'bg-gold'];
    return ['text' => 'Poor', 'class' => 'bg-red'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Feedback Records - John Hay Hotels</title>
    <link rel="icon" type="image/x-icon" href="../img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@700;800&family=Playfair+Display:wght@700&family=Montserrat:wght@400;600;700&display=swap');
        
        :root {
            --jh-primary: #2d4c31;
            --jh-accent-gold: #b5935b;
            --jh-bg-beige: #f4eee1;
            --jh-card-white: #ffffff;
            --jh-text-brown: #4a3c31;
            --jh-border: #d1c1ad;
            --sidebar-width: 320px;
            --sidebar-collapsed-width: 80px;
            --font-bold: 'Kumbh Sans', sans-serif;
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
            transition: all 0.3s ease;
            position: relative;
            z-index: 1000;
        }

        .main-content { 
            flex-grow: 1;
            padding: 2rem 3rem; 
            transition: all 0.3s ease;
            min-width: 0;
            width: 100%;
            box-sizing: border-box;
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

        header h1 { 
            font-family: var(--font-bold); 
            font-weight: 800; 
            color: var(--jh-primary); 
            font-size: 2.5rem; 
            margin: 0; 
            letter-spacing: -1px;
        }
        .subtitle { 
            font-family: var(--font-bold);
            color: var(--jh-accent-gold); 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            font-weight: 700; 
            font-size: 0.8rem; 
            margin-bottom: 2rem; 
        }

        .tools-bar { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; gap: 15px; flex-wrap: wrap; }
        .action-group { display: flex; gap: 10px; }
        .btn-tool { 
            padding: 10px 18px; border-radius: 4px; border: 1px solid var(--jh-border); 
            background: white; color: var(--jh-primary); font-size: 0.75rem; 
            font-family: var(--font-bold); font-weight: 700; cursor: pointer; text-decoration: none; 
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
            text-transform: uppercase;
        }
        .btn-tool:hover { background: #f9f6f0; border-color: var(--jh-primary); }
        .btn-pdf-export { background: #a94442; color: white; border-color: #a94442; }
        .btn-pdf-export:hover { background: #8e3a38; color: white; }

        .dropdown { position: relative; display: inline-block; }
        .dropdown-content {
            display: none; position: absolute; right: 0; background-color: white;
            min-width: 220px; box-shadow: 0px 8px 16px rgba(0,0,0,0.1);
            z-index: 1000; border-radius: 4px; border: 1px solid var(--jh-border);
        }
        .dropdown-content button {
            width: 100%; text-align: left; padding: 12px 16px; border: none;
            background: none; font-family: 'Montserrat'; font-size: 0.8rem; 
            cursor: pointer; color: var(--jh-text-brown);
        }
        .dropdown-content button:hover { background-color: #f4eee1; color: var(--jh-primary); }
        .dropdown:hover .dropdown-content { display: block; }

        .table-container { background: white; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-radius: 4px; overflow-x: auto; flex-grow: 1; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; min-width: 800px; }
        th { 
            background: #f9f6f0; text-align: left; padding: 15px; color: var(--jh-primary); 
            border-bottom: 2px solid var(--jh-border); font-family: var(--font-bold); 
            font-weight: 700; text-transform: uppercase; letter-spacing: 1px; 
        }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        .rating-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; color: white; display: inline-block; min-width: 80px; text-align: center; }
        .bg-green { background: var(--jh-primary); }
        .bg-gold { background: var(--jh-accent-gold); }
        .bg-red { background: #a94442; }

        .overall-score { font-family: var(--font-bold); font-weight: 800; color: var(--jh-primary); font-size: 1.1rem; }

        .btn-view {
            text-decoration: none; background: var(--jh-primary); color: white; padding: 8px 15px;
            font-family: var(--font-bold); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; border-radius: 3px; transition: 0.3s;
            display: inline-block; text-align: center;
        }
        .btn-view:hover { background: var(--jh-accent-gold); }

        .search-form { display: flex; gap: 10px; flex-grow: 1; max-width: 600px; }
        .search-wrapper { position: relative; flex-grow: 1; display: flex; align-items: center; }
        .search-box { 
            width: 100%; padding: 12px 40px 12px 15px; border: 1px solid var(--jh-border); 
            font-family: 'Montserrat'; border-radius: 4px; outline: none;
        }
        .clear-search { position: absolute; right: 12px; color: #999; border: none; background: none; cursor: pointer; }
        .btn-search { 
            background: var(--jh-primary); color: white; border: none; padding: 0 25px; 
            border-radius: 4px; cursor: pointer; font-family: var(--font-bold); font-weight: 700; text-transform: uppercase; font-size: 0.75rem;
        }

        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 2rem; }
        .page-link { text-decoration: none; padding: 8px 16px; border: 1px solid var(--jh-border); color: var(--jh-primary); background: white; border-radius: 4px; font-weight: 600; }
        .page-link.active { background: var(--jh-primary); color: white; border-color: var(--jh-primary); }

        .dashboard-footer {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--jh-border);
            text-align: center;
            color: #8e8379;
            font-size: 0.75rem;
            font-family: var(--font-bold);
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        @media (max-width: 1024px) {
            #sidebar-wrapper { position: fixed; left: -320px; height: 100vh; width: 280px; }
            body.mobile-sidebar-active #sidebar-wrapper { left: 0; }
            .main-content { padding: 6rem 1rem 2rem; width: 100%; }
            .mobile-toggle { display: block; }
            header h1 { font-size: 1.8rem; }
            .tools-bar { flex-direction: column; align-items: stretch; }
            .search-form { max-width: 100%; }
            .table-container { padding: 0; background: transparent; box-shadow: none; }
            table, thead, tbody, th, td, tr { display: block; width: 100%; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { background: white; margin-bottom: 1.5rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--jh-border); }
            td { border: none; border-bottom: 1px solid #f4eee1; position: relative; padding: 12px 15px 12px 40%; text-align: left; }
            td::before { content: attr(data-label); position: absolute; left: 15px; width: 35%; font-weight: 700; color: var(--jh-primary); text-transform: uppercase; font-size: 0.7rem; }
        }

        @media print {
            .mobile-toggle, #sidebar-wrapper, .tools-bar, .pagination, .btn-view, .btn-view-col, .dashboard-footer { display: none !important; }
            .main-content { margin: 0 !important; width: 100% !important; padding: 0 !important; }
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
            <h1>Guest Feedback Records</h1>
            <p class="subtitle">Detailed Audit of Guest Experiences</p>
        </header>

        <div class="tools-bar">
            <form method="GET" action="view_feedbacks.php" class="search-form" id="searchForm">
                <div class="search-wrapper">
                    <input type="text" name="search" id="searchInput" class="search-box" 
                           placeholder="Search name, date, room, or rating..." 
                           value="<?= htmlspecialchars($search) ?>">
                    
                    <button type="button" id="clearBtn" class="clear-search" style="display: <?= $search !== '' ? 'block' : 'none' ?>;">
                        <i class="fa fa-times-circle"></i>
                    </button>
                </div>
                <button type="submit" class="btn-search">Search</button>
            </form>

            <div class="action-group">
                <div class="dropdown">
                    <button class="btn-tool">
                        <i class="fa fa-print"></i> Print List <i class="fa fa-caret-down" style="font-size:0.6rem;"></i>
                    </button>
                    <div class="dropdown-content">
                        <button onclick="printFiltered(10)">Print Top 10</button>
                        <button onclick="printFiltered(20)">Print Top 20</button>
                        <button onclick="printFiltered('all')">Print All Results (<?= $total_records ?>)</button>
                        <button onclick="promptCustomPrint()">Custom Range...</button>
                    </div>
                </div>
                <button onclick="printFiltered('all')" class="btn-tool btn-pdf-export">
                    <i class="fa fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Experience</th>
                        <th>Overall Rating</th>
                        <th class="btn-view-col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feedbacks)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 40px; color: #888;">No records match your criteria.</td></tr>
                    <?php endif; ?>

                    <?php foreach($feedbacks as $row): 
                        $rating = getRatingLabel($row['overall_service']);
                    ?>
                    <tr>
                        <td data-label="Date"><?= date('M d, Y', strtotime($row['submitted_at'])) ?></td>
                        <td data-label="Guest">
                            <strong><?= htmlspecialchars($row['guest_name'] ?: 'N/A') ?></strong><br>
                            <small style="color: #888;"><?= htmlspecialchars($row['email'] ?: '') ?></small>
                        </td>
                        <td data-label="Room"><?= htmlspecialchars($row['room_no'] ?: '-') ?></td>
                        <td data-label="Experience"><span class="rating-badge <?= $rating['class'] ?>"><?= $rating['text'] ?></span></td>
                        <td data-label="Overall Rating" class="overall-score">
                            <?= number_format($row['overall_rating'] ?? 0, 1) ?>
                        </td>
                        <td class="btn-view-col"><a href="view_details.php?id=<?= $row['id'] ?>" class="btn-view">View Details</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php 
                function paginate($p, $s) {
                    return "view_feedbacks.php?page=$p" . ($s !== '' ? "&search=" . urlencode($s) : "");
                }
                ?>
                <?php if($page > 1): ?>
                    <a href="<?= paginate($page - 1, $search) ?>" class="page-link"><i class="fa fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="<?= paginate($i, $search) ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if($page < $total_pages): ?>
                    <a href="<?= paginate($page + 1, $search) ?>" class="page-link"><i class="fa fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <footer class="dashboard-footer">
            &copy; 2026 MIS Department, All Rights Reserved
        </footer>
    </div>

    <script>
        function toggleMobileMenu() {
            document.body.classList.toggle('mobile-sidebar-active');
        }

        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearBtn');
        const searchForm = document.getElementById('searchForm');

        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchForm.submit(); 
        });

        searchInput.addEventListener('input', function() {
            clearBtn.style.display = this.value.length > 0 ? 'block' : 'none';
        });

        const allData = <?= $all_feedbacks_json ?>;

        function promptCustomPrint() {
            const count = prompt("How many records would you like to process? (Total: " + allData.length + ")", "50");
            if (count) {
                const num = parseInt(count);
                if (isNaN(num) || num <= 0) alert("Please enter a valid number.");
                else printFiltered(num);
            }
        }

        function printFiltered(limit) {
            const printData = (limit === 'all') ? allData : allData.slice(0, limit);
            const printWindow = window.open('', '_blank');
            let html = `
            <html>
            <head>
                <title>Guest Feedback Report</title>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@700&family=Montserrat:wght@400;700&display=swap');
                    @page { size: portrait; margin: 1cm; }
                    body { font-family: 'Montserrat', sans-serif; padding: 20px; color: #4a3c31; }
                    .header { display: flex; align-items: center; border-bottom: 3px solid #2d4c31; padding-bottom: 15px; margin-bottom: 20px; }
                    h1 { font-family: 'Kumbh Sans', sans-serif; color: #2d4c31; margin: 0; font-size: 22px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th { text-align: left; background: #f9f6f0 !important; padding: 10px; border-bottom: 2px solid #d1c1ad; font-size: 11px; text-transform: uppercase; -webkit-print-color-adjust: exact; }
                    td { padding: 10px; border-bottom: 1px solid #eee; font-size: 11px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <div>
                        <h1>Guest Feedback Audit Report</h1>
                        <small>Generated on: ${new Date().toLocaleDateString()}</small>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Guest Name</th>
                            <th>Room</th>
                            <th>Experience</th>
                            <th>Overall Score</th>
                        </tr>
                    </thead>
                    <tbody>`;

            printData.forEach(row => {
                const ratingText = row.overall_service >= 2.5 ? 'EXCELLENT' : (row.overall_service >= 1.6 ? 'GOOD' : 'POOR');
                const score = row.overall_rating ? parseFloat(row.overall_rating).toFixed(1) : '0.0';

                html += `
                    <tr>
                        <td>${row.submitted_at.split(' ')[0]}</td>
                        <td><b>${row.guest_name || 'N/A'}</b></td>
                        <td>${row.room_no || '-'}</td>
                        <td>${ratingText}</td>
                        <td><b>${score}</b></td>
                    </tr>`;
            });

            html += `</tbody></table></body></html>`;
            printWindow.document.write(html);
            printWindow.document.close();
            
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
</body>
</html>