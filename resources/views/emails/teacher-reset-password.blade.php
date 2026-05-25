<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request | SMASA</title>
    <style>
        /* Email-safe inline-friendly styles */
        body {
            margin: 0; padding: 0;
            background-color: #f4f4f5;
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            font-size: 15px; color: #18181b;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #2C29CA 0%, #14136e 100%);
            padding: 36px 40px 28px;
            text-align: center;
        }

        .header-logo {
            font-size: 2rem; font-weight: 800;
            color: #ffffff; letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .header-logo span { color: #93c5fd; }

        .header-subtitle {
            color: rgba(255,255,255,0.78);
            font-size: 0.88rem; font-weight: 500;
        }

        /* Lock icon circle */
        .icon-wrap {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 20px auto 0;
            border: 2px solid rgba(255,255,255,0.25);
        }

        .icon-wrap svg { width: 36px; height: 36px; fill: #ffffff; }

        /* Body */
        .body { padding: 36px 40px; }

        .greeting {
            font-size: 1.1rem; font-weight: 600;
            margin-bottom: 14px; color: #18181b;
        }

        .body p {
            color: #3f3f46; line-height: 1.75;
            margin-bottom: 16px;
        }

        /* School badge */
        .school-badge {
            display: inline-block;
            background: rgba(44, 41, 202, 0.07);
            color: #14136e; font-weight: 700;
            padding: 5px 14px; border-radius: 20px;
            font-size: 0.88rem; margin-bottom: 20px;
        }

        /* CTA Button */
        .cta-wrap { text-align: center; margin: 28px 0; }

        .cta-btn {
            display: inline-block;
            background: #2C29CA; color: #ffffff !important;
            text-decoration: none; font-weight: 700;
            font-size: 1rem; padding: 14px 36px;
            border-radius: 50px;
            box-shadow: 0 6px 16px rgba(44, 41, 202, 0.30);
        }

        .cta-btn:hover { background: #14136e; }

        /* URL fallback */
        .url-fallback {
            background: #f4f4f5; border-radius: 10px;
            padding: 14px 18px; margin: 0 0 20px;
            font-size: 0.82rem; word-break: break-all;
            color: #2C29CA; border: 1px solid #e4e4e7;
        }

        /* Expiry notice */
        .expiry-notice {
            background: rgba(217, 119, 6, 0.07);
            border-left: 3px solid #d97706;
            border-radius: 8px;
            padding: 12px 16px; margin-bottom: 20px;
            font-size: 0.88rem; color: #92400e;
        }

        .expiry-notice strong { display: block; margin-bottom: 3px; }

        /* Security list */
        .security-list {
            list-style: none; padding: 0; margin: 0 0 20px;
        }

        .security-list li {
            display: flex; align-items: flex-start; gap: 10px;
            color: #3f3f46; font-size: 0.88rem; padding: 5px 0;
            line-height: 1.6;
        }

        .security-list li::before {
            content: '✓';
            color: #16a34a; font-weight: 700;
            flex-shrink: 0; margin-top: 1px;
        }

        /* Divider */
        .divider { border: none; border-top: 1px solid #e4e4e7; margin: 24px 0; }

        /* Footer */
        .footer {
            background: #f4f4f5; padding: 24px 40px;
            text-align: center; font-size: 0.82rem; color: #71717a;
            line-height: 1.7;
        }

        .footer a { color: #2C29CA; text-decoration: none; }

        .ignore-notice {
            font-size: 0.82rem; color: #71717a;
            font-style: italic; margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- Header -->
        <div class="header">
            <div class="header-logo">SM<span>A</span>SA</div>
            <div class="header-subtitle">School Management & Academic System</div>
            <div class="icon-wrap">
                <!-- Lock SVG (email-safe, no FA needed) -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                </svg>
            </div>
        </div>

        <!-- Body -->
        <div class="body">

            <div class="greeting">Hello {{ $teacherName }},</div>

            <p>
                We received a request to reset the password for your SMASA teacher account
                associated with the school below. If you made this request, click the button
                to set a new password.
            </p>

            <div>
                <span class="school-badge">🏫 {{ $schoolName }}</span>
            </div>

            <!-- CTA -->
            <div class="cta-wrap">
                <a href="{{ $resetUrl }}" class="cta-btn">
                    Reset My Password
                </a>
            </div>

            <!-- Expiry notice -->
            <div class="expiry-notice">
                <strong>⏱ Link expires in {{ $expiryMins }} minutes</strong>
                This link can only be used once and will expire {{ $expiryMins }} minutes after this email was sent.
                After expiry, you will need to submit a new password reset request.
            </div>

            <!-- URL fallback -->
            <p style="font-size:0.88rem; color:#71717a; margin-bottom:8px;">
                If the button above doesn't work, copy and paste this URL into your browser:
            </p>
            <div class="url-fallback">{{ $resetUrl }}</div>

            <hr class="divider">

            <!-- Security notes -->
            <p style="font-weight:600; margin-bottom:10px;">Security reminders:</p>
            <ul class="security-list">
                <li>This link is single-use — it becomes invalid immediately after your password is reset.</li>
                <li>Never share this link with anyone, including school administrators.</li>
                <li>SMASA staff will never ask you for your password or reset link.</li>
                <li>If you didn't request this reset, your account is safe — simply ignore this email.</li>
            </ul>

            <p class="ignore-notice">
                Didn't request a password reset? No action is needed. Your password remains unchanged
                and this link will expire automatically.
            </p>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                This email was sent to <strong>{{ $teacherEmail ?? 'you' }}</strong>
                because a password reset was requested for your account at <strong>{{ $schoolName }}</strong>.
            </p>
            <p style="margin-top:10px;">
                &copy; {{ date('Y') }} SMASA — School Management &amp; Academic System.
                All rights reserved.
            </p>
        </div>

    </div>
</body>
</html>
