<div class="panel panel-custom menu">
    <header class="panel-heading mb0">
        <h3 class="panel-title"><?= lang('active_timers') ?><span class="sub">Tracking now</span></h3>
    </header>
    <div class="rowlist">
        <?php
        if (!empty($active_timers)):
            foreach ($active_timers as $v_timer):
                $item_info = null;
                $name = '';
                $link = '#';
                $sub = '';
                $buttons = '';

                if (!empty($v_timer->task_id)) {
                    $item_info = get_row('tbl_task', array('task_id' => $v_timer->task_id));
                    if (!empty($item_info)) {
                        $name = $item_info->task_name;
                        $link = base_url() . 'admin/tasks/details/' . $v_timer->task_id;

                        if (!empty($item_info->project_id)) {
                            $project_info = get_row('tbl_project', array('project_id' => $item_info->project_id));
                            if (!empty($project_info)) {
                                $sub = $project_info->project_name;
                            }
                        }
                        if ($sub === '' && !empty($v_timer->fullname)) {
                            $sub = $v_timer->fullname;
                        }

                        $status = $v_timer->timer_status;
                        $is_admin = ($this->session->userdata('user_type') == '1');

                        if ($status == 'on') {
                            if ($is_admin) {
                                $hold_link = base_url() . 'admin/tasks/tasks_timer/hold/' . $v_timer->task_id;
                                $buttons .= '<a class="mini-btn" href="' . $hold_link . '" title="Hold Timer">Hold</a>';
                            } else {
                                $pause_link = base_url() . 'admin/tasks/tasks_timer/pause/' . $v_timer->task_id;
                                $buttons .= '<a class="mini-btn" href="' . $pause_link . '" title="Pause Timer">Pause</a>';
                            }
                            $stop_link = base_url() . 'admin/tasks/tasks_timer/off/' . $v_timer->task_id;
                            $buttons .= '<a class="mini-btn stop" href="' . $stop_link . '" title="Stop Timer">Stop</a>';
                        } else {
                            $resume_link = base_url() . 'admin/tasks/tasks_timer/on/' . $v_timer->task_id;
                            $buttons .= '<a class="mini-btn" href="' . $resume_link . '" title="Resume Timer">Resume</a>';
                            $stop_link = base_url() . 'admin/tasks/tasks_timer/off/' . $v_timer->task_id;
                            $buttons .= '<a class="mini-btn stop" href="' . $stop_link . '" title="Stop Timer">Stop</a>';
                        }
                    }
                } elseif (!empty($v_timer->project_id)) {
                    $item_info = get_row('tbl_project', array('project_id' => $v_timer->project_id));
                    if (!empty($item_info)) {
                        $name = $item_info->project_name;
                        $link = base_url() . 'admin/projects/project_details/' . $v_timer->project_id;
                        if (!empty($v_timer->fullname)) {
                            $sub = $v_timer->fullname;
                        }

                        $status = $v_timer->timer_status;
                        if ($status == 'on') {
                            $stop_link = base_url() . 'admin/projects/tasks_timer/off/' . $v_timer->project_id;
                            $buttons = '<a class="mini-btn stop" href="' . $stop_link . '" title="Stop Timer">Stop</a>';
                        }
                    }
                }

                if (empty($item_info)) {
                    continue;
                }

                $status = $v_timer->timer_status;
                if ($status == 'on') {
                    $dot = '#10b981';
                } elseif ($status == 'hold') {
                    $dot = '#f59e0b';
                } else {
                    $dot = '#94a3b8';
                }

                if ($status == 'on') {
                    $el = '&mdash;';
                    $start_raw = $v_timer->start_time;
                    $start_ts = is_numeric($start_raw) ? (int)$start_raw : strtotime($start_raw);
                    if (!empty($start_ts) && $start_ts > 0) {
                        $sec = time() - $start_ts;
                        if ($sec < 0) {
                            $sec = 0;
                        }
                        $hours = intdiv($sec, 3600);
                        $mins = intdiv($sec % 3600, 60);
                        $secs = $sec % 60;
                        if ($hours >= 24) {
                            $el = intdiv($hours, 24) . 'd ' . sprintf('%02d:%02d:%02d', $hours % 24, $mins, $secs);
                        } else {
                            $el = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        }
                    }
                } elseif ($status == 'pause') {
                    $el = 'Paused';
                } elseif ($status == 'hold') {
                    $el = 'On hold';
                } else {
                    $el = ucfirst($status);
                }
                ?>
                <div class="timer">
                    <span class="prio" style="background:<?= $dot ?>"></span>
                    <div class="tmeta">
                        <a class="tname" href="<?= $link ?>"><?= $name ?></a>
                        <?php if ($sub !== '') { ?>
                            <div class="tsub"><?= $sub ?></div>
                        <?php } ?>
                    </div>
                    <span class="el"><?= $el ?></span>
                    <span class="tbtns"><?= $buttons ?></span>
                </div>
                <?php
            endforeach;
        else:
            ?>
            <div class="empty"><?= lang('no_timer_found') ?></div>
            <?php
        endif;
        ?>
    </div>
</div>
