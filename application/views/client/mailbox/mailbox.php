<div class="mbflex">
    <div class="folders">
        <div class="sec"><?= lang('all_messages') ?></div>
        <a class="<?php echo ($menu_active == 'inbox') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>client/mailbox/index/inbox">
            <span class="fi"><i class="fa fa-inbox"></i></span><?= lang('inbox') ?>
            <span class="fc<?php echo (!empty($unread_mail) && $unread_mail > 0) ? ' hot' : ''; ?>"><?php
                echo !empty($unread_mail) ? $unread_mail : 0;
            ?></span>
        </a>
        <a class="<?php echo ($menu_active == 'sent') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>client/mailbox/index/sent">
            <span class="fi"><i class="fa fa-paper-plane"></i></span><?= lang('sent') ?>
        </a>
        <a class="<?php echo ($menu_active == 'draft') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>client/mailbox/index/draft">
            <span class="fi"><i class="fa fa-pencil"></i></span>Drafts
        </a>
        <a class="<?php echo ($menu_active == 'favourites') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>client/mailbox/index/favourites">
            <span class="fi"><i class="fa fa-star"></i></span><?= lang('favourites') ?>
        </a>
        <a class="<?php echo ($menu_active == 'trash') ? 'on f-item' : 'f-item'; ?>"
           href="<?= base_url() ?>client/mailbox/index/trash">
            <span class="fi"><i class="fa fa-trash-o"></i></span><?= lang('trash') ?>
            <span class="fc"><?php
                $inbox_query = $this->db->where(array('to' => $this->session->userdata('email'), 'deleted' => 'Yes'))->get('tbl_inbox');
                $totat_inbox = $inbox_query->num_rows();
                $sent_query = $this->db->where(array('user_id' => $this->session->userdata('user_id'), 'deleted' => 'Yes'))->get('tbl_sent');
                $totat_sent = $sent_query->num_rows();
                $draft_query = $this->db->where(array('user_id' => $this->session->userdata('user_id'), 'deleted' => 'Yes'))->get('tbl_draft');
                $tatal_draft = $draft_query->num_rows();
                echo $totat_inbox + $totat_sent + $tatal_draft;
            ?></span>
        </a>
    </div>
    <div class="list-col">
        <?php echo message_box('success'); ?>
        <?php echo message_box('error'); ?>
        <?php $this->load->view('client/mailbox/' . $view) ?>
    </div>
</div>
