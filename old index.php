<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/icon.png">
    <title>Guest Feedback – John Hay Hotels</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --forest-green: #2d4c31;
            --pine-needle: #1e3522;
            --warm-tan: #f4eee1;
            --gold-accent: #b5935b;
            --text-dark: #2c241e;
            --border-color: #d1c1ad;
            --required-red: #b22222; 
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Montserrat', sans-serif;
            /* Corrected path: ../img/frontdesk.png moves out of 'public' 
               to find the 'img' folder based on your file structure.
            */
            background: linear-gradient(rgba(244, 238, 225, 0.7), rgba(244, 238, 225, 0.7)), 
                        url('../img/frontdesk.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: var(--text-dark);
            padding: 20px 10px;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 4px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.15);
            border-top: 8px solid var(--forest-green);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .header { 
            text-align: center; 
            padding: 2.5rem 1rem; 
            background: #fff; 
        }
        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            color: var(--forest-green);
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }
        .header .subline {
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 0.75rem;
            color: var(--gold-accent);
            font-weight: 600;
        }
        .divider { height: 2px; width: 80px; background: var(--gold-accent); margin: 20px auto; }
        .intro { 
            font-family: 'Playfair Display', serif; 
            font-size: 1.1rem; 
            color: #555; 
            max-width: 85%; 
            margin: 0 auto; 
            line-height: 1.5;
            font-style: italic;
        }

        .section { 
            margin: 1.5rem 1.5rem; 
            padding: 1.8rem; 
            background: #fafafa; 
            border: 1px solid #eee; 
            border-radius: 4px;
        }
        .section h2 {
            font-family: 'Playfair Display', serif;
            color: var(--forest-green);
            font-size: 1.35rem;
            margin-bottom: 1.4rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid var(--gold-accent);
            display: inline-block;
            padding-bottom: 6px;
        }

        .rating-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 1.4rem; 
        }
        .rating-table th { 
            padding: 12px 8px; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            color: var(--gold-accent); 
            font-weight: 600; 
            text-align: center; 
        }
        .rating-table td { 
            padding: 14px 6px; 
            border-bottom: 1px solid #ececec; 
            text-align: center; 
        }
        .rating-table td:first-child { 
            text-align: left; 
            font-weight: 600; 
            color: var(--pine-needle); 
            width: 38%; 
            font-size: 0.95rem; 
        }
        .rating-table input[type="radio"] { 
            accent-color: var(--forest-green); 
            width: 22px; 
            height: 22px; 
            cursor: pointer; 
        }

        textarea, input[type="text"], input[type="email"], input[type="date"], input[type="tel"], select {
            width: 100%; 
            padding: 12px; 
            border: 1px solid var(--border-color);
            font-family: inherit; 
            background: #fff; 
            margin-top: 8px; 
            margin-bottom: 1.2rem; 
            border-radius: 0;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--forest-green);
            background-color: #fff;
        }

        .form-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 1.4rem; 
        }
        .full-width { grid-column: span 2; }

        .required-label::after { content: " *"; color: var(--required-red); font-weight: bold; }

        .optional-header-inline {
            font-weight: 600;
            color: var(--gold-accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
            margin: 10px 0;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .first-stay-prompt { 
            margin: 1.5rem 0; 
            font-weight: 600; 
            font-size: 0.95rem; 
        }
        .radio-group { 
            display: flex; 
            gap: 2.5rem; 
            margin-top: 10px; 
        }

        .submit-btn {
            background: var(--forest-green); 
            color: white; 
            border: none; 
            padding: 18px 50px;
            font-family: 'Montserrat', sans-serif; 
            font-weight: 600; 
            text-transform: uppercase;
            letter-spacing: 2px; 
            cursor: pointer; 
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2rem auto; 
            width: 90%; 
            max-width: 320px;
            transition: 0.3s;
        }
        .submit-btn:hover { background: var(--pine-needle); }
        .submit-btn:disabled {
            background-color: #5c7a60;
            cursor: not-allowed;
            opacity: 0.8;
        }

        .loader {
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: none;
            margin-right: 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .footer-thankyou { 
            text-align: center; 
            font-family: 'Playfair Display', serif; 
            font-style: italic; 
            color: var(--forest-green); 
            padding: 1.5rem 1rem; 
            font-size: 1rem;
            border-top: 1px solid #eee;
        }

        .qr-section {
            text-align: center;
            padding: 2.5rem 1.5rem;
            background: #fafafa;
            border-top: 2px dashed var(--gold-accent);
        }
        .qr-container {
            display: inline-block;
            padding: 15px;
            background: white;
            border: 1px solid var(--border-color);
            margin-top: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .header h1 { font-size: 2.2rem; }
            .rating-table thead { display: none; }
            .rating-table tr { display: block; margin-bottom: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
            .rating-table td:first-child { display: block; width: 100%; margin-bottom: 12px; font-size: 1rem; }
            .rating-table td:not(:first-child) { 
                display: inline-flex; 
                flex-direction: column; 
                align-items: center; 
                width: 32%; 
                font-size: 0.75rem; 
                color: #777; 
            }
            .rating-table td:nth-child(2)::before { content: "Excellent"; margin-bottom: 6px; font-weight: 600; }
            .rating-table td:nth-child(3)::before { content: "Good"; margin-bottom: 6px; font-weight: 600; }
            .rating-table td:nth-child(4)::before { content: "Poor"; margin-bottom: 6px; font-weight: 600; }
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>John Hay Hotels</h1>
        <p class="subline">Garden Wing</p>
        <div class="divider"></div>
        <p class="intro">
            We are committed to provide a guest experience that exceeds your expectations. 
            Through your comments we can build on our strengths and, where necessary, improve our weaknesses.
        </p>
    </div>

    <form id="feedbackForm" action="submit_feedback.php" method="POST">
        
        <div class="section">
            <h2>Front of House</h2>
            <table class="rating-table">
                <thead>
                    <tr><th>Category</th><th>Excellent</th><th>Good</th><th>Poor</th></tr>
                </thead>
                <tbody>
                    <tr><td>Front Desk</td><td><input type="radio" name="frontdesk" value="3" required></td><td><input type="radio" name="frontdesk" value="2"></td><td><input type="radio" name="frontdesk" value="1"></td></tr>
                    <tr><td>Reservations</td><td><input type="radio" name="reservations" value="3" required></td><td><input type="radio" name="reservations" value="2"></td><td><input type="radio" name="reservations" value="1"></td></tr>
                    <tr><td>Telephone Operator</td><td><input type="radio" name="telephone_operator" value="3" required></td><td><input type="radio" name="telephone_operator" value="2"></td><td><input type="radio" name="telephone_operator" value="1"></td></tr>
                    <tr><td>Valet</td><td><input type="radio" name="valet" value="3" required></td><td><input type="radio" name="valet" value="2"></td><td><input type="radio" name="valet" value="1"></td></tr>
                    <tr><td>Housekeeping</td><td><input type="radio" name="housekeeping" value="3" required></td><td><input type="radio" name="housekeeping" value="2"></td><td><input type="radio" name="housekeeping" value="1"></td></tr>
                    <tr><td>Accommodation</td><td><input type="radio" name="accommodation" value="3" required></td><td><input type="radio" name="accommodation" value="2"></td><td><input type="radio" name="accommodation" value="1"></td></tr>
                    <tr><td>Safety</td><td><input type="radio" name="safety" value="3" required></td><td><input type="radio" name="safety" value="2"></td><td><input type="radio" name="safety" value="1"></td></tr>
                    <tr><td>Security</td><td><input type="radio" name="security" value="3" required></td><td><input type="radio" name="security" value="2"></td><td><input type="radio" name="security" value="1"></td></tr>
                    <tr><td>Overall Service</td><td><input type="radio" name="overall_service" value="3" required></td><td><input type="radio" name="overall_service" value="2"></td><td><input type="radio" name="overall_service" value="1"></td></tr>
                </tbody>
            </table>
            <label>Comments & Suggestions:</label>
            <textarea name="frontdesk_comments" rows="4" placeholder="Your thoughts..."></textarea>
        </div>

        <div class="section">
            <h2>Food & Beverage</h2>
            <table class="rating-table">
                <thead>
                    <tr><th>Category</th><th>Excellent</th><th>Good</th><th>Poor</th></tr>
                </thead>
                <tbody>
                    <tr><td>Food Quality</td><td><input type="radio" name="food_quality" value="3" required></td><td><input type="radio" name="food_quality" value="2"></td><td><input type="radio" name="food_quality" value="1"></td></tr>
                    <tr><td>Serving Time</td><td><input type="radio" name="serving_time" value="3" required></td><td><input type="radio" name="serving_time" value="2"></td><td><input type="radio" name="serving_time" value="1"></td></tr>
                    <tr><td>Wait Staff</td><td><input type="radio" name="wait_staff" value="3" required></td><td><input type="radio" name="wait_staff" value="2"></td><td><input type="radio" name="wait_staff" value="1"></td></tr>
                    <tr><td>Grooming</td><td><input type="radio" name="grooming" value="3" required></td><td><input type="radio" name="grooming" value="2"></td><td><input type="radio" name="grooming" value="1"></td></tr>
                    <tr><td>Behavior</td><td><input type="radio" name="behavior" value="3" required></td><td><input type="radio" name="behavior" value="2"></td><td><input type="radio" name="behavior" value="1"></td></tr>
                    <tr><td>Service</td><td><input type="radio" name="fnb_service" value="3" required></td><td><input type="radio" name="fnb_service" value="2"></td><td><input type="radio" name="fnb_service" value="1"></td></tr>
                    <tr><td>Bar</td><td><input type="radio" name="bar" value="3" required></td><td><input type="radio" name="bar" value="2"></td><td><input type="radio" name="bar" value="1"></td></tr>
                    <tr><td>Bartender</td><td><input type="radio" name="bartender" value="3" required></td><td><input type="radio" name="bartender" value="2"></td><td><input type="radio" name="bartender" value="1"></td></tr>
                </tbody>
            </table>
            <label>Comments & Suggestions:</label>
            <textarea name="fnb_comments" rows="4" placeholder="Your thoughts..."></textarea>

            <label style="margin-top:1.2rem; display:block;">Especially Helpful Staff:</label>
            <input type="text" name="helpful_staff_names" placeholder="Name/s of staff members">
        </div>

        <div class="section">
            <h2>Additional Comments</h2>
            <label>Suggestions for the future:</label>
            <textarea name="suggestions_future" rows="4" placeholder="How can we make your next visit even better?"></textarea>

            <label style="margin-top:1.2rem; display:block;">Other Comments:</label>
            <textarea name="other_comments" rows="4" placeholder="Any additional thoughts..."></textarea>
        </div>

        <div class="section">
            <h2>Your Information</h2>

            <div class="first-stay-prompt">
                <p class="required-label">Was this your first stay at John Hay Hotels?</p>
                <div class="radio-group">
                    <label><input type="radio" name="first_stay" value="Yes" required> Yes</label>
                    <label><input type="radio" name="first_stay" value="No" required> No</label>
                </div>
            </div>

            <div class="form-grid">
                <div class="full-width">
                    <label class="required-label">What was the purpose of your stay?</label>
                    <select name="purpose_of_stay" id="purpose_dropdown" onchange="toggleOtherPurpose()" required>
                        <option value="" disabled selected>Select a purpose...</option>
                        <option value="Leisure / Vacation">Leisure / Vacation</option>
                        <option value="Business / Work">Business / Work</option>
                        <option value="Conference / Event">Conference / Event</option>
                        <option value="Wedding / Celebration">Wedding / Celebration</option>
                        <option value="Staycation">Staycation</option>
                        <option value="Other">Other (Please specify)</option>
                    </select>
                    <input type="text" id="other_purpose" name="other_purpose_text" placeholder="Please specify your purpose" style="display:none; margin-top:8px;">
                </div>

                <div class="full-width optional-header-inline">
                    Your Information (Optional)
                </div>

                <div>
                    <label>Name:</label>
                    <input type="text" name="guest_name" placeholder="Your full name">
                </div>
                <div>
                    <label>Email:</label>
                    <input type="email" name="email" placeholder="your.email@example.com">
                </div>
                <div class="full-width">
                    <label>Address:</label>
                    <input type="text" name="address" placeholder="Your complete address">
                </div>
                <div>
                    <label>Contact No.:</label>
                    <input type="tel" name="contact_no" placeholder="+63 917 123 4567">
                </div>
                <div>
                    <label class="required-label">Room No.:</label>
                    <input type="text" name="room_no" placeholder="e.g. 205" required>
                </div>

                <div class="full-width">
                    <label class="required-label">Date/s of stay:</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px;">
                        <div>
                            <span style="font-size: 0.8rem; color: var(--gold-accent);">Check-in</span>
                            <input type="date" name="check_in" id="check_in" onchange="setMinCheckout()" required>
                        </div>
                        <div>
                            <span style="font-size: 0.8rem; color: var(--gold-accent);">Check-out</span>
                            <input type="date" name="check_out" id="check_out" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="footer-thankyou">
            Thank you for staying with us. We look forward to welcoming you again to John Hay Hotels.
        </p>

        <button type="submit" class="submit-btn" id="submitBtn">
            <div id="btnLoader" class="loader"></div>
            <span id="btnText">Submit Feedback</span>
        </button>
    </form>

    <div class="qr-section">
        <h3 style="font-family: 'Playfair Display', serif; color: var(--forest-green);">Access Digital Form</h3>
        <p style="font-size: 0.85rem; color: #666;">Scan this code to fill out this form on your mobile device.</p>
        <div id="qrcode" class="qr-container"></div>
        <p id="url-text" style="font-size: 0.7rem; color: var(--gold-accent); margin-top: 10px; font-weight: 600;"></p>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
window.onload = function() {
    var currentUrl = window.location.href;
    new QRCode(document.getElementById("qrcode"), {
        text: currentUrl,
        width: 160,
        height: 160,
        colorDark : "#2d4c31", 
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
    document.getElementById('url-text').innerText = "Direct Link: " + currentUrl;
};

function toggleOtherPurpose() {
    var dropdown = document.getElementById("purpose_dropdown");
    var otherInput = document.getElementById("other_purpose");
    if (dropdown.value === "Other") {
        otherInput.style.display = "block";
        otherInput.required = true;
        otherInput.focus();
    } else {
        otherInput.style.display = "none";
        otherInput.required = false;
        otherInput.value = "";
    }
}

function setMinCheckout() {
    var checkIn = document.getElementById("check_in").value;
    document.getElementById("check_out").min = checkIn;
}

document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        return;
    }
    var btn = document.getElementById('submitBtn');
    var loader = document.getElementById('btnLoader');
    var text = document.getElementById('btnText');
    btn.disabled = true;
    loader.style.display = 'inline-block';
    text.innerText = 'Submitting...';
});
</script>

</body>
</html>