<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Verify your ShopHop email</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-shell { padding: 24px 12px !important; }
            .email-content { padding: 28px 24px !important; }
            .email-heading { font-size: 26px !important; }
            .email-code { font-size: 32px !important; letter-spacing: 7px !important; padding-left: 7px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#F3F5F7;color:#0F1B3D;font-family:'Poppins','Segoe UI',Arial,sans-serif;-webkit-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;line-height:1px;color:#F3F5F7;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">Your ShopHop verification code expires in {{ $expiresInMinutes }} minutes. Complete your {{ strtolower($accountLabel) }} email verification.</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#F3F5F7;">
        <tr>
            <td align="center" class="email-shell" style="padding:40px 20px;">
                <!--[if mso]><table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:560px;">
                    <tr>
                        <td style="padding:0 4px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-size:27px;font-weight:800;letter-spacing:-1px;line-height:32px;color:#0F1B3D;">Shop<span style="color:#18A98E;">Hop</span></td>
                                    <td align="right" style="font-size:10px;font-weight:700;letter-spacing:1.4px;color:#617087;line-height:16px;">HOP IN.<br>SHOP MORE.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#FFFFFF;border:1px solid #E2E6EA;border-radius:20px;overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                                <tr><td height="5" style="height:5px;background-color:#21C3A6;border-radius:20px 20px 0 0;font-size:1px;line-height:5px;">&nbsp;</td></tr>
                                <tr>
                                    <td class="email-content" style="padding:36px 40px 32px;">
                                        <p style="margin:0 0 14px;color:#118371;font-size:10px;font-weight:700;letter-spacing:1.8px;line-height:16px;text-transform:uppercase;">{{ $accountLabel }} account &nbsp;/&nbsp; Email verification</p>
                                        <h1 class="email-heading" style="margin:0 0 16px;font-size:30px;line-height:1.25;font-weight:750;letter-spacing:-0.8px;color:#0F1B3D;">Verify your email address</h1>
                                        <p style="margin:0 0 10px;font-size:14px;line-height:24px;color:#526178;">{{ $introLines[0] }}</p>
                                        <p style="margin:0 0 26px;font-size:14px;line-height:24px;color:#526178;">Enter the code below on the ShopHop verification page to continue your registration.</p>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;background-color:#EFFAF7;border:1px solid #CDEDE5;border-radius:14px;">
                                            <tr>
                                                <td align="center" style="padding:22px 12px 20px;">
                                                    <p style="margin:0 0 10px;font-size:10px;line-height:16px;font-weight:700;letter-spacing:1.6px;color:#118371;">YOUR VERIFICATION CODE</p>
                                                    <p class="email-code" style="margin:0;padding-left:10px;font-family:'Courier New',monospace;font-size:38px;line-height:48px;font-weight:700;letter-spacing:10px;color:#0F1B3D;">{{ $verificationCode }}</p>
                                                    <p style="margin:10px 0 0;font-size:12px;line-height:20px;color:#526178;">Expires in <strong style="color:#0F1B3D;">{{ $expiresInMinutes }} minutes</strong></p>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin:24px 0;font-size:13px;line-height:22px;color:#526178;">{{ $introLines[count($introLines) - 1] }}</p>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-top:1px solid #E2E6EA;">
                                            <tr>
                                                <td style="padding-top:22px;">
                                                    <p style="margin:0 0 5px;font-size:12px;font-weight:700;line-height:20px;color:#0F1B3D;">Keep your code private</p>
                                                    <p style="margin:0;font-size:12px;line-height:20px;color:#617087;">ShopHop will never ask you to share this code. If you request a new code, use the most recent one.</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:22px 18px 0;">
                            <p style="margin:0 0 8px;font-size:11px;line-height:19px;color:#617087;">If you did not request this code, you can safely ignore this email.</p>
                            <p style="margin:0;font-size:10px;line-height:18px;color:#617087;">&copy; {{ date('Y') }} ShopHop. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
