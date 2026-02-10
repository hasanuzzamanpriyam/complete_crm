<div class="panel panel-custom">
    <div class="panel-heading">
        <?php
        echo html_escape($read_mail->subject);
        if (!empty($read_mail->favourites)) {
            if ($read_mail->favourites == 1) {
                $favs = 0;
                $fav_icon = 'fa-star text-yellow';
                $fav_title = lang('remove_from_favourites');
            } else {
                $fav_icon = 'fa-star-o text-yellow';
                $favs = 1;
                $fav_title = lang('added_into_favourites');
            }
        }
        if (!empty($read_mail->view_status)) {
            if ($read_mail->view_status == 1) {
                $views = 2;
                $view_icon = 'fa-envelope';
                $view_title = lang('mark_as_unread');
            } else {
                $view_icon = 'fa-envelope-o';
                $views = 1;
                $view_title = lang('mark_as_read');
            }
        }
        ?>
        <div class="pull-right">
            <?php if (!empty($favs)) { ?>
                <a href="<?= base_url() ?>admin/mailbox/index/added_favourites/<?= $read_mail->inbox_id . '/' . $favs ?>"
                   class="btn btn-xs" data-toggle="tooltip" title="<?= $fav_title ?>"><i
                            class="fa <?= $fav_icon ?>"></i></a>
            <?php } ?>
            <?php if (!empty($views)) { ?>
                <a href="<?= base_url() ?>admin/mailbox/index/change_view/<?= $read_mail->inbox_id . '/' . $views ?>"
                   class="btn btn-xs" data-toggle="tooltip" title="<?= $view_title ?>"><i
                            class="fa <?= $view_icon ?>"></i></a>
            <?php }
            if (!empty($read_mail->inbox_id)) {
                $id = $read_mail->inbox_id;
                $delete = 'delete_mail/inbox/NULL';
            } else if (!empty($read_mail->sent_id)) {
                $id = $read_mail->sent_id;
                $delete = 'delete_mail/sent/NULL';
            } else if (!empty($read_mail->draft_id)) {
                $id = $read_mail->draft_id;
                $delete = 'delete_mail/draft/NULL';
            }
            ?>
            <a href="<?= base_url() ?>admin/mailbox/index/compose/<?= $id ?>/reply" class="btn text-black btn-xs"
               data-toggle="tooltip" title="<?= lang('reply') ?>"><i class="fa fa-reply"></i></a>
            <a href="<?= base_url() ?>admin/mailbox/index/compose/<?= $id ?>/replyall" class="btn btn-xs"
               data-toggle="tooltip" title="<?= lang('reply_all') ?>"><i
                        class="fa fa-reply-all"></i></a>
            <a href="<?= base_url() ?>admin/mailbox/index/compose/<?= $id ?>/forward" class="btn btn-xs"
               data-toggle="tooltip" title="<?= lang('forward') ?>"><i
                        class="fa fa-mail-forward"></i></a>
            <a href="<?= base_url() ?>admin/mailbox/<?= $delete . '/' . $id ?>" class="btn text-danger btn-xs"
               data-toggle="tooltip" title="<?= lang('delete') ?>"><i class="fa fa-trash-o"></i></a>
        </div>
    </div>
    <div class="panel-body mt0 pt0">
        <div class="mailbox-read-info">
            <?php if (!empty($reply)) { ?>
                <h5><?= lang('from:') ?><?php echo html_escape($read_mail->from); ?></h5>
            <?php } ?>
            <h5><?= lang('to:') ?><?php
                $to = unserialize($read_mail->to);
                if (is_array($to)) {
                    echo implode(', ', $to);
                } else {
                    echo $read_mail->to;
                } ?>
            </h5>
            <?php
            if (!empty($read_mail->cc)) {
                ?>
                <h5><?= lang('cc:') ?><?php echo html_escape($read_mail->cc); ?></h5>
            <?php } ?>
            <h5>
                <span class="mailbox-read-time"><?php echo date('d M , Y h:i:A', strtotime($read_mail->message_time)) ?></span>
            </h5>
        </div><!-- /.mailbox-read-info -->
        <div class="mailbox-read-message text-justify margin">
            <p><?php echo $read_mail->message_body; ?></p>
        </div><!-- /.mailbox-read-message -->
    </div><!-- /.box-body -->
    <ul class="mailbox-attachments clearfix mt">
        <?php
        $uploaded_file = json_decode($read_mail->attach_file);
        if (!empty($uploaded_file)) :
            foreach ($uploaded_file as $v_files) :
                if (!empty($v_files)) :
                    ?>
                    <li>
                        <?php if (!empty($v_files->is_image) && $v_files->is_image == 1) : ?>
                            <span class="mailbox-attachment-icon has-img"><img
                                        src="<?= base_url('modules/mailbox/uploads/' . html_escape($v_files->fileName)) ?>"
                                        alt="Attachment"></span>
                        <?php else : ?>
                            <span class="mailbox-attachment-icon"><i class="fa fa-file-pdf-o"></i></span>
                        <?php endif; ?>
                        <div class="mailbox-attachment-info">
                            <a target="_blank"
                               href="<?= base_url() ?>mailbox/download_file/<?= html_escape($v_files->fileName) ?>"
                               class="mailbox-attachment-name"><i class="fa fa-paperclip"></i>
                                <?= html_escape($v_files->fileName) ?></a>
                            <span class="mailbox-attachment-size">
                                <?= $v_files->size ?> <?= lang('kb') ?>
                                <a href="<?= base_url() ?>mailbox/download_file/<?= html_escape($v_files->fileName) ?>"
                                   class="btn btn-default btn-xs pull-right"><i class="fa fa-cloud-download"></i></a>
                            </span>
                        </div>
                    </li>
                <?php
                endif;
            endforeach;
        endif;
        ?>
    </ul>
</div><!-- /. box -->