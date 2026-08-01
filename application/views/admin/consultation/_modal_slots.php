<?php
$slot_data = isset($slots) ? $slots : array();
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title">
        <?= lang('consultation_weekly_schedule') ?> — <?= htmlspecialchars($consultant->name) ?>
    </h4>
</div>
<div class="modal-body">
    <p class="text-muted">
        <?= lang('consultation_weekly_schedule_help') ?>
    </p>
    <form method="post" action="<?= base_url() ?>admin/consultation/save_slots/<?= $consultant->consultant_id ?>">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?= lang('consultation_active') ?></th>
                    <th><?= lang('consultation_day_of_week') ?></th>
                    <th><?= lang('consultation_start_time') ?></th>
                    <th><?= lang('consultation_end_time') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($days as $day_num => $day_name) : ?>
                    <?php
                    $existing = isset($slot_data[$day_num]) ? $slot_data[$day_num] : null;
                    $is_active = !empty($existing['is_active']);
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="is_active[<?= $day_num ?>]" value="1"
                                <?= $is_active ? 'checked' : '' ?>>
                        </td>
                        <td><?= $day_name ?></td>
                        <td>
                            <input type="time" name="start_time[<?= $day_num ?>]" class="form-control"
                                   value="<?= !empty($existing['start_time']) ? htmlspecialchars(substr($existing['start_time'], 0, 5)) : '' ?>">
                        </td>
                        <td>
                            <input type="time" name="end_time[<?= $day_num ?>]" class="form-control"
                                   value="<?= !empty($existing['end_time']) ? htmlspecialchars(substr($existing['end_time'], 0, 5)) : '' ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-purple"><?= lang('save') ?></button>
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
        </div>
    </form>
</div>
