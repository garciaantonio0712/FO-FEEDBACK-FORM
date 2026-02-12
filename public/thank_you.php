<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/icon.png">
    <title>Thank You – John Hay Hotels</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --forest-green: #2d4c31;
            --pine-needle: #1e3522;
            --warm-tan: #f4eee1;
            --gold-accent: #c5a059;
            --text-dark: #2c241e;
            --white: #ffffff;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Montserrat', sans-serif;
            /* Exact match background setup */
            background: linear-gradient(rgba(244, 238, 225, 0.8), rgba(244, 238, 225, 0.8)), 
                        url('../img/fo.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .thank-you-card {
            background: var(--white);
            max-width: 600px;
            width: 100%;
            padding: 5rem 3rem;
            position: relative;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            animation: fadeIn 0.8s ease-out;
            z-index: 1;
        }

        .thank-you-card::before {
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

        .thank-you-card::after {
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

        h1 {
            font-family: 'Great Vibes', cursive;
            color: var(--forest-green);
            font-size: clamp(3.5rem, 8vw, 5rem);
            font-weight: 400;
            margin-bottom: 0px;
        }

        .divider {a
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem auto 2.5rem;
        }

        .divider::before, .divider::after {
            content: '';
            width: 40px;
            height: 1px;
            background: var(--gold-accent);
            opacity: 0.6;
        }

        .divider span {
            margin: 0 15px;
            color: var(--gold-accent);
            font-size: 1.2rem;
        }

        p {
            font-size: 1.05rem;
            color: #5a5552;
            margin-bottom: 2rem;
            line-height: 1.8;
            font-weight: 400;
        }

        .signature {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-style: italic;
            color: var(--forest-green);
            margin-top: 2rem;
            line-height: 1.6;
        }

        .back-btn {
            display: inline-block;
            background: var(--forest-green);
            color: #fff;
            padding: 18px 50px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 0.8rem;
            border-radius: 50px;
            transition: all 0.4s ease;
            margin-top: 2rem;
            box-shadow: 0 10px 20px rgba(45, 76, 49, 0.2);
        }

        .back-btn:hover {
            background: var(--gold-accent);
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(197, 160, 89, 0.25);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            h1 { font-size: 3rem; }
            .thank-you-card { padding: 4rem 1.5rem; }
            .divider { margin-bottom: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="thank-you-card">
    <h1>Thank You!</h1>
    
    <div class="divider">
        <span>&#10043;</span> 
    </div>

    <p>Your feedback has been successfully submitted. Your insights are invaluable in helping us maintain the high standards of service at <strong>The John Hay Hotels</strong>.</p>
    
    <p class="signature">
        Warm regards,<br>
        <strong>The Management</strong>
    </p>

    <a href="index.php" class="back-btn">Return to Home</a>
</div>

</body>
</html>