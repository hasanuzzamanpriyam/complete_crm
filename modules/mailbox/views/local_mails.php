<?php
$mb_label = array(
    'inbox'      => lang('inbox'),
    'sent'       => lang('sent'),
    'draft'      => 'Drafts',
    'favourites' => lang('favourites'),
    'trash'      => lang('trash'),
);
$mb_title = isset($mb_label[$action]) ? $mb_label[$action] : $action;
$mb_rows = isset($local_messages) ? $local_messages : array();
$mb_is_draft = ($action == 'draft');
$mb_is_trash = ($action == 'trash');
$mb_is_sent = ($action == 'sent');
$mb_system = in_array($action, array('inbox', 'sent', 'draft', 'favourites', 'trash'), true);
if (!$mb_system):
    // Non-system action (remote mailbox folder / unknown key): legacy DataTable path,
    // byte-identical old file — `list` global boots inboxList via the dataTables.php:188 guard.
?>
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/<?= $action; ?>">
            <!-- Main content -->
            <div class="panel panel-custom">
                <div class="panel-heading">
                    <div class="mailbox-controls">

                        <!-- Check all button -->
                        <div class="mail_checkbox mr-sm">
                            <input type="checkbox" id="parent_present">
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default btn-xs mr-sm"><i class="fa fa-trash-o"></i></button>
                        </div><!-- /.btn-group -->
                        <a href="<?php echo base_url() ?>admin/mailbox/index/compose"
                           class="btn btn-danger btn-xs mr-sm">Compose +</a>
                    </div>
                </div>
                <div class="table-responsive mailbox-messages">
                    <table class="table table-striped DataTables mb-mails" id="DataTables" width="100%">
                        <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th><?= lang('host') ?></th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <script type="text/javascript">
                            list = base_url + "admin/mailbox/inboxList/<?= $action; ?>";
                        </script>
                    </table>
                </div>
            </div><!-- /.box-body -->
    </div><!-- /. box -->
    </form>
</div><!-- /.content-wrapper -->
<?php else: ?>
<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/<?= $action ?>">
            <div class="list-card">
                <div class="list-toolbar">
                    <input type="checkbox" id="parent_present" class="tb-check" title="Select all">
                    <button class="tbtn danger" title="Delete selected"><i class="fa fa-trash-o"></i></button>
                    <a href="#" onClick="history.go(0)" class="tbtn" title="Refresh"><i class="fa fa-refresh"></i></a>
                    <a href="<?php echo base_url() ?>admin/mailbox/index/compose" class="tb-compose"><i class="fa fa-pencil"></i> Compose</a>
                </div>
                <div class="rows">
                    <?php if (!empty($mb_rows)): foreach ($mb_rows as $v_inbox_msg): ?>
                        <?php
                        if ($mb_is_draft || $mb_is_sent) {
                            $mb_to = @unserialize($v_inbox_msg->to, array('allowed_classes' => false));
                            if (!is_array($mb_to)) {
                                $mb_to = array((string) $v_inbox_msg->to);
                            }
                            $string = $mb_is_sent ? 'To: ' . ($mb_to[0] ?? '') : ($mb_to[0] ?? '');
                            if (count($mb_to) > 1) {
                                $string .= ' , (' . (count($mb_to) - 1) . ')';
                            }
                        } else {
                            $string = $v_inbox_msg->from;
                        }
                        if (strlen($string) > 50) {
                            $string = strip_html_tags(mb_substr($string, 0, 50)) . '...';
                        }
                        $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                        $preview = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                        $uploaded_file = $v_inbox_msg->attach_file ? json_decode($v_inbox_msg->attach_file) : null;
                        $parts = preg_split('/[\s@.]+/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
                        $ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
                        $av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $v_inbox_msg->inbox_id)) % 8];
                        $mb_href = $mb_is_draft
                            ? base_url('admin/mailbox/index/compose/' . $v_inbox_msg->inbox_id)
                            : base_url('admin/mailbox/index/read_inbox_mail/' . $v_inbox_msg->inbox_id);
                        ?>
                        <div class="mrow<?php echo $v_inbox_msg->view_status == 1 ? '' : ' unread'; ?>">
                            <span class="udot"></span>
                            <input class="child_present tb-check" type="checkbox" name="selected_id[]" value="<?php echo $v_inbox_msg->inbox_id; ?>"/>
                            <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
                            <div class="m-main">
                                <div class="m-top">
                                    <span class="m-from"><?php echo html_escape($string); ?></span>
                                    <span class="m-time"><?php echo time_ago($v_inbox_msg->message_time); ?><?php if ($mb_is_trash) { ?>
                                        <a class="tbtn" href="<?php echo base_url() ?>admin/mailbox/restore/remote_host/<?php echo $v_inbox_msg->inbox_id ?>" title="Restore"><i class="fa fa-retweet"></i></a>
                                        <a class="tbtn danger" href="<?php echo base_url() ?>admin/mailbox/delete_mail/remote_host/deleted/<?php echo $v_inbox_msg->inbox_id ?>"
                                           onclick="return confirm('<?php echo lang('alert_delete') ?>');" title="Permanent Delete"><i class="fa fa-trash-o"></i></a><?php } ?>
                                    </span>
                                </div>
                                <div class="m-subj"><a href="<?php echo $mb_href ?>"><?php echo html_escape($subject); ?></a></div>
                                <div class="m-prev"><?php echo html_escape($preview); ?><?php if (!empty($uploaded_file)): ?> <i class="fa fa-paperclip"></i><?php endif; ?><?php if ($mb_is_draft): ?> <span class="f-tag" style="background:#fffbeb;color:#b45309">Draft</span><?php endif; ?></div>
                            </div>
                            <a class="mb-star<?php echo $v_inbox_msg->favourites == 1 ? ' on' : ''; ?>"
                               href="<?php echo base_url() ?>admin/mailbox/index/added_favourites/<?php echo $v_inbox_msg->inbox_id ?>/<?php echo $v_inbox_msg->favourites == 1 ? 0 : 1; ?>"
                               title="Favourite"><i class="fa fa-star"></i></a>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-empty">
                            <div class="big"><i class="fa <?php echo $mb_is_trash ? 'fa-trash-o' : ($mb_is_sent ? 'fa-envelope-o' : ($mb_is_draft ? 'fa-pencil' : ($action == 'favourites' ? 'fa-star' : 'fa-inbox'))); ?>"></i></div>
                            <b>There is no email to display</b>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="list-status">
                    <span><?php echo count($mb_rows); ?> messages &middot; <?php echo $mb_title; ?></span>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
