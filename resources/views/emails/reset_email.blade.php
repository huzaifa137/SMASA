<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Reset your password' }} | SMASA</title>
</head>

<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#18181b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:#2C29CA;padding:28px 32px;text-align:center;">
                            <div style="font-size:26px;font-weight:700;color:#ffffff;letter-spacing:1px;">SMASA</div>
                            <div style="font-size:13px;color:#d6d6ff;margin-top:4px;">Password reset request</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;">Hello{{ !empty($username) ? ' ' . $username : '' }},</p>
                            <p style="margin:0 0 16px;line-height:1.6;">
                                We received a request to reset the password for the account linked to
                                <strong>{{ $email ?? '' }}</strong>. Click the button below to choose a new password.
                            </p>
                            <p style="margin:28px 0;text-align:center;">
                                <a href="{{ $resetUrl }}"
                                    style="background:#2C29CA;color:#ffffff;text-decoration:none;padding:13px 28px;border-radius:8px;font-weight:700;display:inline-block;">
                                    Reset password
                                </a>
                            </p>
                            <p style="margin:0 0 8px;line-height:1.6;font-size:13px;color:#52525b;">
                                If the button does not work, copy and paste this link into your browser:
                            </p>
                            <p style="margin:0 0 20px;font-size:13px;word-break:break-all;">
                                <a href="{{ $resetUrl }}" style="color:#2C29CA;">{{ $resetUrl }}</a>
                            </p>
                            <p style="margin:0;line-height:1.6;font-size:13px;color:#52525b;">
                                If you did not request this, you can safely ignore this email. Your password will not change.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#fafafa;padding:16px 32px;text-align:center;font-size:12px;color:#71717a;">
                            &copy; {{ date('Y') }} SMASA. This is an automated message, please do not reply.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
