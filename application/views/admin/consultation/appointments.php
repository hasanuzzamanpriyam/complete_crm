<?php $this->load->view('admin/consultation/_tabs', array('active_tab' => 'appointments')); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('consultation_appointments') ?></div>
    </div>
    <div class="panel-body">
        <div class="row mb-lg">
            <?php $counts = $status_counts; ?>
            <div class="col-sm-2 col-xs-6">
                <a href="<?= base_url() ?>admin/consultation/appointments"
                   class="small-box text-center <?= empty($status) ? 'active' : '' ?>">
                    <h4><?= array_sum((array)$counts) ?></h4>
                    <span><?= lang('consultation_all_status') ?></span>
                </a>
            </div>
            <?php
            $labels = array(
                'confirmed' => 'success',
                'pending' => 'info',
                'completed' => 'primary',
                'cancelled' => 'danger',
                'no_show' => 'warning',
            );
            foreach ($labels as $key => $color) {
                $count = isset($counts[$key]) ? $counts[$key] : 0;
                ?>
                <div class="col-sm-2 col-xs-6">
                    <a href="<?= base_url() ?>admin/consultation/appointments?status=<?= $key ?>"
                       class="small-box text-center <?= $status === $key ? 'active' : '' ?>">
                        <h4><?= $count ?></h4>
                        <span><?= lang('consultation_status_' . $key) ?></span>
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <form method="get" action="<?= base_url() ?>admin/consultation/appointments" class="form-inline">
                    <div class="form-group">
                        <select name="consultant_id" class="form-control">
                            <option value=""><?= lang('consultation_all_consultants') ?></option>
                            <?php foreach ($consultants as $c) : ?>
                                <option value="<?= $c->consultant_id ?>" <?= (int)$consultant_id === (int)$c->consultant_id ? 'selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" name="from_date" value="<?= htmlspecialchars($from_date) ?>"
                               class="form-control datepicker" placeholder="<?= lang('consultation_from_date') ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" name="to_date" value="<?= htmlspecialchars($to_date) ?>"
                               class="form-control datepicker" placeholder="<?= lang('consultation_to_date') ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                               class="form-control" placeholder="<?= lang('consultation_search') ?>">
                    </div>
                    <button type="submit" class="btn btn-purple"><?= lang('consultation_filter') ?></button>
                    <a href="<?= base_url() ?>admin/consultation/appointments" class="btn btn-default"><?= lang('consultation_reset') ?></a>
                </form>
            </div>
        </div>

        <div class="table-responsive mt-lg">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?= lang('consultation_customer_name') ?></th>
                    <th><?= lang('consultation_consultant') ?></th>
                    <th><?= lang('consultation_appointment_date') ?></th>
                    <th><?= lang('consultation_appointment_time') ?></th>
                    <th><?= lang('consultation_duration') ?></th>
                    <th><?= lang('consultation_status') ?></th>
                    <th><?= lang('consultation_action') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($appointments)) : ?>
                    <?php foreach ($appointments as $i => $app) : ?>
                        <tr>
                            <td><?= $app->appointment_id ?></td>
                            <td>
                                <strong><?= htmlspecialchars($app->customer_name) ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($app->customer_email) ?></small>
                            </td>
                                                    <td><?= !empty($app->consultant_name) ? htmlspecialchars($app->consultant_name) : lang('consultation_unassigned') ?></td>
                            <td><?= consultation_format_date($app->appointment_date) ?></td>
                            <td>
                                <?= consultation_format_time($app->appointment_time) ?>
                                <small class="text-muted">(<?= htmlspecialchars($app->customer_timezone) ?>)</small>
                            </td>
                            <td><?= $app->duration_minutes ?> <?= lang('consultation_minutes') ?></td>
                            <td>
                                <span class="label label-<?= $labels[$app->status] ?>"><?= lang('consultation_status_' . $app->status) ?></span>
                            </td>
                            <td>
                                <?= btn_view_modal('admin/consultation/view_appointment/' . $app->appointment_id) ?>
                                <?php if ($app->status !== 'cancelled') : ?>
                                    <form method="post"
                                          action="<?= base_url() ?>admin/consultation/cancel_appointment/<?= $app->appointment_id ?>"
                                          style="display:inline-block"
                                          onsubmit="return confirm('<?= lang('consultation_confirm_cancel_appointment') ?>');">
                                        <button type="submit" class="btn btn-danger btn-xs"
                                                title="<?= lang('consultation_cancel_appointment') ?>">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted"><?= lang('consultation_no_appointments') ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('consultation_change_status') ?></div>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= base_url() ?>admin/consultation/change_status"
              class="form-inline" id="change-status-form">
            <div class="form-group">
                <select name="appointment_id" class="form-control" id="status-appointment-id">
                    <option value=""><?= lang('consultation_appointment') ?> #</option>
                    <?php foreach ($appointments as $app) : ?>
                        <option value="<?= $app->appointment_id ?>">#<?= $app->appointment_id ?> — <?= htmlspecialchars($app->customer_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select name="status" class="form-control">
                    <option value=""><?= lang('consultation_status') ?></option>
                    <?php foreach ($labels as $key => $color) : ?>
                        <option value="<?= $key ?>"><?= lang('consultation_status_' . $key) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-purple"><?= lang('consultation_change_status') ?></button>
        </form>
    </div>
</div>

<style>
    .small-box { display: block; border: 1px solid #eee; border-radius: 3px; padding: 12px; margin-bottom: 15px; color: #333; }
    .small-box h4 { margin: 0 0 4px; font-weight: 700; }
    .small-box:hover, .small-box.active { border-color: #7266ba; background: #f7f7ff; }
</style>

<script>
    $(function () {
        $('#change-status-form').on('submit', function () {
            var id = $('#status-appointment-id').val();
            if (!id) {
                toastr.error('<?= lang('consultation_appointment') ?> # <?= lang('is_required') ?>');
                return false;
            }
        });
    });
</script>
