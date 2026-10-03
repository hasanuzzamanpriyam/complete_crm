<div class="mbflex">
    <div class="folders">
        <div class="sec">
            <?= lang('all_messages') ?>
            <div class="fr">
                <button id="syn_bar" title="<?php echo $smtp_syn_time; ?>" class="tbtn" data-toggle="tooltip"><i class="fa fa-refresh"></i></button>
                <a href="<?= base_url() ?>admin/mailbox/settings" class="tbtn" title="Settings"><i class="fa fa-cogs"></i></a>
            </div>
        </div>
        <div class="sec sub"><?= lang('system_mails') ?></div>
        <?php
        $mb_folders = array(
            'inbox'      => array('label' => lang('inbox'), 'icon' => 'fa-inbox'),
            'sent'       => array('label' => lang('sent'), 'icon' => 'fa-envelope-o'),
            'draft'      => array('label' => 'Drafts', 'icon' => 'fa-file-text-o'),
            'favourites' => array('label' => lang('favourites'), 'icon' => 'fa-star'),
            'trash'      => array('label' => lang('trash'), 'icon' => 'fa-trash-o'),
        );
        foreach ($mb_folders as $mb_key => $mb_def): ?>
            <a class="<?php echo ($menu_active == $mb_key) ? 'on f-item' : 'f-item'; ?>"
               href="<?= base_url() ?>admin/mailbox/index/<?php echo $mb_key; ?>">
                <span class="fi"><i class="fa <?php echo $mb_def['icon']; ?>"></i></span><?= $mb_def['label'] ?>
                <span class="fc<?php echo (in_array($mb_key, array('inbox', 'sent', 'draft'), true) && !empty($system_mail_count[$mb_key]) && $system_mail_count[$mb_key] > 0) ? ' hot' : ''; ?>"><?php echo isset($system_mail_count[$mb_key]) ? $system_mail_count[$mb_key] : 0; ?></span>
            </a>
        <?php endforeach; ?>
        <ul id="mail_box_menu" class="nav nav-pills nav-stacked fsub"></ul>
    </div>
    <div class="list-col">
        <?php echo message_box('success'); ?>
        <?php echo message_box('error'); ?>
        <?php $rata['folder'] = $folder;
        $rata['action'] = $action;
        $rata['menu_active'] = $action;
        $this->load->view($view, $rata); ?>
    </div>
</div>
<script>
    <?php
    $mailboxes_last_sync_time = time();
    $this->session->set_userdata('mailboxes_last_sync_time', $mailboxes_last_sync_time);
    if (empty($this->session->userdata('mailboxes_sync_time')) || $mailboxes_last_sync_time > $this->session->userdata('mailboxes_sync_time')) {
    $five_mins_after = time() + (5 * 60);
    $this->session->set_userdata('mailboxes_sync_time', $five_mins_after);
    $mailboxes_sync_time = $this->session->userdata('mailboxes_sync_time');
    ?>
    <?php } ?>
    $(function () {
        'use strict';
        var syn_bar = $("#syn_bar");
        syn_bar.on("click", ajax_req_send);
        ins_data(base_url + "admin/mailbox/mail_box_menu/<?= $folder; ?>");
        function ins_data(url, datastring = '') {
            $.ajax({
                async: false,
                url: url,
                type: 'post',
                data: datastring,
                dataType: "json",
                success: function (data) {
                    $.each(data, function (index, value) {
                        $('#' + index).empty().html(value);
                    });
                }
            });
        }

        function ajax_req_send() {
            syn_bar.css({color: "red"});
            $('#syn_bar i').addClass('fa-spin');
            var send_url = base_url + 'admin/mailbox/fetch_remote_mails';
            $.ajax({
                async: false,
                type: 'POST',
                url: send_url,
                dataType: 'json',
                encode: true,
                success: function (res) {
                    if (res.success == true) {
                        $('#syn_bar i').removeClass('fa-spin');
                        syn_bar.css({color: ""});
                        syn_bar.attr('title') == '';
                        ins_data(base_url + 'admin/mailbox/mail_box_menu/<?= $folder; ?>');
                    } else {
                        alert('There was a problem with AJAX');
                    }
                }
            })
        }
    });
</script>
