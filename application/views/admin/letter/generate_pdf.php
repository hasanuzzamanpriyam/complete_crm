<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 2.54cm;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .letter-content {
                width: 100%;
                min-height: 100%;
                padding: <?= (int)$margin_top ?>px <?= (int)$margin_right ?>px <?= (int)$margin_bottom ?>px <?= (int)$margin_left ?>px;
                margin: 0 auto;
                box-sizing: border-box;
            }
            .no-print { display: none !important; }
            .hide-logo .company-logo { display: none !important; }
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
    <div class="letter-content<?= !empty($hide_logo) ? ' hide-logo' : '' ?>">
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
    <?php if (!empty($print)): ?>
        <script type="text/javascript">
            window.onload = function() {
                window.print();
                window.onafterprint = function() {
                    window.close();
                }
            }
        </script>
    <?php endif; ?>
</body>
</html>
