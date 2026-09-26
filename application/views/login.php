<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo $title; ?></title>
    <?php if (config_item('favicon') != '') : ?>
        <link rel="icon" href="<?php echo base_url() . config_item('favicon'); ?>" type="image/png">
    <?php else: ?>
        <link rel="icon" href="<?php echo base_url('assets/img/favicon.ico'); ?>" type="image/png">
    <?php endif; ?>

    <!-- =============== VENDOR STYLES ===============-->
    <!-- FONT AWESOME-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/fontawesome/css/font-awesome.min.css">
    <!-- Toastr-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/toastr.min.css">
    <!-- =============== BOOTSTRAP STYLES ===============-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" id="bscss">
    <!-- =============== APP STYLES ===============-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/app.min.css?v=2" id="maincss">

    <!-- JQUERY-->
    <script src="<?php echo base_url(); ?>assets/plugins/jquery/dist/jquery.min.js?v=2"></script>

    <?php if (config_item('recaptcha_secret_key') != '' && config_item('recaptcha_site_key') != '') { ?>
        <script src='https://www.google.com/recaptcha/api.js'></script>
    <?php } ?>
    <?php

    $this->load->helper('file');
    $lbg = config_item('login_background');
    if (!empty($lbg)) {
        $login_background = _mime_content_type($lbg);
        $login_background = explode('/', $login_background);
    }
    ?>
    <?php if (!empty($login_background[0]) && $login_background[0] == 'video') { ?>
        <style type="text/css">
            body {
                padding: 0;
                margin: 0;
                background-color: #ffffff;
            }

            .vid-container {
                position: relative;
                height: 100vh;
                overflow: hidden;
            }

            .bgvid.back {
                position: fixed;
                right: 0;
                bottom: 0;
                min-width: 100%;
                min-height: 100%;
                width: auto;
                height: auto;
                z-index: -100;
            }

            .inner {
                position: absolute;
            }

            .inner-container {
                width: 400px;
                height: 400px;
                position: absolute;
                top: calc(50vh - 200px);
                left: calc(50vw - 200px);
                overflow: hidden;
            }

            .bgvid.inner {
                width: 100%;
                /*border: 10px solid red;*/
                min-height: 100%;
                height: auto;
            }

            .box {
                position: absolute;
                height: 100%;
                width: 100%;
                font-family: Helvetica;
                color: #fff;
                background: rgba(0, 0, 0, 0.13);
                padding: 30px 0px;
            }

            .box h1 {
                text-align: center;
                margin: 30px 0;
                font-size: 30px;
            }

            .panel {
                background: transparent;

            }

            input {
                background: rgba(0, 0, 0, 0.2);
                color: #fff;
                border: 0;
            }

            input:focus, input:active, button:focus, button:active {
                outline: none;
            }

            .box button:active {
                background: #27ae60;
            }

            .box p {
                font-size: 14px;
                text-align: center;
            }

            .box p span {
                cursor: pointer;
                color: #666;
            }
        </style>
    <?php } ?>
</head>
<?php
$login_position = config_item('login_position');
if (!empty($login_background[0]) && $login_background[0] == 'image') {
    $login_background = config_item('login_background');
    if (!empty($login_background)) {
        $back_img = base_url() . '/' . config_item('login_background');
    }
} ?>
<style>
    body {
        background-color: #ffffff;
    }

    .left-login {
        height: auto;
        min-height: 100%;
        background: #fff;
        -webkit-box-shadow: 2px 0px 7px 1px rgba(0, 0, 0, 0.08);
        -moz-box-shadow: 2px 0px 7px 1px rgba(0, 0, 0, 0.08);
        box-shadow: 2px 0px 7px 1px rgba(0, 0, 0, 0.08);
    }

    .left-login-panel {
        -webkit-box-shadow: 0px 0px 28px -9px rgba(0, 0, 0, 0.74);
        -moz-box-shadow: 0px 0px 28px -9px rgba(0, 0, 0, 0.74);
        box-shadow: 0px 0px 28px -9px rgba(0, 0, 0, 0.74);
    }

    /* TimeSync promo — an in-flow card, deliberately NOT a Bootstrap modal.
       A .modal adds body.modal-open (overflow:hidden, page stops scrolling) and
       stacks a position:fixed z-index:1050 container over the whole viewport, so
       clicks aimed at the Sign in button were swallowed by the modal layer. */
    .tracker-banner {
        margin: 0 0 20px;
        padding: 14px 16px;
        background: #fff;
        border: 1px solid #e4eaec;
        border-left: 3px solid #5d9cec;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        text-align: center;
        color: #515253;
    }

    .tracker-banner[hidden] {
        display: none;
    }

    .tracker-banner-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 6px;
    }

    .tracker-banner-head strong {
        font-size: 14px;
        color: #1e1e2d;
    }

    .tracker-banner-close {
        border: 0;
        background: none;
        font-size: 20px;
        line-height: 1;
        color: #a6a7a9;
        cursor: pointer;
        padding: 0 2px;
    }

    .tracker-banner-close:hover,
    .tracker-banner-close:focus {
        color: #515253;
        outline: none;
    }

    .tracker-banner p {
        margin: 0 0 10px;
        font-size: 13px;
        color: #777;
    }

    .tracker-banner-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        margin-bottom: 10px;
    }

    .tracker-banner-actions a {
        flex: 1 1 0;
        min-width: 0;
        padding: 8px 4px;
        border-radius: 3px;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        color: #fff;
    }

    .tracker-banner-actions a:hover {
        opacity: .88;
    }

    #dl-win   { background: #27c24c; }
    #dl-mac   { background: #23b7e5; }
    #dl-linux { background: #ff902b; }

    .tracker-banner .dismiss {
        margin: 0;
        font-size: 12px;
        color: #777;
    }

    .tracker-banner .dismiss a {
        color: #5d9cec;
    }

    .apply_jobs {
        position: absolute;
        z-index: 1;
        right: 0;
        top: 0
    }

    .login-center {
        background: #fff;
        width: 400px;
        margin: 0 auto;
    }

    @media only screen and (max-width: 380px) {
        .login-center {
            width: 320px;
            padding: 10px;
        }

        .wd-xl {
            width: 260px;
        }
    }
