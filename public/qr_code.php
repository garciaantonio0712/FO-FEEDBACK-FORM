<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback QR Code Generator</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --forest-green: #2d4c31;
            --gold-accent: #c5a059;
            --warm-tan: #f4eee1;
        }

        body {
            background-color: var(--warm-tan);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
        }

        .qr-card {
            text-align: center;
            padding: 50px 40px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            max-width: 400px;
            width: 90%;
            position: relative;
            border: 1px solid rgba(197, 160, 89, 0.3);
        }

        .qr-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 6px;
            background: var(--forest-green);
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
        }

        .qr-title { 
            font-family: 'Playfair Display', serif; 
            color: var(--forest-green); 
            margin-bottom: 5px;
            font-size: 1.8rem;
        }

        .sub-text {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 25px;
            font-style: italic;
        }

        /* Container for the new canvas QR */
        #qrcode-container { 
            display: inline-block; 
            padding: 15px; 
            background: white;
            border: 2px solid var(--warm-tan);
            margin: 10px 0 25px 0;
        }

        /* Hide the library's default output */
        #qrcode-raw {
            display: none;
        }

        canvas#final-qr {
            display: block;
            width: 220px;
            height: 220px;
        }

        .print-btn {
            background: var(--forest-green); 
            color: white; 
            border: none; 
            padding: 12px 30px;
            border-radius: 50px; 
            cursor: pointer; 
            text-transform: uppercase; 
            font-weight: 600;
            font-size: 0.8rem;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(45, 76, 49, 0.2);
            display: block;
            margin: 0 auto;
        }

        .print-btn:hover {
            background: #3e6643;
            transform: translateY(-2px);
        }

        @media print {
            body { background: white; }
            .print-btn { display: none; }
            .qr-card { box-shadow: none; border: 1px solid #eee; margin-top: 0; }
        }
    </style>
</head>
<body>

<div class="qr-card">
    <h2 class="qr-title">Guest Feedback</h2>
    <p class="sub-text">Scan to share your experience</p>
    
    <div id="qrcode-container">
        <div id="qrcode-raw"></div>
        <canvas id="final-qr"></canvas>
    </div>
    
    <button class="print-btn" onclick="window.print()">Print QR Code</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    var formUrl = "http://172.31.193.173/frontoffice_feedback/public/index.php"; 
    var canvasSize = 600; // Render high-res for printing

    // 1. Generate the raw QR code in a hidden element
    var qrcode = new QRCode(document.getElementById("qrcode-raw"), {
        text: formUrl,
        width: canvasSize,
        height: canvasSize,
        colorDark : "#2d4c31",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H // High error correction is vital for logo center
    });

    // 2. Overlay the Logo onto a final Canvas
    setTimeout(function() {
        var rawCanvas = document.querySelector('#qrcode-raw canvas');
        var finalCanvas = document.getElementById('final-qr');
        var ctx = finalCanvas.getContext('2d');
        
        finalCanvas.width = canvasSize;
        finalCanvas.height = canvasSize;

        // Draw the QR code
        ctx.drawImage(rawCanvas, 0, 0);

        // Load the John Hay Logo
        var logo = new Image();
        logo.src = "../img/icon.png"; 
        
        logo.onload = function() {
            // Calculate size (approx 20% of QR size)
            var logoSize = canvasSize * 0.2;
            var x = (canvasSize - logoSize) / 2;
            var y = (canvasSize - logoSize) / 2;

            // White background for logo to ensure it's scannable
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(x - 5, y - 5, logoSize + 10, logoSize + 10);

            // Draw the logo in the middle
            ctx.drawImage(logo, x, y, logoSize, logoSize);
        };
    }, 500); // Small delay to ensure library finishes rendering
</script>

</body>
</html>