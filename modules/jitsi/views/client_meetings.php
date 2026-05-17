<?php defined('BASEPATH') or exit('No direct script access allowed');
echo message_box('success');
echo message_box('error');
?>
<div class="panel panel-custom">
    <header class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('jitsi') ?></strong>
        </div>
    </header>
    <div class="panel-body">
        <?php if (empty($meetings)) : ?>
            <div class="text-center text-muted" style="padding: 40px;">
                <i class="fa fa-video-camera fa-3x"></i>
                <p class="mt"><?= lang('no_meetings_found') ?></p>
            </div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?= lang('topic') ?></th>
                            <th><?= lang('meeting_time') ?></th>
                            <th><?= lang('host') ?></th>
                            <th><?= lang('status') ?></th>
                            <th><?= lang('join') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meetings as $meeting) :
                            $status_class = 'primary';
                            if ($meeting->status == 'finished') {
                                $status_class = 'success';
                            } elseif ($meeting->status == 'canceled') {
                                $status_class = 'danger';
                            }
                        ?>
                            <tr>
                                <td><?= $meeting->topic ?></td>
                                <td><?= display_datetime($meeting->meeting_time) ?></td>
                                <td><?= $meeting->host_name ?></td>
                                <td><span class="label label-<?= $status_class ?>"><?= lang($meeting->status) ?></span></td>
                                <td>
                                    <?php if ($meeting->status != 'canceled') : ?>
                                        <a href="<?= base_url('jitsi/join_meeting/' . url_encode($meeting->jitsi_meeting_id)) ?>" class="btn btn-xs btn-warning" target="_blank">
                                            <i class="fa fa-video-camera"></i> <?= lang('join_the_meeting') ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted"><?= lang('meeting_ended') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
