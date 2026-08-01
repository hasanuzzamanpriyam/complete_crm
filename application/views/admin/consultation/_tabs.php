<?php
$active_tab = isset($active_tab) ? $active_tab : 'appointments';
?>
<ul class="nav nav-tabs nav-tabs-consultation mb-lg">
    <li class="<?= $active_tab === 'appointments' ? 'active' : '' ?>">
        <a href="<?= base_url() ?>admin/consultation/appointments">
            <i class="fa fa-calendar-check-o"></i> <?= lang('consultation_appointments') ?>
        </a>
    </li>
    <li class="<?= $active_tab === 'consultants' ? 'active' : '' ?>">
        <a href="<?= base_url() ?>admin/consultation/consultants">
            <i class="fa fa-user-md"></i> <?= lang('consultation_consultants') ?>
        </a>
    </li>
    <li class="<?= $active_tab === 'settings' ? 'active' : '' ?>">
        <a href="<?= base_url() ?>admin/consultation/settings">
            <i class="fa fa-cogs"></i> <?= lang('consultation_settings') ?>
        </a>
    </li>
</ul>
<style>
    .nav-tabs-consultation > li > a { border-radius: 3px 3px 0 0; }
    .nav-tabs-consultation > li.active > a { border-top: 2px solid #7266ba; }
</style>
