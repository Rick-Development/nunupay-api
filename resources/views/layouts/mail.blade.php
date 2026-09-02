<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? $site_title }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
        
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .email-header { background-color: #0f172a; padding: 24px; text-align: center; }
        .email-header h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 600; letter-spacing: 0.5px; }
        .email-body { padding: 32px 24px; color: #334155; font-size: 15px; line-height: 1.6; }
        .email-footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .email-footer a { color: #64748b; text-decoration: underline; }

        /* Typography overrides inside dynamic content */
        .email-body h1, .email-body h2, .email-body h3 { color: #0f172a; margin-top: 0; }
        .email-body p { margin-bottom: 16px; }
        .email-body p:last-child { margin-bottom: 0; }
    </style>
</head>
<body style="margin: 0; padding: 40px 0; background-color: #f4f6f9;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <!--[if mso]>
                <table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
                <tr>
                <td align="center" valign="top" width="600">
                <![endif]-->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container">
                    
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <h1>{{ $site_title }}</h1>
                        </td>
                    </tr>

                    <!-- Main Body Content -->
                    <tr>
                        <td class="email-body">
                            {!! $msg !!}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p style="margin: 0 0 8px 0;">&copy; {{ date('Y') }} {{ $site_title }}. All rights reserved.</p>
                            <p style="margin: 0;">This is an automated notification. Please do not reply directly to this email.</p>
                        </td>
                    </tr>

                </table>
                <!--[if mso]>
                </td>
                </tr>
                </table>
                <![endif]-->
            </td>
        </tr>
    </table>

</body>
</html>