<div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('filemanager') ?></h1>
        </div>
        
        <div class="cl-embed-frame" id="elfinder-wrapper">
            <!-- elFinder CSS/JS (duplicate jQuery loads kept — remove only after browser-test confirms elFinder still initializes) -->
            <link rel="stylesheet" type="text/css"
                  href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css"/>
            <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
            <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
            <link rel="stylesheet" type="text/css" media="screen"
                  href="<?php echo site_url('assets/plugins/elFinder/css/elfinder.min.css'); ?>">
            <link rel="stylesheet" type="text/css" media="screen"
                  href="<?php echo site_url('assets/plugins/elFinder/themes/Material/css/theme.css'); ?>">
            <link rel="stylesheet" type="text/css" media="screen"
                  href="<?php echo site_url('assets/plugins/elFinder/themes/Material/css/theme-light.css'); ?>">
            
            <script src="<?php echo site_url('assets/plugins/elFinder/js/elfinder.min.js'); ?>"></script>
            <script type="text/javascript" charset="utf-8">
                $().ready(function () {
                    window.setTimeout(function () {
                        var elf = $('#elfinder').elfinder({
                            // lang: 'ru',             // language (OPTIONAL)
                            url: '<?= site_url() ?>admin/client/elfinder_init/<?= $client_details->client_id ?>', // connector URL (REQUIRED)
                            height: 600,
                            uiOptions: {
                                // toolbar configuration
                                toolbar: [
                                    ['back', 'forward'],
                                    //                     ['mkdir'],
                                    ['mkdir', 'mkfile', 'upload'],
                                    ['open', 'download', 'getfile'],
                                    ['info'],
                                    ['quicklook'],
                                    ['copy', 'cut', 'paste'],
                                    ['rm'],
                                    ['duplicate', 'rename', 'edit', 'resize'],
                                    ['extract', 'archive'],
                                    ['search'],
                                    ['view'],
                                ],
                            }

                        }).elfinder('instance');
                    }, 200);
                });
            </script>
            <div id="elfinder" style="height: 600px;"></div>
        </div>
    </div>