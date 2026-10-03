<?php
$read_label = !empty($reply) ? 'From' : 'To';
$read_from = !empty($reply) ? ($read_mail->from ?? '') : '';
if (empty($reply)) {
    $read_to = @unserialize($read_mail->to, array('allowed_classes' => false));
    $read_from = is_array($read_to) ? implode(', ', $read_to) : $read_mail->to;
}
if (!empty($read_mail->inbox_id)) {
    $favs = (!empty($read_mail->favourites) && $read_mail->favourites == 1) ? 0 : 1;
    $fav_icon = $favs == 0 ? 'fa-star' : 'fa-star-o';
    $fav_title = $favs == 0 ? lang('remove_from_favourites') : lang('added_into_favourites');
}
if (!empty($read_mail->inbox_id)) {
    $id = $read_mail->inbox_id;
} else if (!empty($read_mail->sent_id)) {
    $id = $read_mail->sent_id;
} else if (!empty($read_mail->draft_id)) {
    $id = $read_mail->draft_id;
}
$parts = preg_split('/[\s@.]+/', trim((string) $read_from), -1, PREG_SPLIT_NO_EMPTY);
$ini = mb_strtoupper(implode('', array_slice($parts, 0, 2)));
$row_key = $read_mail->inbox_id ?? $read_mail->sent_id ?? $read_mail->draft_id ?? '';
$av = ['#0d9488', '#2563eb', '#8b5cf6', '#f59e0b', '#dc2626', '#0ea5e9', '#10b981', '#e11d48'][abs(crc32((string) $row_key)) % 8];
?>
<div class="row">
    <div class="col-md-12">
        <div class="read-card">
            <div class="read-head">
                <div class="rh-top">
                    <button class="mb-back" onclick="history.go(-1)"><i class="fa fa-arrow-left"></i> Back</button>
                    <div class="rh-tools">
                        <?php if (isset($favs)) { ?>
                            <a href="<?= base_url() ?>client/mailbox/index/added_favourites/<?= $read_mail->inbox_id . '/' . $favs ?>"
                               class="tbtn" title="<?= $fav_title ?>"><i class="fa <?= $fav_icon ?>"></i></a>
                        <?php } ?>
                        <?php if (!empty($reply)) { ?>
                            <a href="<?= base_url() ?>client/mailbox/index/compose/<?= $id ?>/reply" class="tbtn"
                               title="<?= lang('reply') ?>"><i class="fa fa-reply"></i></a>
                            <a href="<?= base_url() ?>client/mailbox/index/compose/<?= $id ?>/replyall" class="tbtn"
                               title="<?= lang('reply_all') ?>"><i class="fa fa-reply-all"></i></a>
                            <a href="<?= base_url() ?>client/mailbox/index/compose/<?= $id ?>/forward" class="tbtn"
                               title="<?= lang('forward') ?>"><i class="fa fa-mail-forward"></i></a>
                            <a href="<?= base_url() ?>client/mailbox/delete_inbox_mail/<?= $read_mail->inbox_id ?>"
                               class="tbtn danger" title="<?= lang('delete') ?>"><i class="fa fa-trash-o"></i></a>
                        <?php } ?>
                    </div>
                </div>
                <h2 class="r-subj"><?php echo html_escape($read_mail->subject); ?></h2>
                <div class="r-who">
                    <span class="mb-av" style="background:<?php echo $av; ?>"><?php echo html_escape($ini); ?></span>
                    <div>
                        <div class="who"><?php echo $read_label ?>: <?php echo html_escape($read_from); ?></div>
                        <?php if (!empty($read_mail->cc)) { ?>
                            <div class="who">Cc: <?php echo html_escape($read_mail->cc); ?></div>
                        <?php } ?>
                        <div class="em"><?php echo date('d M , Y h:i:A', strtotime($read_mail->message_time)) ?></div>
                    </div>
                    <div class="when"><?php echo date('d M , Y', strtotime($read_mail->message_time)) ?></div>
                </div>
            </div>
            <div class="read-body">
                <p><?php echo $read_mail->message_body; ?></p>
            </div>
            <?php
            $uploaded_file = $read_mail->attach_file ? json_decode($read_mail->attach_file) : null;
            if (!empty($uploaded_file)):
                ?>
                <ul class="att-strip">
                    <?php foreach ($uploaded_file as $v_files): ?>
                        <?php if (!empty($v_files)): ?>
                            <li class="att-file">
                                <?php if (!empty($v_files->is_image) && $v_files->is_image == 1): ?>
                                    <img class="att-thumb" src="<?= base_url() . $v_files->path ?>" alt="Attachment">
                                <?php else: ?>
                                    <span class="ic"><i class="fa fa-file-pdf-o"></i></span>
                                <?php endif; ?>
                                <a target="_blank"
                                   href="<?= base_url() ?>client/mailbox/download_file/<?= html_escape($v_files->fileName) ?>"
                                   class="att-name"><i class="fa fa-paperclip"></i> <?= html_escape($v_files->fileName) ?></a>
                                <span class="sz"><?= $v_files->size ?> <?= lang('kb') ?></span>
                                <a href="<?= base_url() ?>client/mailbox/download_file/<?= html_escape($v_files->fileName) ?>"
                                   class="att-dl" title="Download"><i class="fa fa-cloud-download"></i></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
