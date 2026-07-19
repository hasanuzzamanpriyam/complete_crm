<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function live_users()
    {
        $user = $this->api_auth->authenticate();

        $visible_ids = $this->api_auth->get_authorized_user_ids();

        $this->db->select('u.user_id, u.online_time, u.last_active_ping, a.fullname, a.avatar')
            ->from('tbl_users u')
            ->join('tbl_account_details a', 'a.user_id = u.user_id', 'left')
            ->where('u.activated', 1)
            ->where('u.banned', 0);
        if ($visible_ids !== null) {
            $this->db->where_in('u.user_id', $visible_ids);
        }
        $all_users = $this->db->get()->result();

        $running_entries = $this->db
            ->select('tde.user_id, tde.paused_at, t.task_name as task_title')
            ->from('tbl_desktop_time_entries tde')
            ->join('tbl_task t', 't.task_id = tde.task_id', 'left')
            ->where('tde.is_running', 1)
            ->where('tde.stopped_at IS NULL', null, false);
        if ($visible_ids !== null) {
            $this->db->where_in('tde.user_id', $visible_ids);
        }
        $running_rows = $running_entries->get()->result();

        $active_map = [];
        $paused_user_ids = [];
        $active_user_ids = [];
        foreach ($running_rows as $e) {
            $uid = (int)$e->user_id;
            $active_map[$uid] = ['task_title' => $e->task_title];
            $active_user_ids[] = $uid;
            if (!empty($e->paused_at)) {
                $paused_user_ids[] = $uid;
            }
        }

        $active_count = 0;
        $paused_count = 0;
        $idle_count = 0;
        $offline_count = 0;
        $active_users_list = [];
        $now_ts = time();

        foreach ($all_users as $u) {
            $uid = (int)$u->user_id;
            $is_running = in_array($uid, $active_user_ids);
            $is_paused = in_array($uid, $paused_user_ids);
            $online_time_ok = !empty($u->online_time) && (int)$u->online_time > ($now_ts - 300);

            if ($is_running) {
                $window = $this->db
                    ->select('app_name, window_title')
                    ->from('tbl_desktop_app_usage')
                    ->where('user_id', $uid)
                    ->order_by('created_at', 'DESC')
                    ->limit(1)
                    ->get()->row();

                $status = $is_paused ? 'paused' : 'active';
                if ($is_paused) {
                    $paused_count++;
                } else {
                    $active_count++;
                }

                $active_users_list[] = [
                    'user_id' => $uid,
                    'name' => !empty($u->fullname) ? $u->fullname : $u->username,
                    'avatar' => base_url(!empty($u->avatar) ? $u->avatar : 'assets/img/user/default_avatar.jpg'),
                    'status' => $status,
                    'current_task' => $active_map[$uid]['task_title'],
                    'current_window' => $window ? (!empty($window->window_title) ? $window->window_title : $window->app_name) : null,
                    'is_active_now' => !empty($u->last_active_ping) && strtotime($u->last_active_ping) >= (time() - 120),
                ];
            } elseif ($online_time_ok) {
                $idle_count++;
            } else {
                $offline_count++;
            }
        }

        return $this->_respond(200, true, 'OK', [
            'summary' => [
                'total' => count($all_users),
                'active' => $active_count,
                'paused' => $paused_count,
                'idle' => $idle_count,
                'offline' => $offline_count,
            ],
            'active_users' => $active_users_list,
        ]);
    }

    public function summary()
    {
        $user = $this->api_auth->authenticate();
        $period = $this->input->get('period') ?? 'weekly';
        $start_date = $this->input->get('start_date');
        $end_date_input = $this->input->get('end_date');

        $has_date_range = $start_date && $end_date_input;

        if ($has_date_range) {
            $since = $start_date;
            $until = $end_date_input;
        } else {
            $since = $period === 'monthly' ? date('Y-m-d', strtotime('-30 days')) : date('Y-m-d', strtotime('-7 days'));
            $until = null;
        }
        $until_dt = $until ? $until . ' 23:59:59' : null;

        // Chart trend data always spans 7 days ending on $until, regardless of period.
        // KPIs and other summaries continue to use the original $since range.
        $chart_since = $has_date_range
            ? date('Y-m-d', strtotime($until . ' -6 days'))
            : $since;

        $is_admin = $this->api_auth->is_super_admin();
        $is_manager = (int)$user->role_id === 3;
        $current_user_id = (int)$user->user_id;

        $response = [];

        // Personal section — always present for the current user
        if ($has_date_range) {
            $today_row = $this->db
                ->select("COALESCE(SUM(total_seconds), 0) as total")
                ->from('tbl_desktop_time_entries')
                ->where('user_id', $current_user_id)
                ->where('started_at >=', $since)
                ->where('started_at <=', $until_dt)
                ->where('type', 'work')
                ->get()->row();
        } else {
            $today_row = $this->db
                ->select("COALESCE(SUM(total_seconds), 0) as total")
                ->from('tbl_desktop_time_entries')
                ->where('user_id', $current_user_id)
                ->where('DATE(started_at)', 'CURDATE()', false)
                ->where('type', 'work')
                ->get()->row();
        }

        $weekly_q = $this->db
            ->select("COALESCE(SUM(total_seconds), 0) as total")
            ->from('tbl_desktop_time_entries')
            ->where('user_id', $current_user_id)
            ->where('started_at >=', $since)
            ->where('type', 'work');
        if ($has_date_range) {
            $weekly_q->where('started_at <=', $until_dt);
        }
        $weekly_row = $weekly_q->get()->row();

        $in_progress_q = $this->db
            ->select("COUNT(*) as cnt")
            ->from('tbl_task')
            ->where('task_status', 'in_progress')
            ->where('created_by', $current_user_id);
        if ($has_date_range) {
            $in_progress_q->where('task_created_date >=', $since)
                ->where('task_created_date <=', $until);
        }
        $in_progress_row = $in_progress_q->get()->row();

        $personal_trend_q = $this->db
            ->select("DATE(started_at) as date, SUM(total_seconds) as total_seconds")
            ->from('tbl_desktop_time_entries')
            ->where('user_id', $current_user_id)
            ->where('started_at >=', $chart_since)
            ->where('type', 'work')
            ->group_by('DATE(started_at)')
            ->order_by('DATE(started_at)', 'ASC');
        if ($has_date_range) {
            $personal_trend_q->where('started_at <=', $until_dt);
        }
        $personal_trend = $personal_trend_q->get()->result();

        $visible_project_ids = $this->api_auth->get_visible_project_ids([$current_user_id]);

        $time_sub = "(";
        $time_sub .= "SELECT p2.project_id, SUM(te.total_seconds) as total_seconds";
        $time_sub .= " FROM tbl_project p2";
        $time_sub .= " JOIN tbl_task t ON t.project_id = p2.project_id";
        $time_sub .= " JOIN tbl_desktop_time_entries te ON te.task_id = t.task_id";
        $time_sub .= " WHERE te.user_id = $current_user_id";
        $time_sub .= " AND te.type = 'work'";
        $time_sub .= " AND te.started_at >= " . $this->db->escape($since);
        if ($has_date_range) {
            $time_sub .= " AND te.started_at <= " . $this->db->escape($until_dt);
        }
        $time_sub .= " GROUP BY p2.project_id) sub";

        $project_q = $this->db
            ->select("p.project_id, p.project_name, p.progress, p.project_status, COALESCE(sub.total_seconds, 0) as total_seconds")
            ->from('tbl_project p')
            ->join($time_sub, 'sub.project_id = p.project_id', 'left');
        if ($visible_project_ids !== null) {
            $project_q->where_in('p.project_id', $visible_project_ids);
        }
        $project_q->group_by('p.project_id, p.project_name')
            ->order_by('total_seconds', 'DESC');
        $project_rows = $project_q->get()->result();

        $grand_total = array_sum(array_map(function ($r) { return (int)$r->total_seconds; }, $project_rows));

        $app_usage_rows = $this->db
            ->select('app_name, SUM(total_seconds) as total_sec')
            ->from('tbl_desktop_app_usage')
            ->where('user_id', $current_user_id)
            ->where('recorded_at >=', $since);
        if ($has_date_range) {
            $app_usage_rows->where('recorded_at <=', $until_dt);
        }
        $app_usage_rows = $app_usage_rows->group_by('app_name')->get()->result();
        $total_app_sec = 0;
        $effective_prod_sec = 0;
        foreach ($app_usage_rows as $au) {
            $sec = (int)$au->total_sec;
            $total_app_sec += $sec;
            $cat = $this->_categorize_app($au->app_name);
            if ($cat === 'productive') $effective_prod_sec += $sec;
            elseif ($cat === 'neutral') $effective_prod_sec += $sec * 0.5;
        }
        $productive_ratio = $total_app_sec > 0 ? round(($effective_prod_sec / $total_app_sec) * 100, 1) : 0;

        $response['personal'] = [
            'hours_today' => round((float)$today_row->total / 3600, 1),
            'weekly_total_seconds' => (int)$weekly_row->total,
            'tasks_in_progress_count' => (int)$in_progress_row->cnt,
            'productive_ratio' => $productive_ratio,
            'weekly_trend' => array_map(function ($r) {
                return ['date' => $r->date, 'total_seconds' => (int)$r->total_seconds];
            }, $personal_trend),
            'project_distribution' => array_map(function ($r) use ($grand_total) {
                $secs = (int)$r->total_seconds;
                return [
                    'project_id' => (int)$r->project_id,
                    'project_name' => $r->project_name ?? 'No Project',
                    'total_seconds' => $secs,
                    'percentage' => $grand_total > 0 ? round(($secs / $grand_total) * 100, 1) : 0,
                    'progress' => (int)($r->progress ?? 0),
                    'project_status' => $r->project_status ?? '',
                ];
            }, $project_rows),
        ];

        // Team section — admin/manager only
        if ($is_admin || $is_manager) {
            $company_ids = $this->api_auth->get_authorized_user_ids(null);

            $team_trend_q = $this->db
                ->select("DATE(started_at) as date, SUM(total_seconds) as total_seconds")
                ->from('tbl_desktop_time_entries')
                ->where('started_at >=', $chart_since)
                ->where('type', 'work');
            if ($has_date_range) {
                $team_trend_q->where('started_at <=', $until_dt);
            }
            if ($company_ids !== null) {
                $team_trend_q->where_in('user_id', $company_ids);
            }
            $team_trend_rows = $team_trend_q->group_by('DATE(started_at)')->order_by('DATE(started_at)', 'ASC')->get()->result();

            $brief_on = "te.user_id = u.user_id";
            if ($has_date_range) {
                $brief_on .= " AND te.started_at >= " . $this->db->escape($since) . " AND te.started_at <= " . $this->db->escape($until_dt);
            } else {
                $brief_on .= " AND DATE(te.started_at) = CURDATE()";
            }
            $brief_on .= " AND te.type = 'work'";
            $brief_q = $this->db
                ->select("u.user_id, u.online_time, u.last_active_ping, u.role_id, ad.fullname as name, ad.avatar, COALESCE(SUM(te.total_seconds), 0) as today_seconds")
                ->from('tbl_users u')
                ->join('tbl_account_details ad', 'ad.user_id = u.user_id', 'left')
                ->join('tbl_desktop_time_entries te', $brief_on, 'left')
                ->where('u.activated', 1)
                ->where('u.banned', 0);
            if ($company_ids !== null) {
                $brief_q->where_in('u.user_id', $company_ids);
            }
            $brief_rows = $brief_q->group_by('u.user_id')->get()->result();

            $running_entries = $this->db
                ->select('user_id, paused_at')
                ->from('tbl_desktop_time_entries')
                ->where('is_running', 1)
                ->where('stopped_at IS NULL', null, false)
                ->get()->result();
            $active_user_ids = [];
            $paused_user_ids = [];
            foreach ($running_entries as $e) {
                $uid = (int)$e->user_id;
                $active_user_ids[] = $uid;
                if (!empty($e->paused_at)) {
                    $paused_user_ids[] = $uid;
                }
            }

            $now_ts = time();

$time_where = $has_date_range
                ? "started_at >= " . $this->db->escape($since) . " AND started_at <= " . $this->db->escape($until_dt)
                : "DATE(started_at) = CURDATE()";
            $completed_where = $has_date_range
                ? "task_created_date >= " . $this->db->escape($since) . " AND task_created_date <= " . $this->db->escape($until)
                : "DATE(task_created_date) = CURDATE()";

            $time_scope = $company_ids !== null ? " AND user_id IN (" . implode(',', $company_ids) . ")" : "";
            $task_scope = $company_ids !== null ? " AND created_by IN (" . implode(',', $company_ids) . ")" : "";

            $global = $this->db
                ->select("
                    (SELECT COALESCE(SUM(total_seconds), 0) FROM tbl_desktop_time_entries WHERE $time_where AND type = 'work'$time_scope) as total_hours,
                    (SELECT COUNT(DISTINCT user_id) FROM tbl_desktop_time_entries WHERE is_running = 1 AND stopped_at IS NULL$time_scope) as active_users,
                    (SELECT COUNT(*) FROM tbl_task WHERE task_status = 'completed' AND $completed_where$task_scope) as tasks_completed
                ")
                ->get()->row();

            $dept_heads = [];
            $dept_result = $this->db->select('department_head_id')
                ->where('department_head_id >', 0)
                ->get('tbl_departments')
                ->result();
            foreach ($dept_result as $d) {
                $dept_heads[(int)$d->department_head_id] = true;
            }

            $response['team'] = [
                'total_company_hours_today' => round((float)$global->total_hours / 3600, 1),
                'active_users_count' => (int)$global->active_users,
                'tasks_completed_today' => (int)$global->tasks_completed,
                'weekly_trend' => array_map(function ($r) {
                    return ['date' => $r->date, 'total_seconds' => (int)$r->total_seconds];
                }, $team_trend_rows),
                'team_list' => array_map(function ($r) use ($active_user_ids, $paused_user_ids, $now_ts, $dept_heads) {
                    $uid = (int)$r->user_id;
                    $is_running = in_array($uid, $active_user_ids);
                    $is_paused = in_array($uid, $paused_user_ids);
                    $online = !empty($r->online_time) && (int)$r->online_time > ($now_ts - 300);
                    $status = $is_running ? ($is_paused ? 'paused' : 'active') : ($online ? 'idle' : 'offline');
                    $role_id = (int)$r->role_id;
                    $role = $role_id === 1 ? 'admin' : (isset($dept_heads[$uid]) ? 'manager' : 'member');
                    return [
                        'user_id' => $uid,
                        'name' => $r->name ?? 'Unknown',
                        'avatar' => base_url(!empty($r->avatar) ? $r->avatar : 'assets/img/user/default_avatar.jpg'),
                        'hours_today' => round((float)$r->today_seconds / 3600, 1),
                        'status' => $status,
                        'is_active_now' => !empty($r->last_active_ping) && strtotime($r->last_active_ping) >= (time() - 120),
                        'role' => $role,
                    ];
                }, $brief_rows),
            ];
        }

        return $this->_respond(200, true, 'OK', $response);
    }

    private function _categorize_app($name) {
        $productive = ['vscode', 'code', 'visual studio', 'cursor', 'windsurf',
            'terminal', 'cmd', 'powershell', 'windows terminal', 'git bash',
            'phpstorm', 'webstorm', 'intellij', 'pycharm', 'goland', 'rubymine',
            'sublime text', 'atom', 'notepad++', 'vim', 'neovim', 'nano',
            'excel', 'word', 'outlook', 'powerpoint', 'onenote',
            'chrome', 'firefox', 'edge', 'brave', 'opera', 'arc',
            'slack', 'teams', 'discord', 'zoom', 'meet', 'webex',
            'postman', 'tableplus', 'heidisql', 'dbeaver', 'datagrip',
            'git', 'github desktop', 'sourcetree', 'fork',
            'figma', 'sketch', 'adobe xd', 'photoshop', 'illustrator',
            'docker', 'k9s', 'lens', 'kubectl',
            'wsl', 'ubuntu', 'debian'];
        $neutral = ['file explorer', 'explorer', 'finder', 'settings',
            'system settings', 'calculator', 'calendar', 'clock',
            'notes', 'reminders', 'spotlight', 'search'];
        $lower = strtolower(trim($name));
        foreach ($productive as $p) { if (strpos($lower, $p) !== false) return 'productive'; }
        foreach ($neutral as $n) { if (strpos($lower, $n) !== false) return 'neutral'; }
        return 'distracting';
    }

    private function _app_color($name) {
        $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6',
                   '#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
        return $colors[abs(crc32($name)) % count($colors)];
    }

    public function discrepancy()
    {
        $user = $this->api_auth->authenticate();
        $target_user_id = $this->input->get('user_id') ? (int)$this->input->get('user_id') : (int)$user->user_id;
        $start_date = $this->input->get('start_date') ?? date('Y-m-d', strtotime('-7 days'));
        $end_date = $this->input->get('end_date') ?? date('Y-m-d');

        $eff = $this->api_auth->get_authorized_user_ids($target_user_id);
        if ($eff === null || !in_array($target_user_id, $eff)) {
            return $this->_respond(403, false, 'Access denied');
        }

        $working_days = $this->_count_working_days($start_date, $end_date);
        $leave_days = $this->_count_user_leave_days($target_user_id, $start_date, $end_date);
        $adjusted_wd = max(0, $working_days - $leave_days);

        $as_of = $end_date . ' 23:59:59';
        $setting = $this->db
            ->query("SELECT required_daily_hours FROM tbl_timesync_user_settings_log
                WHERE user_id = ? AND changed_at <= ?
                ORDER BY changed_at DESC LIMIT 1", [$target_user_id, $as_of])
            ->row();
        if ($setting) {
            $daily_hours = (float)$setting->required_daily_hours;
        } else {
            $config = $this->db
                ->query("SELECT value FROM tbl_timesync_config_log
                    WHERE config_key = 'timesync_default_daily_hours' AND changed_at <= ?
                    ORDER BY changed_at DESC LIMIT 1", [$as_of])
                ->row();
            $daily_hours = (float)($config->value ?? config_item('timesync_default_daily_hours') ?: 8.0);
        }

        $required_sec = $daily_hours * $adjusted_wd * 3600;

        $logged_row = $this->db
            ->select('COALESCE(SUM(total_seconds), 0) as total')
            ->from('tbl_desktop_time_entries')
            ->where('user_id', $target_user_id)
            ->where('started_at >=', $start_date)
            ->where('started_at <=', $end_date . ' 23:59:59')
            ->get()->row();
        $logged_sec = (int)$logged_row->total;
        $logged_hours = round($logged_sec / 3600, 2);

        $required_hours = round($required_sec / 3600, 2);
        $diff = round($logged_hours - $required_hours, 2);

        $daily_rows = $this->db
            ->select("DATE(started_at) as date, SUM(total_seconds) as sec")
            ->from('tbl_desktop_time_entries')
            ->where('user_id', $target_user_id)
            ->where('started_at >=', $start_date)
            ->where('started_at <=', $end_date . ' 23:59:59')
            ->group_by('DATE(started_at)')
            ->order_by('DATE(started_at)', 'ASC')
            ->get()->result();
        $daily_breakdown = array_map(function ($r) use ($daily_hours) {
            $logged = round((int)$r->sec / 3600, 2);
            return [
                'date' => $r->date,
                'logged_hours' => $logged,
                'required_hours' => $daily_hours,
                'diff' => round($logged - $daily_hours, 2),
            ];
        }, $daily_rows);

        return $this->_respond(200, true, 'OK', [
            'user_id' => $target_user_id,
            'from' => $start_date,
            'to' => $end_date,
            'working_days' => $working_days,
            'leave_days' => $leave_days,
            'adjusted_working_days' => $adjusted_wd,
            'required_hours' => $required_hours,
            'logged_hours' => $logged_hours,
            'shortage_hours' => $diff < 0 ? abs($diff) : 0,
            'surplus_hours' => $diff > 0 ? $diff : 0,
            'daily_breakdown' => $daily_breakdown,
        ]);
    }

    public function detailed_overview()
    {
        $auth_user = $this->api_auth->authenticate();
        $target_user_id = $this->input->get('user_id') ? (int)$this->input->get('user_id') : null;
        $start_date = $this->input->get('start_date');
        $end_date_input = $this->input->get('end_date');
        if (!$start_date || !$end_date_input) {
            $end_date_input = date('Y-m-d');
            $start_date = date('Y-m-d', strtotime('-6 days'));
        }
        $since = $start_date;
        $until = $end_date_input . ' 23:59:59';
        $period_days = (strtotime($end_date_input) - strtotime($start_date)) / 86400 + 1;
        $prev_end = date('Y-m-d', strtotime($start_date . ' -1 day'));
        $prev_since = date('Y-m-d', strtotime($prev_end . ' -' . ($period_days - 1) . ' days'));
        // Chart daily_logged always spans 7 days ending on end_date, regardless of period.
        // KPIs and other summaries continue to use the original $since range.
        $chart_since = date('Y-m-d', strtotime($end_date_input . ' -6 days'));

        $is_admin = $this->api_auth->is_super_admin();
        $is_manager = (int)$auth_user->role_id === 3;

        $company_ids = null;

        // Resolve target
        if ($target_user_id === null) {
            if (!$is_admin && !$is_manager) {
                return $this->_respond(403, false, 'Access denied');
            }
            $company_ids = $this->api_auth->get_authorized_user_ids(null);
            $effective_user_id = null;
        } else {
            if (!$is_admin && !$is_manager && (int)$auth_user->user_id !== $target_user_id) {
                return $this->_respond(403, false, 'Access denied');
            }
            $resolved = $this->api_auth->get_authorized_user_ids($target_user_id);
            $effective_user_id = $resolved ? $resolved[0] : null;
            if ($effective_user_id === null) {
                return $this->_respond(404, false, 'User not found');
            }
        }

        // ---- Profile ----
        if ($target_user_id === null) {
            $profile = ['name' => 'Organization Overview', 'role' => 'org', 'avatar' => ''];
        } else {
            $u = $this->db
                ->select('u.username, u.role_id, ad.fullname, ad.avatar')
                ->from('tbl_users u')
                ->join('tbl_account_details ad', 'ad.user_id = u.user_id', 'left')
                ->where('u.user_id', $effective_user_id)
                ->get()->row();
            $role_map = [1 => 'admin', 2 => 'employee', 3 => 'manager'];
            $profile = [
                'name' => $u->fullname ?? $u->username ?? 'Unknown',
                'role' => $role_map[(int)$u->role_id] ?? 'employee',
                'avatar' => base_url(!empty($u->avatar) ? $u->avatar : 'assets/img/user/default_avatar.jpg'),
            ];
        }

        // ---- Helper scoping closures ----
        $time_q = function ($sel, $since_date, $join_billable = false) use ($effective_user_id, $company_ids, $until) {
            $this->db->select($sel)->from('tbl_desktop_time_entries te');
            if ($join_billable) {
                $this->db->join('tbl_task t', 't.task_id = te.task_id', 'left');
                $this->db->where('t.billable', 'Yes');
            }
            if ($effective_user_id !== null) {
                $this->db->where('te.user_id', $effective_user_id);
            } elseif ($company_ids !== null) {
                $this->db->where_in('te.user_id', $company_ids);
            }
            $this->db->where('te.type', 'work');
            $this->db->where('te.started_at >=', $since_date);
            $this->db->where('te.started_at <=', $until);
            return $this->db;
        };

        $app_q = function ($limit = null) use ($effective_user_id, $since, $company_ids, $until) {
            $this->db->select('au.app_name, au.window_title, au.url, au.total_seconds, au.recorded_at')
                ->from('tbl_desktop_app_usage au');
            if ($effective_user_id !== null) {
                $this->db->where('au.user_id', $effective_user_id);
            } elseif ($company_ids !== null) {
                $this->db->where_in('au.user_id', $company_ids);
            }
            $this->db->where('au.recorded_at >=', $since);
            $this->db->where('au.recorded_at <=', $until);
            $this->db->order_by('au.recorded_at', 'DESC');
            if ($limit) $this->db->limit($limit);
            return $this->db;
        };

        // ---- KPI ----
        $current_total = (int)$time_q("COALESCE(SUM(te.total_seconds), 0) as val", $since)->get()->row()->val;
        $billable_total = (int)$time_q("COALESCE(SUM(te.total_seconds), 0) as val", $since, true)->get()->row()->val;
        $prev_total = (int)$time_q("COALESCE(SUM(te.total_seconds), 0) as val", $prev_since)
            ->where('te.started_at <=', $prev_end . ' 23:59:59')->get()->row()->val;
        $trend = $prev_total > 0 ? round((($current_total - $prev_total) / $prev_total) * 100, 2) : 0;

        // ---- Daily logged (always 7-day chart span) ----
        $daily_rows = $time_q("DATE(te.started_at) as date, SUM(te.total_seconds) as secs", $chart_since)
            ->group_by('DATE(te.started_at)')->order_by('DATE(te.started_at)', 'ASC')->get()->result();
        $daily_logged = array_map(function ($r) {
            return ['date' => $r->date, 'hours' => round((int)$r->secs / 3600, 1)];
        }, $daily_rows);

        // ---- Desktop activity (app_usage classification) ----
        $app_rows = $app_q(null)->get()->result();
        $total_app_seconds = 0;
        $productive_seconds = 0;
        $neutral_seconds = 0;
        $daily_breakdown_map = [];

        foreach ($app_rows as $r) {
            $secs = (int)$r->total_seconds;
            $total_app_seconds += $secs;
            $cat = $this->_categorize_app($r->app_name);
            if ($cat === 'productive') $productive_seconds += $secs;
            elseif ($cat === 'neutral') $neutral_seconds += $secs;
            $day = $r->recorded_at;
            if (!isset($daily_breakdown_map[$day])) {
                $daily_breakdown_map[$day] = ['date' => $day, 'productive' => 0, 'unproductive' => 0];
            }
            if ($cat === 'productive') $daily_breakdown_map[$day]['productive'] += $secs;
            elseif ($cat === 'neutral') $daily_breakdown_map[$day]['productive'] += $secs * 0.5;
            else $daily_breakdown_map[$day]['unproductive'] += $secs;
        }

        $unproductive_seconds = $total_app_seconds - $productive_seconds - $neutral_seconds;
        $effective_productive = $productive_seconds + ($neutral_seconds * 0.5);
        $activity_score = $total_app_seconds > 0 ? round(($effective_productive / $total_app_seconds) * 100, 1) : 0;

        $daily_activity_breakdown = [];
        foreach ($daily_breakdown_map as $day => $d) {
            $daily_activity_breakdown[] = [
                'date' => $d['date'],
                'productive' => round($d['productive'] / 3600, 1),
                'unproductive' => round($d['unproductive'] / 3600, 1),
            ];
        }
        usort($daily_activity_breakdown, function ($a, $b) { return strcmp($a['date'], $b['date']); });

        // ---- Top apps ----
        $app_agg = [];
        foreach ($app_rows as $r) {
            $name = $r->app_name;
            $secs = (int)$r->total_seconds;
            if (!isset($app_agg[$name])) $app_agg[$name] = 0;
            $app_agg[$name] += $secs;
        }
        arsort($app_agg);
        $top_apps = [];
        foreach (array_slice($app_agg, 0, 10) as $name => $secs) {
            $top_apps[] = [
                'name' => $name,
                'percentage' => $total_app_seconds > 0 ? round(($secs / $total_app_seconds) * 100, 1) : 0,
                'color' => $this->_app_color($name),
            ];
        }

        // ---- Recent screenshots ----
        $screenshot_q = $this->db
            ->select('id, file_path, captured_at, keystroke_count, mouse_click_count, activity_percentage')
            ->from('tbl_screenshots')
            ->where('captured_at >=', $since)
            ->where('captured_at <=', $until);
        if ($effective_user_id !== null) {
            $screenshot_q->where('user_id', $effective_user_id);
        } elseif ($company_ids !== null) {
            $screenshot_q->where_in('user_id', $company_ids);
        }
        $screenshot_rows = $screenshot_q->order_by('captured_at', 'DESC')->limit(200)->get()->result();
        $recent_screenshots = array_map(function ($s) {
            return [
                'id' => (int)$s->id,
                'file_url' => base_url($s->file_path),
                'captured_at' => $s->captured_at,
                'keystroke_count' => (int)$s->keystroke_count,
                'mouse_click_count' => (int)$s->mouse_click_count,
                'activity_percentage' => (float)$s->activity_percentage,
            ];
        }, $screenshot_rows);

        // ---- Recent windows ----
        $window_q = $this->db
            ->select('au.app_name, au.window_title, au.url, SUM(au.total_seconds) as total_seconds, MAX(au.recorded_at) as recorded_at')
            ->from('tbl_desktop_app_usage au');
        if ($effective_user_id !== null) {
            $window_q->where('au.user_id', $effective_user_id);
        } elseif ($company_ids !== null) {
            $window_q->where_in('au.user_id', $company_ids);
        }
        $window_rows = $window_q
            ->where('au.recorded_at >=', $since)
            ->where('au.recorded_at <=', $until)
            ->group_by('au.app_name, au.window_title, au.url')
            ->order_by('MAX(au.recorded_at)', 'DESC')
            ->limit(200)
            ->get()->result();
        $recent_windows = array_map(function ($w) {
            return [
                'app_name' => $w->app_name,
                'window_title' => $w->window_title,
                'url' => $w->url ?? null,
                'total_seconds' => (int)$w->total_seconds,
                'recorded_at' => $w->recorded_at,
            ];
        }, $window_rows);

        return $this->_respond(200, true, 'OK', [
            'profile' => $profile,
            'kpi' => [
                'time_logged' => round($current_total / 3600, 1),
                'time_billable' => round($billable_total / 3600, 1),
                'trend_percent' => $trend,
            ],
            'daily_logged' => $daily_logged,
            'desktop_activity' => [
                'total_time' => round($total_app_seconds / 3600, 1),
                'productive_time' => round(($productive_seconds + $neutral_seconds) / 3600, 1),
                'unproductive_time' => round($unproductive_seconds / 3600, 1),
                'activity_score' => $activity_score,
            ],
            'daily_activity_breakdown' => $daily_activity_breakdown,
            'top_apps' => $top_apps,
            'recent_screenshots' => $recent_screenshots,
            'recent_windows' => $recent_windows,
        ]);
    }



    private function _count_working_days($from, $to)
    {
        $holidays = $this->db
            ->where('start_date <=', $to)
            ->where('end_date >=', $from)
            ->get('tbl_holiday')
            ->result();
        $holiday_map = [];
        foreach ($holidays as $h) {
            $d = new DateTime(max($h->start_date, $from));
            $end_d = new DateTime(min($h->end_date, $to));
            while ($d <= $end_d) {
                $holiday_map[$d->format('Y-m-d')] = true;
                $d->modify('+1 day');
            }
        }

        $start = new DateTime($from);
        $end = new DateTime($to);
        $count = 0;
        while ($start <= $end) {
            $dow = (int)$start->format('N');
            $date_str = $start->format('Y-m-d');
            if ($dow !== 5 && !isset($holiday_map[$date_str])) {
                $count++;
            }
            $start->modify('+1 day');
        }
        return $count;
    }

    private function _count_user_leave_days($user_id, $from, $to)
    {
        $leaves = $this->db
            ->where('user_id', $user_id)
            ->where('application_status', 2)
            ->where('leave_start_date <=', $to)
            ->group_start()
            ->where('leave_end_date >=', $from)
            ->or_where('leave_end_date IS NULL')
            ->group_end()
            ->get('tbl_leave_application')
            ->result();
        $leave_map = [];
        foreach ($leaves as $lv) {
            $d = new DateTime(max($lv->leave_start_date, $from));
            $end_date = $lv->leave_end_date ?? $lv->leave_start_date;
            $end_d = new DateTime(min($end_date, $to));
            while ($d <= $end_d) {
                if ((int)$d->format('N') !== 5) {
                    $leave_map[$d->format('Y-m-d')] = true;
                }
                $d->modify('+1 day');
            }
        }
        return count($leave_map);
    }

    private function _respond($status_code, $success, $message, $data = null)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}

