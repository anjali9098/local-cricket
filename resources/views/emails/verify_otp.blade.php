<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code - CricketKaScore</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #0b0f17;
            color: #f1f5f9;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 560px;
            margin: 30px auto;
            background-color: #111827;
            border-radius: 16px;
            border: 1px solid #1f2937;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 28px 24px;
            text-align: center;
            border-bottom: 1px solid #334155;
        }
        .header h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 900;
            margin: 8px 0 0 0;
            letter-spacing: -0.02em;
        }
        .header h1 span {
            color: #38bdf8;
        }
        .content {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 16px;
            color: #e2e8f0;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .message {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .otp-box {
            background: #0f172a;
            border: 2px dashed #38bdf8;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }
        .otp-label {
            font-size: 11px;
            font-weight: 800;
            color: #38bdf8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 8px;
        }
        .otp-code {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 10px;
            color: #ffffff;
            font-family: 'Courier New', Courier, monospace;
        }
        .expiry-note {
            font-size: 12px;
            color: #f59e0b;
            margin-top: 10px;
            font-weight: 600;
        }
        .security-note {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            border-top: 1px solid #1f2937;
            padding-top: 18px;
            margin-top: 24px;
        }
        .footer {
            background-color: #0b0f17;
            padding: 18px 24px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #1f2937;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div style="font-size: 28px; margin-bottom: 4px;">🏏</div>
            <h1>CRICKET<span>KASCORE</span></h1>
        </div>
        <div class="content">
            <div class="greeting">Hello {{ $user->name }},</div>
            <p class="message">
                Thank you for joining <strong>CricketKaScore</strong>! To verify your email address and activate your cricket fan & scoring account, please enter the following 6-digit verification code:
            </p>
            
            <div class="otp-box">
                <div class="otp-label">Your One-Time Password (OTP)</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="expiry-note">⏱️ This code is valid for 2 minutes only.</div>
            </div>

            <p class="message" style="margin-bottom: 0;">
                If you did not request this registration, you can safely ignore this email. No action is required.
            </p>

            <div class="security-note">
                🔒 <strong>Security Tip:</strong> Never share this OTP with anyone. CricketKaScore will never ask for your verification code.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CricketKaScore. All rights reserved. &bull; Every Ball Counts.
        </div>
    </div>
</body>
</html>
