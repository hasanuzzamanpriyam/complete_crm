<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>client/mailbox/delete_mail/inbox">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                    <a href="<?php echo base_url() ?>client/mailbox/index/compose" class="tb-compose"><i class="fa fa-pencil"></i> Compose</a>
                </div>
                <div class="rows">
                    <?php if (!empty($get_inbox_message)): foreach ($get_inbox_message as $v_inbox_msg): ?>
                        <?php
                        $string = (strlen($v_inbox_msg->to) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->to, 0, 50)) . '...' : $v_inbox_msg->to;
                        $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                        $preview = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                        $uploaded_file = $v_inbox_msg->attach_file ? json_decode($v_inbox_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_inbox_msg->inbox_id)) % 8];
                        ?>
                        <div class="mrow<?php echo $v_inbox_msg->view_status == 1 ? '' : ' unread'; ?>">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_inbox_msg->inbox_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from"><?php echo $string; ?></span>
                                    <span class="m-time"><?php echo time_ago($v_inbox_msg->message_time); ?></span>
                                </div>
                                <div class="m-subj"><a href="<?php echo base_url() ?>client/mailbox/index/read_inbox_mail/<?php echo $v_inbox_msg->inbox_id ?>"><?php echo $subject; ?></a></div>
                                <div class="m-prev"><?php echo $preview; ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?></div>
                            </div>
                            <a class="mb-star<?php echo $v_inbox_msg->favourites == 1 ? ' on' : ''; ?>"
                               href="<?php echo base_url() ?>client/mailbox/index/added_favourites/<?php echo $v_inbox_msg->inbox_id ?>/<?php echo $v_inbox_msg->favourites == 1 ? 0 : 1; ?>"
                               title="Favourite"><i class="fa fa-star"></i></a>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa fa-inbox"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo !empty($get_inbox_message) ? count($get_inbox_message) : 0; ?> messages &middot; Inbox</span>
                </div>
            </div>
        </form>
    </div>
</div>
