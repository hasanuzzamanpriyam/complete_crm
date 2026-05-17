<?php
echo message_box('success');
echo message_box('error');
$created = can_action_by_label('jitsi', 'created');
$edited = can_action_by_label('jitsi', 'edited');
$deleted = can_action_by_label('jitsi', 'deleted');
if (!empty($created) || !empty($edited)) {
?>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="<?= $active == 1 ? 'active' : ''; ?>"><a href="#manage" data-toggle="tab"><?= lang('all') . ' ' . lang('jitsi') ?></a></li>
            <li class="<?= $active == 2 ? 'active' : ''; ?>"><a href="#create" data-toggle="tab"><?= lang('new') . ' ' . lang('jitsi') ?></a></li>
            <li class="pull-right"><a href="<?= base_url('admin/jitsi/settings') ?>"><i class="fa fa-cogs"></i></a></li>
        </ul>
        <div class="tab-content bg-white">
            <div class="tab-pane <?= $active == 1 ? 'active' : ''; ?>" id="manage">
<?php } else { ?>
                <div class="panel panel-custom">
                    <header class="panel-heading">
                        <div class="panel-title"><strong><?= lang('jitsi') ?></strong></div>
                    </header>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-striped DataTables" id="DataTables" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th><?= lang('topic') ?></th>
                                <th><?= lang('meeting_time') ?></th>
                                <th><?= lang('notes') ?></th>
                                <th><?= lang('status') ?></th>
                                <th><?= lang('join') ?></th>
                                <th><?= lang('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <script type="text/javascript">
                                $(document).ready(function() {
                                    list = base_url + "admin/jitsi/meetingList";
                                });
                            </script>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (!empty($created) || !empty($edited)) { ?>
                <div class="tab-pane <?= $active == 2 ? 'active' : ''; ?>" id="create">
                    <form role="form" enctype="multipart/form-data" id="form" data-parsley-validate="" novalidate="" action="<?php echo base_url(); ?>admin/jitsi/save_meeting/<?php
                                                                                                                                                                            if (!empty($meeting_info)) {
                                                                                                                                                                                echo $meeting_info->jitsi_meeting_id;
                                                                                                                                                                            }
                                                                                                                                                                            ?>" method="post" class="form-horizontal">

                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('topic') ?> <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" value="<?php
                                                                                    if (!empty($meeting_info)) {
                                                                                        echo $meeting_info->topic;
                                                                                    }
                                                                                    ?>" name="topic" required="">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('meeting_time') ?> <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control datetimepicker" value="<?php
                                                                                                        if (!empty($meeting_info)) {
                                                                                                            echo $meeting_info->meeting_time;
                                                                                                        }
                                                                                                        ?>" name="meeting_time" required="">
                                        <div class="input-group-addon">
                                            <a href="#"><i class="fa fa-calendar"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('duration') ?> <span class="text-danger">*</span></label>
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="<?php
                                                                                        if (!empty($meeting_info)) {
                                                                                            echo $meeting_info->duration;
                                                                                        }
                                                                                        ?>" name="duration" required="">
                                        <div class="input-group-addon">
                                            <?= lang('in_minutes') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('notes') ?></label>
                                <div class="col-lg-8">
                                    <textarea class="form-control" name="notes"><?php
                                                                                if (!empty($meeting_info)) {
                                                                                    echo $meeting_info->notes;
                                                                                }
                                                                                ?></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('host') ?></label>
                                <div class="col-lg-8">
                                    <?php
                                    $all_user = get_result('tbl_users', array('role_id !=' => 2, 'activated' => 1));
                                    $select = '<select class="selectpicker" data-actions-box="true" data-width="100%" name="host" data-live-search="true" data-none-selected-text="' . lang('select') . ' ' . lang('host') . '">';
                                    foreach ($all_user as $user) {
                                        $selected = '';
                                        if (!empty($meeting_info) && $meeting_info->host == $user->user_id) {
                                            $selected = ' selected ';
                                        } else if (empty($meeting_info) && $user->user_id == my_id()) {
                                            $selected = ' selected ';
                                        }
                                        $select .= '<option value="' . $user->user_id . '"' . $selected . 'data-taxrate="' . fullname($user->user_id) . '" data-taxname="' . $user->username . '" data-subtext="' . $user->username . '">' . fullname($user->user_id) . '</option>';
                                    }
                                    $select .= '</select>';
                                    echo $select;
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('users') ?></label>
                                <div class="col-lg-8">
                                    <?php
                                    $all_user = get_result('tbl_users', array('role_id !=' => 2, 'activated' => 1));
                                    if (!empty($meeting_info->user_id) && !is_numeric($meeting_info->user_id)) {
                                        $user_id = json_decode($meeting_info->user_id);
                                    }
                                    $select = '<select class="selectpicker" data-actions-box="true" data-width="100%" name="user_id[]" data-live-search="true" multiple data-none-selected-text="' . lang('select') . ' ' . lang('users') . '">';
                                    foreach ($all_user as $user) {
                                        $selected = '';
                                        if (!empty($user_id) && is_array($user_id)) {
                                            if (in_array($user->user_id, $user_id)) {
                                                $selected = ' selected ';
                                            }
                                        }
                                        $select .= '<option value="' . $user->user_id . '"' . $selected . 'data-taxrate="' . fullname($user->user_id) . '" data-taxname="' . $user->username . '" data-subtext="' . $user->username . '">' . fullname($user->user_id) . '</option>';
                                    }
                                    $select .= '</select>';
                                    echo $select;
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('client') ?></label>
                                <div class="col-lg-8">
                                    <?php
                                    $all_client = get_result('tbl_client');
                                    if (!empty($meeting_info->client_id) && !is_numeric($meeting_info->client_id)) {
                                        $client_id = json_decode($meeting_info->client_id);
                                    }
                                    $select = '<select class="selectpicker" data-actions-box="true" data-width="100%" name="client_id[]" data-live-search="true" multiple data-none-selected-text="' . lang('select') . ' ' . lang('clients') . '">';
                                    foreach ($all_client as $client) {
                                        $selected = '';
                                        if (!empty($client_id) && is_array($client_id)) {
                                            if (in_array($client->client_id, $client_id)) {
                                                $selected = ' selected ';
                                            }
                                        }
                                        $select .= '<option value="' . $client->client_id . '"' . $selected . 'data-taxrate="' . $client->name . '" data-taxname="' . $client->email . '">' . $client->name . '</option>';
                                    }
                                    $select .= '</select>';
                                    echo $select;
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar">
                            <label class="col-lg-2 control-label ml-lg"></label>
                            <button type="submit" class="btn btn-sm btn-primary"><?= lang('save') ?></button>
                        </div>
                    </form>
                </div>
            <?php } else { ?>
        </div>
    <?php } ?>
    </div>
</div>
<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/datetimepicker/jquery.datetimepicker.min.css">
<?php include_once 'assets/plugins/datetimepicker/jquery.datetimepicker.full.php'; ?>
<script type="text/javascript">
    $(function() {
        'use strict';
        init_datepicker();
    });

    function init_datepicker() {
        var datetimepickers = $('.datetimepicker');
        if (datetimepickers.length == 0) {
            return;
        }
        var opt_time;
        $.each(datetimepickers, function() {
            opt_time = {
                lazyInit: true,
                scrollInput: false,
                format: 'Y-m-d H:i',
                autoclose: true,
                minDate: "today",
            };
            opt_time.formatTime = 'H:i';
            $(this).datetimepicker(opt_time);
        });
    }

    function copyMeetingLink(btn, url) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(url).select();
        document.execCommand("copy");
        $temp.remove();
        
        var originalTitle = $(btn).attr('data-original-title') || $(btn).attr('title');
        $(btn).attr('title', 'Link Copied!').tooltip('fixTitle').tooltip('show');
        setTimeout(function() {
            $(btn).attr('title', originalTitle).tooltip('fixTitle').tooltip('hide');
        }, 1500);
    }
</script>
