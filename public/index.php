<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/icon.png">
    <title>Guest Feedback – John Hay Hotels</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:ital,wght@0,700;1,400&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --forest-green: #2d4c31;
            --pine-needle: #1e3522;
            --warm-tan: #f4eee1;
            --gold-accent: #c5a059;
            --text-dark: #2c241e;
            --border-color: #d1c1ad;
            --required-red: #b22222; 
            --input-bg: #f9f9f9;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(rgba(244, 238, 225, 0.8), rgba(244, 238, 225, 0.8)), 
                        url('../img/fo.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: var(--text-dark);
            padding: 40px 15px;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            position: relative;
            z-index: 1;
            padding-bottom: 2rem;
            animation: fadeIn 0.8s ease-out;
            overflow: hidden; 
        }

        .container::before {
            content: '';
            position: absolute;
            top: 15px;
            bottom: 15px;
            left: 15px;
            right: 15px;
            border: 1px solid var(--gold-accent);
            opacity: 0.3;
            pointer-events: none;
            border-radius: 8px;
            z-index: -1;
        }

        .container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 8px;
            background: var(--forest-green);
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .header { 
            text-align: center; 
            padding: 4rem 1.5rem 2rem; 
        }
        
        .header h1 {
            font-family: 'Great Vibes', cursive;
            font-size: clamp(3rem, 10vw, 4.5rem);
            color: var(--forest-green);
            margin-bottom: 0px;
            font-weight: 400;
        }
        
        .header .subline {
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 0.8rem;
            color: var(--gold-accent);
            font-weight: 600;
            margin-top: -10px;
            display: block;
        }
        
        .divider { height: 1px; width: 100px; background: var(--gold-accent); margin: 25px auto; opacity: 0.6; }
        
        .intro { 
            font-family: 'Playfair Display', serif; 
            font-size: 1.1rem; 
            color: #666; 
            max-width: 80%; 
            margin: 0 auto; 
            line-height: 1.6;
            font-style: italic;
            text-align: center;
        }

        .section { 
            margin: 1.5rem 3rem; 
            padding: 2rem; 
            background: #fff; 
            border: 1px solid rgba(197, 160, 89, 0.2); 
            border-radius: 8px;
        }
        
        .section h2 {
            font-family: 'Playfair Display', serif;
            color: var(--forest-green);
            font-size: 1.4rem;
            margin-bottom: 1.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section h2::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(197, 160, 89, 0.3);
        }

        .rating-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 1.4rem; 
        }
        .rating-table th { 
            padding: 15px 8px; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            color: var(--gold-accent); 
            font-weight: 700; 
            text-align: center;
            letter-spacing: 1px;
        }
        .rating-table td { 
            padding: 15px 6px; 
            border-bottom: 1px solid #f0f0f0; 
            text-align: center; 
        }
        .rating-table td:first-child { 
            text-align: left; 
            font-weight: 600; 
            color: var(--text-dark); 
            width: 45%; 
            font-size: 0.9rem; 
        }
        .rating-table input[type="radio"] { 
            accent-color: var(--forest-green); 
            width: 20px; 
            height: 20px; 
            cursor: pointer; 
        }

        textarea, 
        input[type="text"], 
        input[type="email"], 
        input[type="date"], 
        input[type="tel"], 
        select {
            width: 100%; 
            padding: 12px 14px; 
            border: 1px solid #e2e2e2;
            font-family: 'Montserrat', sans-serif; 
            background: var(--input-bg);
            margin-top: 8px; 
            margin-bottom: 1.2rem; 
            border-radius: 8px;
            font-size: 0.9rem;
            color: #444;
            transition: all 0.3s ease;
            display: block;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--gold-accent);
            background-color: #fff;
            box-shadow: 0 0 8px rgba(197, 160, 89, 0.2); 
        }

        label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--forest-green);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        .form-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 0 1.5rem; 
        }
        .full-width { grid-column: span 2; }

        .required-label::after { content: " *"; color: var(--required-red); }

        .optional-header-inline {
            font-weight: 700;
            color: var(--gold-accent);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.75rem;
            margin: 20px 0 10px;
            padding-top: 20px;
            border-top: 1px dashed #ddd;
            text-align: center;
        }

        .submit-btn {
            background: var(--forest-green); 
            color: white; 
            border: none; 
            padding: 18px 50px;
            font-family: 'Montserrat', sans-serif; 
            font-weight: 600; 
            text-transform: uppercase;
            letter-spacing: 3px; 
            cursor: pointer; 
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 3rem auto; 
            width: 90%; 
            max-width: 320px;
            border-radius: 50px;
            transition: all 0.4s;
            box-shadow: 0 10px 20px rgba(45, 76, 49, 0.2);
        }

        .footer-thankyou { 
            text-align: center; 
            font-family: 'Playfair Display', serif; 
            font-style: italic; 
            color: var(--forest-green); 
            padding: 2rem 1rem; 
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            body { padding: 10px 5px; }
            .section { 
                margin: 1rem 0.5rem; 
                padding: 1.5rem 1rem; 
            }
            .header h1 { font-size: 2.8rem; }
            .form-grid { grid-template-columns: 1fr; gap: 0; }
            .full-width { grid-column: span 1; }
            
            .rating-table thead { display: none; }
            .rating-table tr {
                display: block;
                padding: 1rem 0;
                border-bottom: 1px solid #eee;
                text-align: center;
            }
            .rating-table td:first-child {
                text-align: center;
                width: 100% !important;
                display: block;
                margin-bottom: 10px;
            }
            .rating-table td:not(:first-child) {
                display: inline-flex;
                flex-direction: column;
                width: 30%;
                align-items: center;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>John Hay Hotels</h1>
        <span class="subline">Garden Wing</span>
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

            <label style="margin-top:1.2rem;">Especially Helpful Staff:</label>
            <input type="text" name="helpful_staff_names" placeholder="Name/s of staff members">
        </div>

        <div class="section">
            <h2>Additional Comments</h2>
            <label>Suggestions for the future:</label>
            <textarea name="suggestions_future" rows="4" placeholder="How can we make your next visit even better?"></textarea>

            <label style="margin-top:1.2rem;">Other Comments:</label>
            <textarea name="other_comments" rows="4" placeholder="Any additional thoughts..."></textarea>
        </div>

        <div class="section">
            <h2>Your Information</h2>

            <div class="first-stay-prompt" style="margin-bottom: 20px;">
                <label class="required-label">Was this your first stay at John Hay Hotels?</label>
                <div style="display: flex; gap: 2rem; margin-top: 10px; justify-content: flex-start;">
                    <label style="text-transform:none; font-weight:400; display:flex; align-items:center; gap:8px;"><input type="radio" name="first_stay" value="Yes" required> Yes</label>
                    <label style="text-transform:none; font-weight:400; display:flex; align-items:center; gap:8px;"><input type="radio" name="first_stay" value="No" required> No</label>
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
                    <label class="required-label" style="margin-bottom: 10px;">Date/s of stay:</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--gold-accent); font-weight:700; display:block;">Check-in</span>
                            <input type="date" name="check_in" id="check_in" onchange="setMinCheckout()" required>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; color: var(--gold-accent); font-weight:700; display:block;">Check-out</span>
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
            <div id="btnLoader" class="loader" style="border: 2px solid #fff; border-top: 2px solid transparent; width: 16px; height: 16px; border-radius: 50%; display:none; margin-right: 10px; animation: spin 1s linear infinite;"></div>
            <span id="btnText">Submit Feedback</span>
        </button>
    </form>
</div>

<script>
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