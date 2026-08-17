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

    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/toastr.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" id="bscss">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/app.min.css?v=2" id="maincss">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/booking.css?v=<?php echo filemtime(FCPATH . 'assets/css/booking.css'); ?>">

    <script src="<?php echo base_url(); ?>assets/plugins/jquery/dist/jquery.min.js?v=2"></script>
</head>
<body class="booking-page">

<div class="booking-topbar">
    <div class="container">
        <div class="booking-brand">
            <?php if (config_item('logo_or_icon') == 'logo_title') { ?>
                <img style="height: 40px;"
                     src="<?= base_url() . config_item('company_logo') ?>" class="m-r-sm" alt="logo">
            <?php } else { ?>
                <i class="fa fa-calendar-check-o"></i> <span><?= config_item('company_name') ?></span>
            <?php } ?>
        </div>
        <a href="<?= base_url() ?>" class="btn btn-sm btn-default booking-back-home">
            <i class="fa fa-sign-in"></i> <?= lang('login') ?>
        </a>
    </div>
</div>

<div class="booking-wrap">
    <div class="container">
        <?php echo $subview; ?>
    </div>
</div>

<footer class="booking-footer">
    <div class="container text-center">
        &copy; <a href="<?= config_item('copyright_url') ?>"><?= config_item('copyright_name') ?></a> 2015-<?= date('Y') ?>.
        All rights reserved.
    </div>
</footer>

<script src="<?= base_url() ?>assets/js/toastr.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/booking.js?v=<?php echo filemtime(FCPATH . 'assets/js/booking.js'); ?>"></script>
</body>
</html>
