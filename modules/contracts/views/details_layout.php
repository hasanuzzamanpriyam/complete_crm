<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<style>
    .note-editor .note-editable {
        height: 150px;
    }
</style>



<div class="row mt-lg">
    <div class="col-sm-2">
        <ul class=" nav nav-pills nav-stacked navbar-custom-nav">
            <li class="<?php if ($active == 'details') echo 'active'; ?>"><a href="<?= base_url('admin/contracts/details/' . $id); ?>"><?= lang('contract'); ?></a>
            </li>
            <li class="<?php if ($active == 'attachments') echo 'active'; ?>"><a href="<?= base_url('admin/contracts/attachments/' . $id); ?>"><?= lang('attachments'); ?>
                </a></li>
            <li class="<?php if ($active == 'comments') echo 'active'; ?>"><a href="<?= base_url('admin/contracts/comments/' . $id); ?>"><?= lang('comments'); ?>
                </a></li>
            <li class="<?php if ($active == 'history') echo 'active'; ?>"><a href="<?= base_url('admin/contracts/history/' . $id); ?>"><?= lang('renewal_history'); ?>
                </a></li>

            <li class="<?php if ($active == 'tasks') echo 'active'; ?>"><a href="<?= base_url('admin/contracts/tasks/' . $id); ?>"><?= lang('tasks'); ?>
                </a></li>
            <li class="<?php if ($active == 'notes') echo 'active'; ?>"><a href="<?= base_url('admin/contracts/notes/' . $id); ?>" aria-expanded="false"><?= lang('notes'); ?> </a>
            </li>
            <li class="<?php if ($active == 'templates') echo 'active'; ?>"><a href="<?= base_url('admin/contracts/templates/' . $id); ?>"><?= lang('templates'); ?></a>
            </li>
        </ul>
    </div>
    <div class="col-sm-10">

        <div class="tab-content b0 p0" >

            <?= $page_content ?>

        </div>
    </div>
</div>