</style>
<?php

if (!empty($login_position) && $login_position == 'center') {
    if (!empty($back_img)) {
        $body_style = 'style="background: url(' . $back_img . ') no-repeat center center fixed;
 -webkit-background-size: cover;
 -moz-background-size: cover;
 -o-background-size: cover;
 background-size: cover;min-height: 100%;width:100%"';
    } else {
        $body_style = '';
    }
} else {
    $body_style = '';
}
$type = $this->session->userdata('c_message');
?>
<body <?= $body_style ?>>
<?php if (!empty($login_position) && $login_position == 'left') {
    $lcol = 'col-lg-4 col-sm-6 left-login';
} else if (!empty($login_position) && $login_position == 'right') {
    $lcol = 'col-lg-4 col-sm-6 left-login pull-right';
} else {
    $lcol = 'login-center';
} ?>
<div class="<?= $lcol ?>">
    <?php if (config_item('enable_languages') == 'TRUE') { ?>
        <ul class="nav navbar-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-flag"></i> <?= lang('languages') ?>
                </a>
                <ul class="dropdown-menu animated zoomIn">
                    <?php
                    $languages = $this->db->where('active', 1)->order_by('name', 'ASC')->get('tbl_languages')->result();
                    foreach ($languages as $lang) :
                        ?>
                        <li>
                            <a href="<?= base_url() ?>admin/common/set_language/<?= $lang->name ?>"
                               title="<?= ucwords(str_replace("_", " ", $lang->name)) ?>">
                                <img src="<?= base_url() ?>asset/images/flags/<?= $lang->icon ?>.gif"
                                     alt="<?= ucwords(str_replace("_", " ", $lang->name)) ?>"/> <?= ucwords(str_replace("_", " ", $lang->name)) ?>
                            </a>
                        </li>
                    <?php
                    endforeach;
                    ?>

                </ul>
            </li>
        </ul>
    <?php } ?>
    <div class="wrapper" style="margin: 20% 0 0 auto">
        <div class="block-center mt-xl wd-xl">
            <div class="text-center" style="margin-bottom: 20px">
                <img style="width: 100%;"
                     src="<?= base_url() . config_item('company_logo') ?>" class="m-r-sm">
            </div>
            <?php if ((string)config_item('consultation_booking_enabled') === '1') { ?>
                <div class="text-center" style="margin-bottom: 20px">
                    <a href="<?= base_url() ?>book-free-consultation" class="btn btn-success btn-block btn-lg">
                        <i class="fa fa-calendar-check-o"></i> Book a Free Consultation
                    </a>
                </div>
            <?php } ?>
            <?= message_box('success'); ?>
            <?= message_box('error'); ?>
            <div class="error_login">
                <?php
                $validation_errors = validation_errors();
                if (!empty($validation_errors)) { ?>
                    <div class="alert alert-danger"><?php echo $validation_errors; ?></div>
                    <?php
                }
                $error = $this->session->flashdata('error');
                $success = $this->session->flashdata('success');
                if (!empty($error)) {
                    ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php } ?>
                <?php if (!empty($success)) { ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php } ?>
            </div>
            <!-- START panel-->
            <?php if (config_item('logo_or_icon') == 'logo_title') { ?>
            <div class="panel panel-dark panel-flat left-login-panel">
                <div class="panel-heading text-center">
                    <a href="#" style="color: #ffffff">
                   <span style="font-size: 15px;"><?= config_item('company_name') ?>
                    </a>
                </div>
                <?php if (!empty($type)) {
                    ?>
                    <script>
                        $(document).ready(function () {
                            // show when page load
                            toastr.success('<?= lang($type)?>');
                        });
                    </script>
                    <?php
                    $this->session->unset_userdata('c_message');
                } ?>

                <div class="panel-body">
                    <?php } ?>
                    <?= $subview; ?>
                    <?php if (config_item('logo_or_icon') == 'logo_title') { ?>
                </div>
            </div>
        <?php } ?>
            <!-- END panel-->
            <!-- TimeSync promo. Rendered in normal document flow so it can never
                 cover or intercept the sign-in form. Hidden until JS confirms the
                 user has not dismissed it. -->
            <div id="tracker-download-banner" class="tracker-banner" hidden>
                <div class="tracker-banner-head">
                    <strong>Download TimeSync Desktop Tracker</strong>
                    <button type="button" class="tracker-banner-close"
                            id="tracker-download-close"
                            aria-label="Close">&times;</button>
                </div>
                <p>Track your time effortlessly with the TimeSync desktop app.</p>
                <div id="tracker-download-buttons" class="tracker-banner-actions">
                    <a id="dl-win" href="<?= base_url() ?>api/updates/timesync/download?platform=windows">
                        Windows<br><small>.exe installer</small>
                    </a>
                    <a id="dl-mac" href="<?= base_url() ?>api/updates/timesync/download?platform=macos">
                        macOS<br><small>.dmg</small>
                    </a>
                    <a id="dl-linux" href="<?= base_url() ?>api/updates/timesync/download?platform=linux">
                        Linux<br><small>.AppImage</small>
                    </a>
                </div>
                <p class="dismiss">
                    <a href="#" id="tracker-download-dismiss">I already have it &mdash; don't show again</a>
                </p>
            </div>
            <div class="p-lg text-center">
                <span>&copy;</span>
                <span><a href="<?= config_item('copyright_url') ?>"> <?= config_item('copyright_name') ?></a></span>
                <br/>
                <span>2015-<?= date('Y') ?></span>
                <span>-</span>
                <span><?= lang('version') . ' ' . config_item('version') ?></span>
            </div>
        </div>
    </div>
