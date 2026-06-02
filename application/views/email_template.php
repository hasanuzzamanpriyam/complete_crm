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
    $logo_path = ROOTPATH . '/' . config_item('company_logo');
    $logo_src = null;
    if (!empty(config_item('company_logo')) && file_exists($logo_path)) {
        $image_data = base64_encode(file_get_contents($logo_path));
        $mime_type = mime_content_type($logo_path);
        $logo_src = 'data:' . $mime_type . ';base64,' . $image_data;
    }
    $accent_color = '#' . (config_item('button_color') ?: '23b7e5');
    ?>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa;">
        <tr>
            <td align="center" style="padding:20px 0;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color:#ffffff;padding:25px 25px;border-bottom:1px solid #f0f4f8;text-align:center;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="vertical-align:middle;text-align:center;">
                                        <?php if ($logo_src): ?>
                                            <img src="<?= $logo_src ?>" alt="<?= htmlspecialchars(config_item('company_name')) ?>" style="max-height:55px;max-width:250px;display:inline-block;vertical-align:middle;">
                                        <?php else: ?>
                                            <h1 style="color:#333333;font-size:20px;font-weight:600;margin:0;display:inline-block;vertical-align:middle;"><?= htmlspecialchars(config_item('company_name')) ?></h1>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 35px;color:#656565;font-size:14px;line-height:1.6;border-top:3px solid <?= $accent_color ?>;">
                            <?= $message ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 25px;background-color:#fafbfc;border-top:1px solid #cfdbe2;text-align:center;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="color:#929292;font-size:12px;line-height:1.4;text-align:center;vertical-align:middle;">
                                        &copy; <a href="<?= htmlspecialchars(config_item('copyright_url')) ?>" style="color:<?= $accent_color ?>;text-decoration:none;"><?= htmlspecialchars(config_item('copyright_name')) ?></a>. All rights reserved.
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
