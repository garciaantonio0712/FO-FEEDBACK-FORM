<!DOCTYPE html>
<html>
<head>
    <title>Feedback QR Code Generator</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Montserrat&display=swap" rel="stylesheet">
    <style>
        .qr-card {
            text-align: center;
            padding: 40px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 400px;
            margin: 50px auto;
            font-family: 'Montserrat', sans-serif;
        }
        .qr-title { font-family: 'Playfair Display', serif; color: #2d4c31; margin-bottom: 10px; }
        #qrcode { display: inline-block; padding: 20px; border: 1px solid #eee; margin: 20px 0; }
        .print-btn {
            background: #2d4c31; color: white; border: none; padding: 10px 20px;
            border-radius: 5px; cursor: pointer; text-transform: uppercase; font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="qr-card">
    <h2 class="qr-title">Guest Feedback Link</h2>
    <p style="font-size: 0.85rem; color: #777;">Scan to access the digital form</p>
    
    <div id="qrcode"></div>
    
    <p id="url-text" style="font-size: 0.7rem; color: #c5a059; font-weight: 700; word-break: break-all; margin-bottom: 20px;"></p>
    
    <button class="print-btn" onclick="window.print()">Print QR Code</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // Set your feedback form URL here
    var formUrl = window.location.origin + "/path-to-your/feedback_form.html"; 
    
    new QRCode(document.getElementById("qrcode"), {
        text: formUrl,
        width: 200,
        height: 200,
        colorDark : "#2d4c31",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
    document.getElementById('url-text').innerText = formUrl;
</script>
</body>
</html>