</div>
<?php if (!empty(config_item('allow_apply_job_from_login'))) { ?>
    <a class="pull-right btn btn-info btn-lg mt0 apply_jobs"
       href="<?= base_url() ?>career"><?= lang('apply_for_a_job') ?></a>
<?php } ?>
<?php
if (!empty($login_position) && $login_position == 'left') {
    $col = 'col-lg-8 col-sm-6';
    if (!empty($back_img)) {
        $leftstyle = 'style="background: url(' . $back_img . ') no-repeat center center fixed;
 -webkit-background-size: cover;
 -moz-background-size: cover;
 -o-background-size: cover;
 background-size: cover;min-height: 100%;"';
    } else {
        $leftstyle = '';
    }
} else if (!empty($login_position) && $login_position == 'right') {
    $col = 'col-lg-8 col-sm-6 left-login pull-right';
    if (!empty($back_img)) {
        $leftstyle = 'style="background: url(' . $back_img . ') no-repeat center center fixed;
 -webkit-background-size: cover;
 -moz-background-size: cover;
 -o-background-size: cover;
 background-size: cover;min-height: 100%;"';
    } else {
        $leftstyle = '';
    }
} else {
    $col = '';
    $leftstyle = '';
}
?>

<div class="<?= $col ?> hidden-xs" <?= $leftstyle ?>>
    <?php if (!empty($login_background[0]) && $login_background[0] == 'video') { ?>
        <video class="bgvid inner" autoplay="autoplay" muted="muted" preload="auto" loop>
            <source
                    src="<?php echo base_url() . config_item('login_background'); ?>"
                    type="video/webm">
        </video>
    <?php } ?>
</div>

<!-- =============== VENDOR SCRIPTS ===============-->

<!-- =============== Toastr ===============-->
<script src="<?= base_url() ?>assets/js/toastr.min.js"></script>
<!-- BOOTSTRAP-->
<script src="<?php echo base_url(); ?>assets/plugins/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- STORAGE API-->
<script src="<?php echo base_url(); ?>assets/plugins/jQuery-Storage-API/jquery.storageapi.min.js"></script>
<script src="<?php echo base_url() ?>assets/plugins/parsleyjs/parsley.min.js"></script>

<script>
$(function () {
    var KEY = 'tracker_download_dismissed';
    var $banner = $('#tracker-download-banner');

    // localStorage throws in some privacy modes; never let that break the page.
    function alreadyDismissed() {
        try { return localStorage.getItem(KEY) === 'true'; } catch (e) { return false; }
    }
    function remember() {
        try { localStorage.setItem(KEY, 'true'); } catch (e) {}
    }

    // Reveal the promo unless dismissed. It is an ordinary in-flow card, so no
    // Bootstrap modal is opened: body never gains modal-open (overflow:hidden)
    // and no fixed z-index layer is stacked over the sign-in form.
    if (!alreadyDismissed()) {
        $banner.prop('hidden', false);
    }

    // BOTH dismiss paths persist. Previously only the "don't show again" link
    // wrote the flag, so closing with x brought the promo straight back.
    function dismiss(e) {
        if (e) { e.preventDefault(); }
        remember();
        $banner.prop('hidden', true);
    }

    $('#tracker-download-close').on('click', dismiss);
    $('#tracker-download-dismiss').on('click', dismiss);
});
</script>

</body>

</html>
