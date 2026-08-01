<?php $this->load->view('admin/consultation/_tabs', array('active_tab' => 'consultants')); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="pull-left panel-title"><?= lang('consultation_consultants') ?></div>
        <div class="pull-right">
            <a href="<?= base_url() ?>admin/consultation/consultant_form"
               class="btn btn-purple btn-sm" data-toggle="modal" data-target="#myModal">
                <i class="fa fa-plus"></i> <?= lang('consultation_add_consultant') ?>
            </a>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?= lang('consultation_consultant_name') ?></th>
                    <th><?= lang('consultation_department') ?></th>
                    <th><?= lang('consultation_consultant_email') ?></th>
                    <th><?= lang('consultation_timezone') ?></th>
                    <th><?= lang('consultation_status') ?></th>
                    <th><?= lang('consultation_weekly_schedule') ?></th>
                    <th><?= lang('consultation_action') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($consultants)) : ?>
                    <?php foreach ($consultants as $c) : ?>
                        <tr>
                            <td><?= $c->consultant_id ?></td>
                            <td><strong><?= htmlspecialchars($c->name) ?></strong></td>
                            <td><?= htmlspecialchars($c->department) ?></td>
                            <td><?= htmlspecialchars($c->email) ?></td>
                            <td><?= htmlspecialchars($c->timezone) ?></td>
                            <td>
                                <?php if ((int)$c->is_active === 1) : ?>
                                    <span class="label label-success"><?= lang('consultation_active') ?></span>
                                <?php else : ?>
                                    <span class="label label-default"><?= lang('inactive') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url() ?>admin/consultation/slots/<?= $c->consultant_id ?>"
                                   class="btn btn-warning btn-xs" data-toggle="modal" data-target="#myModal"
                                   title="<?= lang('consultation_edit_schedule') ?>">
                                    <i class="fa fa-calendar"></i> <?= lang('consultation_edit_schedule') ?>
                                </a>
                            </td>
                            <td>
                                <?= btn_edit_modal('admin/consultation/consultant_form/' . $c->consultant_id) ?>
                                <?= btn_delete('admin/consultation/delete_consultant/' . $c->consultant_id, null, 'delete') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted"><?= lang('no_record_found') ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
