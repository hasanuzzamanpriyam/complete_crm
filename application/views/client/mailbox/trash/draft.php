<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>client/mailbox/delete_mail/draft/trash">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                    <a href="<?php echo base_url() ?>client/mailbox/index/compose" class="tb-compose"><i class="fa fa-pencil"></i> Compose +</a>
                </div>
                <div class="rows">
                    <?php if (!empty($draft_message)): foreach ($draft_message as $v_draft_msg): ?>
                        <?php
                        $email_address = unserialize($v_draft_msg->to, array('allowed_classes' => false));
                        $total_email = count(array($email_address));
                        if ($total_email > 1) {
                            $deduct = "$total_email" - 1;
                            $string = $email_address[0] . ' , (' . $deduct . ')';
                        } else {
                            $string = $email_address[0];
                        }
                        $subject = (strlen($v_draft_msg->subject) > 50) ? strip_html_tags(mb_substr($v_draft_msg->subject, 0, 50)) . '...' : $v_draft_msg->subject;
                        $preview = (strlen($v_draft_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_draft_msg->message_body, 0, 100)) . '...' : $v_draft_msg->message_body;
                        $uploaded_file = $v_draft_msg->attach_file ? json_decode($v_draft_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim((string) $string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_draft_msg->draft_id)) % 8];
                        ?>
                        <div class="mrow">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_draft_msg->draft_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from"><?php echo html_escape($string); ?></span>
                                    <span class="m-time"><?php echo time_ago($v_draft_msg->message_time); ?>
                                        <a class="tbtn" href="<?php echo base_url() ?>client/mailbox/restore/draft/<?php echo $v_draft_msg->draft_id ?>" title="Restore"><i class="fa fa-retweet"></i></a>
                                        <a class="tbtn danger" href="<?php echo base_url() ?>client/mailbox/delete_mail/draft/deleted/<?php echo $v_draft_msg->draft_id ?>"
                                           onclick="return confirm('You are about to delete a record. This cannot be undone. Are you sure?');" title="Permanent Delete"><i class="fa fa-trash-o"></i></a>
                                    </span>
                                </div>
                                <div class="m-subj"><a href="<?php echo base_url() ?>client/mailbox/index/compose/<?php echo $v_draft_msg->draft_id ?>"><?php echo html_escape($subject); ?></a></div>
                                <div class="m-prev"><?php echo html_escape($preview); ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?> <span class="f-tag" style="background:#fffbeb;color:#b45309">Draft</span></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa fa-trash-o"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo !empty($draft_message) ? count($draft_message) : 0; ?> deleted messages &middot; Drafts</span>
                </div>
            </div>
        </form>
    </div>
</div>
