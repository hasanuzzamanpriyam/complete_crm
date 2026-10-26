<link rel="stylesheet" href="<?= base_url('assets/css/chat_modern.css?v=1') ?>">
<?php $script = ""; ?>
<?php
$profile = profile();
if ($profile->role_id == 2) {
    $where = array('role_id !=' => '2', 'activated' => '1');
} else {
    $where = array('activated' => '1');
}
$all_user_info = get_result('tbl_users', $where);

// collect renderable users first (need the count for the panel badge)
$cc_users = array();
if (!empty($all_user_info)) {
    foreach ($all_user_info as $v_user) {
        $account_info = $this->chat_model->check_by(array('user_id' => $v_user->user_id), 'tbl_account_details');
        if (!empty($account_info) && $account_info->user_id != $this->session->userdata('user_id')) {
            $cc_users[] = array('user' => $v_user, 'account' => $account_info);
        }
    }
}

// conversation identity + other user's role for the header subtitle
if (!empty($chats)) {
    $title = $chats->title;
    $id = 'conversation_chat_' . $chats->private_chat_id;
    $chatid = $chats->private_chat_id;
} else {
    $title = fullname($user_id);
    $id = null;
    $chatid = null;
}
$other_user = get_row('tbl_users', array('user_id' => $user_id));
if (!empty($other_user)) {
    if ($other_user->role_id == 1) {
        $cc_other_role = lang('admin');
    } elseif ($other_user->role_id == 3) {
        $cc_other_role = lang('staff');
    } else {
        $cc_other_role = lang('client');
    }
} else {
    $cc_other_role = '';
}
?>
<div class="row cc-page">
    <!-- ===== All Users ===== -->
    <div class="col-sm-3">
        <div class="cc-panel">
            <div class="cc-panel-head">
                <h3><?= lang('all_users') ?></h3>
                <span class="cc-count"><?= count($cc_users) ?></span>
            </div>
            <div class="cc-users">
                <?php foreach ($cc_users as $cc_u) :
                    $v_user = $cc_u['user'];
                    $account_info = $cc_u['account'];
                    if ($v_user->role_id == 1) {
                        $cc_tag = '<span class="cc-tag cc-tag-admin">' . lang('admin') . '</span>';
                    } elseif ($v_user->role_id == 3) {
                        $cc_tag = '<span class="cc-tag cc-tag-staff">' . lang('staff') . '</span>';
                    } else {
                        $cc_tag = '<span class="cc-tag cc-tag-client">' . lang('client') . '</span>';
                    }
                    ?>
                    <a class="cc-user-item<?= ($v_user->user_id == $user_id) ? ' active' : '' ?>"
                       href="<?= base_url(); ?>chat/conversations/<?= $v_user->user_id ?>">
                        <img class="cc-ava" src="<?= base_url(staffImage($v_user->user_id)) ?>" alt=""/>
                        <span class="cc-meta">
                            <span class="cc-name"><?= $account_info->fullname ?> <?= $cc_tag ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ===== Conversation ===== -->
    <div class="col-md-9">
        <div class="cc-panel cc-conv">
            <div class="cc-conv-head">
                <img class="cc-ava" src="<?= base_url(staffImage($user_id)) ?>" alt=""/>
                <div class="cc-who">
                    <h3><?= $title ?></h3>
                    <p>● <?= $cc_other_role ?></p>
                </div>
                <div class="cc-actions">
                    <button type="button" data-user_id="<?= $user_id ?>"
                            class="cc-chip cc-chip-primary start_chat"><?= lang('start') . ' ' . lang('chat') ?>
                    </button>
                </div>
            </div>

            <?php
            // get all message by private chat id
            $messages = array();
            if (!empty($all_messages)) {
                foreach ($all_messages as $message) {
                    array_push($messages, $message);
                }
                $messages = array_reverse($messages);
                $window_id = "conversation_chat_" . $chats->private_chat_id;
                $script .= '$("#' . $window_id . '").scrollTop($("#' . $window_id . '")[0].scrollHeight);';
                ?>
                <div class="cc-msgs" id="<?= $id ?>">
                    <?php
                    $cc_last_day = '';
                    $cc_my_id = $this->session->userdata('user_id');
                    foreach ($messages as $message) :
                        $cc_day = date('Y-m-d', strtotime($message->message_time));
                        if ($cc_day != $cc_last_day) {
                            $cc_last_day = $cc_day;
                            if ($cc_day == date('Y-m-d')) {
                                $cc_day_label = 'Today';
                            } elseif ($cc_day == date('Y-m-d', strtotime('-1 day'))) {
                                $cc_day_label = 'Yesterday';
                            } else {
                                $cc_day_label = date('d M Y', strtotime($message->message_time));
                            }
                            ?>
                            <div class="cc-day"><?= $cc_day_label ?></div>
                        <?php } ?>
                        <?php if ($message->user_id == $cc_my_id) { ?>
                            <div class="cc-m me">
                                <img data-toggle="tooltip" data-placement="top"
                                     title="<?= fullname($message->user_id) ?>" class="cc-ava"
                                     src="<?= base_url(staffImage($message->user_id)) ?>"
                                     alt="message user image"/>
                                <div class="cc-mbody">
                                    <div class="cc-bubble">
                                        <?php echo $message->message ?>
                                    </div>
                                    <div data-toggle="tooltip" data-placement="top"
                                         title="<?php echo strftime(config_item('date_format'), strtotime($message->message_time)) . ' ' . lang('at') . ' ' . display_time($message->message_time); ?>"
                                         class="cc-time"><?= time_ago($message->message_time); ?></div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="cc-m">
                                <img data-toggle="tooltip" data-placement="top"
                                     title="<?= fullname($message->user_id) ?>" class="cc-ava"
                                     src="<?= base_url(staffImage($message->user_id)) ?>"
                                     alt="message user image"/>
                                <div class="cc-mbody">
                                    <div class="cc-who"><?= fullname($message->user_id) ?></div>
                                    <div class="cc-bubble">
                                        <?php echo $message->message ?>
                                    </div>
                                    <div data-toggle="tooltip" data-placement="top"
                                         title="<?php echo strftime(config_item('date_format'), strtotime($message->message_time)) . ' ' . lang('at') . ' ' . display_time($message->message_time); ?>"
                                         class="cc-time"><?= time_ago($message->message_time); ?></div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php endforeach; ?>
                </div>
            <?php } else { ?>
                <div class="cc-msgs">
                    <div class="cc-empty"><?= lang('start') . ' ' . lang('chat') ?> — <?= $title ?></div>
                </div>
            <?php } ?>

            <?php if (!empty($chats)) { ?>
                <div class="cc-composer">
                    <input type="text" id="conv_msg_input" placeholder="<?= lang('type_message') ?>"
                           onkeypress="return cc_send(event);">
                    <button type="button" class="cc-send" onclick="cc_send();">&#10148;</button>
                </div>
                <div class="cc-err" id="cc_err"></div>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    var cc_user_id = <?= (int)$user_id ?>;
    var cc_chat_id = <?= (int)$chatid ?>;

    function cc_send(event) {
        if (event && event.keyCode && event.keyCode != 13) {
            return true;
        }
        var msg = $('#conv_msg_input').val();
        if (!msg || !cc_chat_id) {
            return false;
        }
        $('#cc_err').text('');
        $.get(base_url + 'chat/send_message/' + cc_chat_id, {message: msg}, function (res) {
            if (res.error) {
                $('#cc_err').text(res.error);
                return;
            }
            $('#conv_msg_input').val('');
            all_conversations(cc_user_id, function () {
                var el = $('#conversation_chat_' + cc_chat_id);
                if (el.length) {
                    el.scrollTop(el[0].scrollHeight);
                }
            });
        }, 'json');
        return false;
    }

    $(document).ready(function () {
        setInterval(function () {
            all_conversations(<?= $user_id ?>);
        }, interval_time);

        <?php echo $script ?>
    });
</script>
