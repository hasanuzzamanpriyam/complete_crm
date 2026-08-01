<?php
$s = $settings;
?>
<?php $this->load->view('admin/consultation/_tabs', array('active_tab' => 'settings')); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('consultation_settings') ?></div>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= base_url() ?>admin/consultation/save_settings"
              data-parsley-validate novalidate>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= lang('consultation_booking_enabled') ?></label>
                        <div class="checkbox c-checkbox">
                            <label>
                                <input type="checkbox" name="booking_enabled" value="1"
                                    <?= (isset($s['booking_enabled']) && $s['booking_enabled'] === '1') ? 'checked' : '' ?>>
                                <span class="fa fa-check"></span>
                                <?= lang('consultation_booking_enabled') ?>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?= lang('consultation_default_duration') ?> (<?= lang('consultation_minutes') ?>)</label>
                        <input type="number" class="form-control" name="default_duration" min="5" max="240" required
                               value="<?= htmlspecialchars(isset($s['default_duration']) ? $s['default_duration'] : 30) ?>">
                    </div>
                    <div class="form-group">
                        <label><?= lang('consultation_min_advance_hours') ?></label>
                        <input type="number" class="form-control" name="min_advance_hours" min="0" max="168" required
                               value="<?= htmlspecialchars(isset($s['min_advance_hours']) ? $s['min_advance_hours'] : 2) ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= lang('consultation_buffer_minutes') ?></label>
                        <input type="number" class="form-control" name="buffer_minutes" min="0" max="120" required
                               value="<?= htmlspecialchars(isset($s['buffer_minutes']) ? $s['buffer_minutes'] : 15) ?>">
                    </div>
                    <div class="form-group">
                        <label><?= lang('consultation_reminder_hours') ?></label>
                        <input type="text" class="form-control" name="reminder_hours"
                               placeholder="e.g. 24, 1"
                               value="<?= htmlspecialchars(isset($s['reminder_hours']) ? $s['reminder_hours'] : '24,1') ?>">
                        <p class="help-block"><?= lang('consultation_reminder_hours_help') ?></p>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-purple">
                    <i class="fa fa-check"></i> <?= lang('save') ?>
                </button>
            </div>
        </form>
    </div>
</div>
