<?php
require_once 'includes/auth.php';
require_once '../config/db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: view_feedbacks.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM guest_feedbacks WHERE id = ?");
$stmt->execute([$_GET['id']]);
$g = $stmt->fetch();

if (!$g) {
    die("Record not found.");
}

// --- CALCULATION LOGIC ---
$service_fields = ['frontdesk', 'reservations', 'housekeeping', 'accommodation', 'safety', 'valet', 'telephone_operator'];
$service_total = 0;
foreach($service_fields as $f) { $service_total += $g[$f]; }
$service_avg = $service_total / count($service_fields);

$fnb_fields = ['food_quality', 'serving_time', 'wait_staff', 'grooming', 'behavior', 'bar'];
$fnb_total = 0;
foreach($fnb_fields as $f) { $fnb_total += $g[$f]; }
$fnb_avg = $fnb_total / count($fnb_fields);

function getRatingLabel($score) {
    if ($score >= 2.5) return ['text' => 'EXCELLENT', 'color' => '#2d4c31'];
    if ($score >= 1.6) return ['text' => 'GOOD', 'color' => '#b5935b'];
    return ['text' => 'POOR', 'color' => '#a94442'];
}

$overall_rating = getRatingLabel($g['overall_service']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Report - <?= htmlspecialchars($g['guest_name']) ?></title>
    <link rel="icon" type="image/x-icon" href="../img/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Added Kumbh Sans with Bold weights */
        @import url('https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@700;800&family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;600;700&display=swap');
        
        :root { 
            --jh-primary: #2d4c31; 
            --jh-accent-gold: #b5935b; 
            --jh-bg-beige: #f4eee1; 
            --jh-text-brown: #4a3c31;
            --jh-border: #d1c1ad;
            --rating-color: <?= $overall_rating['color'] ?>;
            --sidebar-width: 320px;
            --font-bold: 'Kumbh Sans', sans-serif;
        }

        body { font-family: 'Montserrat', sans-serif; background: var(--jh-bg-beige); margin: 0; display: flex; color: var(--jh-text-brown); min-height: 100vh; overflow-x: hidden; }
        
        #sidebar-wrapper {
            width: var(--sidebar-width);
            flex-shrink: 0;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1000;
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

        .main-content { 
            flex-grow: 1;
            padding: 4rem; 
            min-width: 0;
            width: 100%;
            box-sizing: border-box; 
            display: flex;
            flex-direction: column;
        }

        /* Actions Bar */
        .action-bar { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .btn { padding: 10px 18px; border-radius: 4px; border: none; font-family: var(--font-bold); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; cursor: pointer; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-print { background: var(--jh-primary); color: white; }
        .btn-pdf { background: #a94442; color: white; } /* Distinctive PDF Red */
        .btn-back { background: white; color: var(--jh-primary); border: 1px solid var(--jh-primary); }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

        /* Updated Header with Kumbh Sans BOLD */
        .report-header { border-bottom: 2px solid var(--jh-primary); padding-bottom: 1.5rem; margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-end; }
        h1 { font-family: var(--font-bold); font-weight: 800; font-size: 2.8rem; margin: 0; color: var(--jh-primary); letter-spacing: -1px; }
        .date-stamp { font-family: var(--font-bold); color: var(--jh-accent-gold); font-weight: 700; letter-spacing: 1px; font-size: 0.8rem; margin-top: 5px; }

        .rating-display { text-align: right; border-left: 4px solid var(--rating-color); padding-left: 1.5rem; }
        .rating-label { font-family: var(--font-bold); font-size: 0.75rem; font-weight: 700; letter-spacing: 2px; color: #888; text-transform: uppercase; }
        .rating-value { font-family: var(--font-bold); font-size: 2.2rem; font-weight: 800; color: var(--rating-color); margin: 0; }
        .rating-score { font-family: 'Montserrat'; font-size: 1rem; color: var(--jh-text-brown); font-weight: 400; opacity: 0.7; }

        /* Grid Layout */
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start; }
        .card { background: white; padding: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 4px; height: 100%; box-sizing: border-box; }
        
        /* Card Titles with Kumbh Sans BOLD */
        .card h3 { font-family: var(--font-bold); font-weight: 700; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; color: var(--jh-primary); border-bottom: 1px solid var(--jh-accent-gold); padding-bottom: 0.5rem; margin-top: 0; display: flex; justify-content: space-between; align-items: center; }
        
        .avg-number { font-family: var(--font-bold); font-size: 0.8rem; font-weight: 700; background: #f9f6f0; padding: 4px 8px; border-radius: 3px; color: var(--jh-accent-gold); }
        .data-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f9f9f9; font-size: 0.9rem; }
        .label { font-family: var(--font-bold); font-weight: 700; color: #888; text-transform: uppercase; font-size: 0.7rem; }
        .score-num { font-weight: 700; color: var(--jh-primary); }
        
        .comment-group { margin-top: 1.2rem; }
        .comment-text { background: #fdfaf4; padding: 1rem; border-left: 3px solid var(--jh-accent-gold); font-style: italic; font-size: 0.85rem; line-height: 1.5; margin-top: 5px; }

        .footer-grid { margin-top: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }

        /* Updated Footer */
        .dashboard-footer {
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 1px solid var(--jh-border);
            text-align: center;
            color: #8e8379;
            font-size: 0.75rem;
            font-family: var(--font-bold);
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        @media (max-width: 1100px) {
            #sidebar-wrapper { position: fixed; left: -320px; height: 100vh; width: 320px; }
            body.mobile-sidebar-active #sidebar-wrapper { left: 0; }
            .mobile-toggle { display: block; }
            .grid, .footer-grid { grid-template-columns: 1fr; }
            .main-content { padding: 6rem 1.5rem 2rem 1.5rem; width: 100%; }
            .report-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .rating-display { border-left: none; border-top: 4px solid var(--rating-color); padding-left: 0; padding-top: 1rem; text-align: left; width: 100%; }
            h1 { font-size: 2rem; }
        }

        @media print {
            body { background: white; padding: 1.5cm; }
            .mobile-toggle, #sidebar-wrapper, .action-bar, .btn-back, .dashboard-footer { display: none !important; }
            .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .card { box-shadow: none; border: 1px solid #eee; break-inside: avoid; }
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
        <div class="action-bar">
            <a href="view_feedbacks.php" class="btn btn-back"><i class="fa fa-arrow-left"></i> Back to List</a>
            <button onclick="window.print()" class="btn btn-print"><i class="fa fa-print"></i> Quick Print</button>
            <button onclick="window.print()" class="btn btn-pdf"><i class="fa fa-file-pdf"></i> Download PDF</button>
        </div>
        
        <div class="report-header">
            <div>
                <h1>Guest Report Card</h1>
                <div class="date-stamp">SUBMITTED: <?= date('F d, Y @ h:i A', strtotime($g['submitted_at'])) ?></div>
            </div>
            <div class="rating-display">
                <div class="rating-label">Overall Experience</div>
                <div class="rating-value">
                    <?= $overall_rating['text'] ?> 
                    <span class="rating-score"><?= number_format($g['overall_service'], 1) ?>/3</span>
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Guest Profile</h3>
                <div class="data-row"><span class="label">Name</span><span><?= htmlspecialchars($g['guest_name'] ?: 'Anonymous') ?></span></div>
                <div class="data-row"><span class="label">Room</span><span><?= htmlspecialchars($g['room_no'] ?: '-') ?></span></div>
                <div class="data-row"><span class="label">Email</span><span><?= htmlspecialchars($g['email'] ?: '-') ?></span></div>
                <div class="data-row"><span class="label">Contact</span><span><?= htmlspecialchars($g['contact_no'] ?: '-') ?></span></div>
                <div class="data-row"><span class="label">Stay Dates</span><span><?= htmlspecialchars($g['date_of_stay'] ?: '-') ?></span></div>
                <div class="data-row"><span class="label">Purpose</span><span><?= htmlspecialchars($g['purpose_of_stay'] ?: '-') ?></span></div>
                <div class="data-row"><span class="label">First Time</span><span><?= htmlspecialchars($g['first_stay'] ?: '-') ?></span></div>
            </div>

            <div class="card">
                <h3>Service Audit <span class="avg-number">Avg: <?= number_format($service_avg, 2) ?></span></h3>
                <div class="data-row"><span class="label">Front Desk</span><span class="score-num"><?= $g['frontdesk'] ?>/3</span></div>
                <div class="data-row"><span class="label">Reservations</span><span class="score-num"><?= $g['reservations'] ?>/3</span></div>
                <div class="data-row"><span class="label">Housekeeping</span><span class="score-num"><?= $g['housekeeping'] ?>/3</span></div>
                <div class="data-row"><span class="label">Accommodation</span><span class="score-num"><?= $g['accommodation'] ?>/3</span></div>
                <div class="data-row"><span class="label">Safety</span><span class="score-num"><?= $g['safety'] ?>/3</span></div>
                <div class="data-row"><span class="label">Valet</span><span class="score-num"><?= $g['valet'] ?>/3</span></div>
                <div class="data-row"><span class="label">Operator</span><span class="score-num"><?= $g['telephone_operator'] ?>/3</span></div>
            </div>

            <div class="card">
                <h3>Dining Audit <span class="avg-number">Avg: <?= number_format($fnb_avg, 2) ?></span></h3>
                <div class="data-row"><span class="label">Food Quality</span><span class="score-num"><?= $g['food_quality'] ?>/3</span></div>
                <div class="data-row"><span class="label">Serving Time</span><span class="score-num"><?= $g['serving_time'] ?>/3</span></div>
                <div class="data-row"><span class="label">Wait Staff</span><span class="score-num"><?= $g['wait_staff'] ?>/3</span></div>
                <div class="data-row"><span class="label">Grooming</span><span class="score-num"><?= $g['grooming'] ?>/3</span></div>
                <div class="data-row"><span class="label">Behavior</span><span class="score-num"><?= $g['behavior'] ?>/3</span></div>
                <div class="data-row"><span class="label">Bar & Beverage</span><span class="score-num"><?= $g['bar'] ?>/3</span></div>
            </div>

            <div class="card">
                <h3>Staff & Department Notes</h3>
                <div class="comment-group">
                    <span class="label">Helpful Staff</span>
                    <p style="font-family: var(--font-bold); font-weight: 700; color: var(--jh-primary); margin: 5px 0;"><?= htmlspecialchars($g['helpful_staff_names'] ?: 'None recorded') ?></p>
                </div>
                <div class="comment-group">
                    <span class="label">Front Office Remarks</span>
                    <div class="comment-text">"<?= htmlspecialchars($g['frontdesk_comments'] ?: 'No specific remarks.') ?>"</div>
                </div>
                <div class="comment-group">
                    <span class="label">Dining Remarks</span>
                    <div class="comment-text">"<?= htmlspecialchars($g['fnb_comments'] ?: 'No specific remarks.') ?>"</div>
                </div>
            </div>
        </div>

        <div class="footer-grid">
            <div class="card">
                <h3>Future Suggestions</h3>
                <div class="comment-group">
                    <span class="label">Guest Proposals</span>
                    <div class="comment-text">"<?= htmlspecialchars($g['suggestions_future'] ?: 'No suggestions provided.') ?>"</div>
                </div>
            </div>
            <div class="card">
                <h3>General Feedback</h3>
                <div class="comment-group">
                    <span class="label">Other Comments</span>
                    <div class="comment-text" style="background: #f4eee1;">"<?= htmlspecialchars($g['other_comments'] ?: 'No additional comments.') ?>"</div>
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
            const sidebarWrapper = document.getElementById('sidebar-wrapper');
            const toggleBtn = document.querySelector('.mobile-toggle');
            if (window.innerWidth <= 1100 && document.body.classList.contains('mobile-sidebar-active')) {
                if (!sidebarWrapper.contains(e.target) && !toggleBtn.contains(e.target)) {
                    document.body.classList.remove('mobile-sidebar-active');
                }
            }
        });
    </script>
</body>
</html>