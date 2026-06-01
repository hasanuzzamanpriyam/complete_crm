<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= !empty($subject) ? $subject : config_item('company_name') ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f5f7fa;font-family:'Source Sans Pro',Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">
    <?php
    $logo_path = ROOTPATH . config_item('company_logo');
    $logo_src = null;
    if (!empty(config_item('company_logo')) && file_exists($logo_path)) {
        $logo_data = base64_encode(file_get_contents($logo_path));
        $logo_mime = mime_content_type($logo_path);
        $logo_src = 'data:' . $logo_mime . ';base64,' . $logo_data;
    }
    $accent_color = '#' . (config_item('button_color') ?: '23b7e5');
    ?>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa;">
        <tr>
            <td align="center" style="padding:20px 0;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background:linear-gradient(to right,#23b7e5,#51c6ea);padding:15px 25px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <?php if ($logo_src): ?>
                                    <td style="width:60px;vertical-align:middle;">
                                        <img src="<?= $logo_src ?>" alt="<?= htmlspecialchars(config_item('company_name')) ?>" style="max-height:50px;max-width:200px;display:block;">
                                    </td>
                                    <td style="vertical-align:middle;padding-left:15px;">
                                    <?php else: ?>
                                    <td style="vertical-align:middle;">
                                    <?php endif; ?>
                                        <h1 style="color:#ffffff;font-size:18px;font-weight:600;margin:0;"><?= htmlspecialchars(config_item('company_name')) ?></h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 35px;color:#656565;font-size:14px;line-height:1.6;border-top:3px solid #23b7e5;">
                            <?= $message ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:15px 25px;background-color:#fafbfc;border-top:1px solid #cfdbe2;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#929292;font-size:12px;line-height:1.4;">
                                        &copy; <a href="<?= htmlspecialchars(config_item('copyright_url')) ?>" style="color:#23b7e5;text-decoration:none;"><?= htmlspecialchars(config_item('copyright_name')) ?></a>. All rights reserved.
                                    </td>
                                    <td style="color:#929292;font-size:12px;line-height:1.4;text-align:right;">
                                        <b>Version</b> <?= htmlspecialchars(config_item('version')) ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
