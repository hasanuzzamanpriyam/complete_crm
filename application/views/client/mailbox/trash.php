<div class="trash-tabs">
    <a class="ttab<?php echo ($trash_view == 'inbox') ? ' on' : ''; ?>" href="<?= base_url() ?>client/mailbox/index/trash/inbox"><i class="fa fa-inbox"></i> <?= lang('inbox') ?></a>
    <a class="ttab<?php echo ($trash_view == 'sent') ? ' on' : ''; ?>" href="<?= base_url() ?>client/mailbox/index/trash/sent"><i class="fa fa-envelope-o"></i> <?= lang('sent') ?></a>
    <a class="ttab<?php echo ($trash_view == 'draft') ? ' on' : ''; ?>" href="<?= base_url() ?>client/mailbox/index/trash/draft"><i class="fa fa-file-text-o"></i> <?= lang('draft') ?></a>
</div>
<div class="trash-body">
    <?php $this->load->view('client/mailbox/trash/' . $trash_view) ?>
</div>
