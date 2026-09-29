<div class="panel panel-custom menu">
    <header class="panel-heading mb0">
        <h3 class="panel-title"><?= lang('my_project') ?><span class="sub">In progress</span></h3>
    </header>
    <div class="rowlist">
        <?php
        if (!empty($my_projects)):
            foreach ($my_projects as $v_my_project):
                $pct = (int)$v_my_project->progress;
                if ($pct < 0) {
                    $pct = 0;
                }
                if ($pct > 100) {
                    $pct = 100;
                }
                $due_ts = strtotime($v_my_project->end_date);
                $is_late = (strtotime(date('Y-m-d')) > $due_ts && $pct < 100);
                ?>
                <div class="goal">
                    <div class="gr">
                        <a href="<?= base_url() ?>admin/projects/project_details/<?= $v_my_project->project_id ?>"><?= $v_my_project->project_name ?></a>
                        <b><?= $pct ?>%</b>
                    </div>
                    <div class="track"><span style="width: <?= $pct ?>%; background: #f59e0b"></span></div>
                    <div class="due<?= $is_late ? ' late' : '' ?>">Due <?= strftime(config_item('date_format'), $due_ts) ?><?= $is_late ? ' &middot; ' . lang('overdue') : '' ?></div>
                </div>
                <?php
            endforeach;
        else:
            ?>
            <div class="empty"><?= lang('no_data') ?></div>
            <?php
        endif;
        ?>
    </div>
</div>
