<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin-top: <?= (int)$margin_top ?>px;
            margin-bottom: <?= (int)$margin_bottom ?>px;
            margin-left: <?= (int)$margin_left ?>px;
            margin-right: <?= (int)$margin_right ?>px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
        }
        .letter-content {
            padding: 0;
        }
        .company-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-logo img {
            max-height: 80px;
            width: auto;
        }
    </style>
</head>
<body>
    <div class="letter-content">
        <?php
        $logo = config_item('company_logo');
        if (!empty($logo)) {
            $logo_path = ROOTPATH . '/' . $logo;
            if (file_exists($logo_path)) {
                $logo_url = base_url() . $logo;
                echo '<div class="company-logo"><img src="' . $logo_url . '"></div>';
            }
        }
        ?>
        <?= $content ?>
    </div>
</body>
</html>
