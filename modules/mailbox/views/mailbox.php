<div class="row">
    <div class="col-md-2">
        <div class="panel panel-custom">
            <div class="panel-heading">
                <h3 class="panel-title"><?= lang('all_messages') ?>
                    <div class="pull-right">
                        <button onClick="" id="syn_bar" title="<?php echo $smtp_syn_time; ?>"
                                class="btn btn-default btn-xs mr-sm" data-toggle="tooltip"><i class="fa fa-refresh"></i>
                        </button>
                        <a href="<?= base_url() ?>admin/mailbox/settings"> <i class="fa fa-cogs"></i></a>
                    </div>
                </h3>
            </div>
            
            <div class="panel-body ">
                <ul class="nav nav-pills nav-stacked">
                    <h4><?= lang('system_mails') ?></h4>
                    
                    <li class="<?php echo($menu_active == 'inbox' ? 'active' : ''); ?>">
                        <a href="<?= base_url() ?>admin/mailbox/index/inbox"> <i class="fa fa-inbox"></i>
                            <?= lang('inbox') ?>
                            <span class="label label-green pull-right"><?php
                                if (isset($system_mail_count['inbox'])) {
                                    echo $system_mail_count['inbox'];
                                } else {
                                    echo '0';
                                }
                                ?></span>
                        </a>
                    </li>
                    <li class="<?php echo ($menu_active == 'sent') ? 'active' : ''; ?>">
                        <a href="<?= base_url() ?>admin/mailbox/index/sent"> <i class="fa fa-envelope-o"></i>
                            <?= lang('sent') ?>
                            <span class="label label-green pull-right"><?php
                                if (isset($system_mail_count['sent'])) {
                                    echo $system_mail_count['sent'];
                                } else {
                                    echo '0';
                                }
                                ?></span>
                        </a>
                    </li>
                    <li class="<?php echo ($menu_active == 'draft') ? 'active' : ''; ?>"><a
                                href="<?= base_url() ?>admin/mailbox/index/draft"><i class="fa fa-file-text-o"></i>
                            Drafts
                            <span class="label label-green pull-right"><?php
                                if (isset($system_mail_count['draft'])) {
                                    echo $system_mail_count['draft'];
                                } else {
                                    echo '0';
                                }
                                ?></span></a>
                    </li>
                    
                    <li class="<?php echo ($menu_active == 'favourites') ? 'active' : ''; ?>">
                        <a href="<?= base_url() ?>admin/mailbox/index/favourites"> <i
                                    class="fa fa-star text-yellow"></i>
                            <?= lang('favourites') ?>
                            <span class="label label-green pull-right"><?php
                                if (isset($system_mail_count['favourites'])) {
                                    echo $system_mail_count['favourites'];
                                } else {
                                    echo '0';
                                }
                                ?></span>
                        </a>
                    </li>
                    <li class="<?php echo ($menu_active == 'trash') ? 'active' : ''; ?>">
                        <a href="<?= base_url() ?>admin/mailbox/index/trash"> <i class="fa fa-trash-o"></i>
                            <?= lang('trash') ?><span class="label label-warning pull-right"><?php
                                if (isset($system_mail_count['trash'])) {
                                    echo $system_mail_count['trash'];
                                } else {
                                    echo '0';
                                }
                                ?></span></a>
                    </li>
                
                </ul>
                <ul id="mail_box_menu" class="nav nav-pills nav-stacked">
                
                </ul>
            </div><!-- /.box-body -->
        </div><!-- /. box -->
    </div><!-- /.col -->
    
    <div class="col-md-10">
        <?php echo message_box('success'); ?>
        <?php echo message_box('error'); ?>
        <?php $rata['folder'] = $folder;
        $rata['action'] = $action;
        $rata['menu_active'] = $action;
        $this->load->view($view, $rata); ?>
    </div><!-- /.col -->
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
                    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                    url: send_url, // the url where we want to POST
                    dataType: 'json', // what type of data do we expect back from the server
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