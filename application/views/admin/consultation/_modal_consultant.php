<?php
$c = isset($consultant) ? $consultant : null;
$base_uri = 'admin/consultation/save_consultant' . (!empty($c) ? '/' . $c->consultant_id : '');
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title">
        <?= !empty($c) ? lang('consultation_edit_consultant') : lang('consultation_add_consultant') ?>
    </h4>
</div>
<div class="modal-body">
    <form method="post" action="<?= base_url() . $base_uri ?>" data-parsley-validate novalidate>
        <div class="form-group">
            <label><?= lang('consultation_consultant_name') ?> *</label>
            <input type="text" name="name" class="form-control" required
                   value="<?= !empty($c) ? htmlspecialchars($c->name) : '' ?>">
        </div>
        <div class="form-group">
            <label><?= lang('consultation_consultant_email') ?> *</label>
            <input type="email" name="email" class="form-control" required
                   value="<?= !empty($c) ? htmlspecialchars($c->email) : '' ?>">
        </div>
        <div class="form-group">
            <label><?= lang('consultation_consultant_phone') ?></label>
            <input type="text" name="phone" class="form-control"
                   value="<?= !empty($c) ? htmlspecialchars($c->phone) : '' ?>">
        </div>
        <div class="form-group">
            <label><?= lang('consultation_department') ?></label>
            <input type="text" name="department" class="form-control"
                   value="<?= !empty($c) ? htmlspecialchars($c->department) : '' ?>">
        </div>
        <div class="form-group">
            <label><?= lang('consultation_timezone') ?> *</label>
            <select name="timezone" class="form-control select_box" required>
                <option value=""><?= lang('consultation_timezone') ?></option>
                <?php foreach ($timezones as $tz) : ?>
                    <option value="<?= $tz ?>" <?= !empty($c) && $c->timezone === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label><?= lang('consultation_bio') ?></label>
            <textarea name="bio" class="form-control" rows="3"><?= !empty($c) ? htmlspecialchars($c->bio) : '' ?></textarea>
        </div>
        <div class="checkbox c-checkbox">
            <label>
                <input type="checkbox" name="is_active" value="1"
                    <?= empty($c) || (int)$c->is_active === 1 ? 'checked' : '' ?>>
                <span class="fa fa-check"></span> <?= lang('consultation_active') ?>
            </label>
        </div>
        <div class="form-group mt-lg">
            <button type="submit" class="btn btn-purple"><?= lang('save') ?></button>
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
        </div>
    </form>
</div>
