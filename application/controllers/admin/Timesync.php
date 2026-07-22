<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Timesync extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('tasks_model');
        $this->load->model('user_model');

        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }
    }

    public function index()
    {
        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        $data['title'] = 'TimeSync Dashboard';

        $from = $this->input->get('from');
        $to = $this->input->get('to');
        if (empty($from)) $from = date('Y-m-d');
        if (empty($to)) $to = date('Y-m-d');
        $to_end = $to . ' 23:59:59';
        $data['from'] = $from;
        $data['to'] = $to;

        $interval = $this->input->get('interval') ?: 'daily';
        $data['interval'] = $interval;

        $selected_user_id = $this->input->get('user_id');
        $data['selected_user_id'] = $selected_user_id;

        $allowed_ids = get_authorized_user_ids_web();

        $data['users'] = $this->_get_user_list_with_stats($from, $to, $allowed_ids);

        $working_days = $this->_count_working_days($from, $to);
        $data['working_days'] = $working_days;

        $data['holidays'] = $this->db
            ->where('start_date <=', $to)
            ->where('end_date >=', $from)
            ->order_by('start_date', 'ASC')
            ->get('tbl_holiday')
            ->result();

        if (!empty($selected_user_id)) {
            $data['user_leaves'] = $this->db
                ->where('user_id', $selected_user_id)
                ->where('application_status', 2)
                ->where('leave_start_date <=', $to)
                ->where('leave_end_date >=', $from)
                ->order_by('leave_start_date', 'ASC')
                ->get('tbl_leave_application')
                ->result();
        } else {
            $data['user_leaves'] = [];
        }

        if (!empty($selected_user_id)) {
            $found_user = null;
            foreach ($data['users'] as $u) {
                if ((int)$u->user_id === (int)$selected_user_id) {
                    $found_user = $u;
                    break;
                }
            }
            if ($found_user) {
                $data['total_logged_seconds'] = $found_user->total_sec;
                $data['total_activity_seconds'] = $found_user->activity_sec;
                $data['productive_ratio'] = $found_user->productive_pct;
                $leave_days = $this->_count_user_leave_days($found_user->user_id, $from, $to);
                $adjusted_wd = max(0, $working_days - $leave_days);
                $user_req_sec = $found_user->required_daily * $adjusted_wd * 3600;
                $data['required_seconds'] = $user_req_sec;
                $data['required_daily_avg'] = $found_user->required_daily;
                $data['discrepancy_seconds'] = $found_user->total_sec - $user_req_sec;
                $data['selected_user'] = $found_user;
                $data['adjusted_working_days'] = $adjusted_wd;
            } else {
                $data['total_logged_seconds'] = 0;
                $data['total_activity_seconds'] = 0;
                $data['productive_ratio'] = 0;
                $data['required_seconds'] = 0;
                $data['required_daily_avg'] = 8;
                $data['discrepancy_seconds'] = 0;
            }
        } else {
            $metrics = $this->_aggregate_metrics($from, $to, $allowed_ids);
            $data['total_logged_seconds'] = $metrics['logged_sec'];
            $data['total_activity_seconds'] = $metrics['activity_sec'];
            $data['productive_ratio'] = $metrics['productive_pct'];

            $required = $this->_get_aggregated_required_hours($from, $to, $allowed_ids);
            $data['required_seconds'] = $required['required_sec'];
            $data['required_daily_avg'] = $required['daily_avg'];

            $data['discrepancy_seconds'] = $metrics['logged_sec'] - $required['required_sec'];
        }

        $data['subview'] = $this->load->view('admin/timesync/dashboard', $data, true);
        $this->_render_or_ajax($data);
    }

    public function live_users()
    {
        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                $this->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'Access denied']));
                return;
            }
        }

        $allowed_ids = get_authorized_user_ids_web();

        $this->db->select('u.user_id, u.online_time, u.last_active_ping, a.fullname, a.avatar')
            ->from('tbl_users u')
            ->join('tbl_account_details a', 'a.user_id = u.user_id', 'left')
            ->where('u.activated', 1)
            ->where('u.banned', 0)
            ->where_in('u.role_id', [1, 3]);
        if ($allowed_ids !== null) {
            $this->db->where_in('u.user_id', $allowed_ids);
        }
        $all_users = $this->db->get()->result();

        $running_rows = $this->db
            ->select('tde.user_id, tde.paused_at, tde.started_at, t.task_name as task_title')
            ->from('tbl_desktop_time_entries tde')
            ->join('tbl_task t', 't.task_id = tde.task_id', 'left')
            ->where('tde.is_running', 1)
            ->where('tde.stopped_at IS NULL', null, false);
        if ($allowed_ids !== null) {
            $this->db->where_in('tde.user_id', $allowed_ids);
        }
        $running_rows = $running_rows->get()->result();

        $active_map = [];
        $paused_ids = [];
        $running_ids = [];
        foreach ($running_rows as $e) {
            $uid = (int)$e->user_id;
            $active_map[$uid] = [
                'task_title' => $e->task_title,
                'started_at' => $e->started_at ? (strtotime($e->started_at . ' UTC') * 1000) : null,
            ];
            $running_ids[] = $uid;
            if (!empty($e->paused_at)) {
                $paused_ids[] = $uid;
            }
        }

        $now_ts = time();
        $active_count = 0;
        $paused_count = 0;
        $idle_count = 0;
        $offline_count = 0;
        $list = [];

        foreach ($all_users as $u) {
            $uid = (int)$u->user_id;
            $is_running = in_array($uid, $running_ids);
            $is_paused = in_array($uid, $paused_ids);
            $online_ok = !empty($u->online_time) && (int)$u->online_time > ($now_ts - 300);

            if ($is_running) {
                $status = $is_paused ? 'paused' : 'active';
                if ($is_paused) $paused_count++;
                else $active_count++;
            } elseif ($online_ok) {
                $status = 'idle';
                $idle_count++;
            } else {
                $status = 'offline';
                $offline_count++;
            }

            $window = null;
            if ($status === 'active') {
                $w = $this->db
                    ->select('app_name, window_title')
                    ->from('tbl_desktop_app_usage')
                    ->where('user_id', $uid)
                    ->order_by('created_at', 'DESC')
                    ->limit(1)
                    ->get()->row();
                if ($w) {
                    $window = !empty($w->window_title) ? $w->window_title : $w->app_name;
                }
            }

            $profile_img = null;
            if (!empty($u->avatar) && file_exists(FCPATH . $u->avatar)) {
                $profile_img = base_url() . $u->avatar;
            }

            $list[] = [
                'user_id' => $uid,
                'name' => $u->fullname ?? ('User #' . $uid),
                'profile_image_url' => $profile_img,
                'status' => $status,
                'current_task' => $is_running ? ($active_map[$uid]['task_title'] ?? null) : null,
                'current_window' => $window,
                'started_at' => $is_running ? ($active_map[$uid]['started_at'] ?? null) : null,
                'is_active_now' => !empty($u->last_active_ping) && strtotime($u->last_active_ping) >= ($now_ts - 120),
                'last_active_ping' => $u->last_active_ping,
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'summary' => [
                    'total' => count($all_users),
                    'active' => $active_count,
                    'paused' => $paused_count,
                    'idle' => $idle_count,
                    'offline' => $offline_count,
                ],
                'users' => $list,
                'server_time' => date('Y-m-d H:i:s'),
            ]));
    }

    public function entries()
    {
        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        $data['title'] = 'Time Entries';

        $allowed_ids = get_authorized_user_ids_web();
        $this->db->select('tbl_desktop_time_entries.*, tbl_account_details.fullname, tbl_task.task_name');
        $this->db->from('tbl_desktop_time_entries');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left');
        $this->db->join('tbl_task', 'tbl_task.task_id = tbl_desktop_time_entries.task_id', 'left');
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_desktop_time_entries.user_id', $allowed_ids);
        }
        $this->db->order_by('tbl_desktop_time_entries.started_at', 'DESC');
        $this->db->limit(100);
        $data['entries'] = $this->db->get()->result();

        $data['subview'] = $this->load->view('admin/timesync/entries', $data, true);
        $this->_render_or_ajax($data);
    }

    public function calendar()
    {
        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        $data['title'] = 'TimeSync Calendar';

        $year = (int)($this->input->get('year') ?: date('Y'));
        $month = (int)($this->input->get('month') ?: date('m'));
        $data['year'] = $year;
        $data['month'] = $month;

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        $allowed_ids = get_authorized_user_ids_web();
        $data['entries'] = $this->db
            ->select('tbl_desktop_time_entries.*, tbl_account_details.fullname')
            ->from('tbl_desktop_time_entries')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left')
            ->where('tbl_desktop_time_entries.started_at >=', $start . ' 00:00:00')
            ->where('tbl_desktop_time_entries.started_at <=', $end . ' 23:59:59');
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_desktop_time_entries.user_id', $allowed_ids);
        }
        $this->db->order_by('tbl_desktop_time_entries.started_at', 'DESC')
            ->get()
            ->result();

        $data['daily_totals'] = [];
        foreach ($data['entries'] as $e) {
            $day = date('Y-m-d', strtotime($e->started_at));
            if (!isset($data['daily_totals'][$day])) {
                $data['daily_totals'][$day] = 0;
            }
            $data['daily_totals'][$day] += (int)$e->total_seconds;
        }

        $data['subview'] = $this->load->view('admin/timesync/calendar', $data, true);
        $this->_render_or_ajax($data);
    }

    public function day_details($date = null)
    {
        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $data['title'] = 'Details for ' . $date;
        $data['selected_date'] = $date;

        $allowed_ids = get_authorized_user_ids_web();
        $data['entries'] = $this->db
            ->select('tbl_desktop_time_entries.*, tbl_account_details.fullname, tbl_task.task_name')
            ->from('tbl_desktop_time_entries')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left')
            ->join('tbl_task', 'tbl_task.task_id = tbl_desktop_time_entries.task_id', 'left')
            ->where('tbl_desktop_time_entries.started_at >=', $date . ' 00:00:00')
            ->where('tbl_desktop_time_entries.started_at <=', $date . ' 23:59:59');
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_desktop_time_entries.user_id', $allowed_ids);
        }
        $this->db->order_by('tbl_desktop_time_entries.started_at', 'ASC')
            ->get()
            ->result();

        $data['total_seconds'] = 0;
        foreach ($data['entries'] as $e) {
            $data['total_seconds'] += (int)$e->total_seconds;
        }

        $data['subview'] = $this->load->view('admin/timesync/day_details', $data, true);
        $this->_render_or_ajax($data);
    }

    public function entries_datatable()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        if ($length <= 0) $length = 25;

        $columns = ['tbl_desktop_time_entries.id', 'fullname', 'task_name', 'started_at', 'stopped_at', 'total_seconds', 'is_running'];
        $order_col = (int)$this->input->post('order[0][column]');
        $order_dir = $this->input->post('order[0][dir]') === 'asc' ? 'ASC' : 'DESC';
        $order_by = $columns[$order_col] ?? 'tbl_desktop_time_entries.id';

        $search_val = $this->input->post('search[value]');

        $allowed_ids = get_authorized_user_ids_web();

        $this->db->select('tbl_desktop_time_entries.*, tbl_account_details.fullname, tbl_task.task_name');
        $this->db->from('tbl_desktop_time_entries');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left');
        $this->db->join('tbl_task', 'tbl_task.task_id = tbl_desktop_time_entries.task_id', 'left');

        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_desktop_time_entries.user_id', $allowed_ids);
        }

        if (!empty($search_val)) {
            $this->db->group_start();
            $this->db->like('tbl_account_details.fullname', $search_val);
            $this->db->or_like('tbl_task.task_name', $search_val);
            $this->db->or_like('tbl_desktop_time_entries.description', $search_val);
            $this->db->group_end();
        }

        $total_filtered = $this->db->count_all_results(null, false);

        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);
        $data = $this->db->get()->result();

        $this->db->reset_query();
        $total_all = $this->db->from('tbl_desktop_time_entries');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $total_all = $this->db->count_all_results();

        $rows = [];
        foreach ($data as $row) {
            $rows[] = [
                (string)$row->id,
                htmlspecialchars($row->fullname ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($row->task_name ?? 'N/A', ENT_QUOTES, 'UTF-8'),
                $row->started_at,
                $row->stopped_at ?? 'Running',
                (int)$row->total_seconds,
                $row->is_running ? 'Yes' : 'No',
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'draw' => $draw,
                'recordsTotal' => $total_all,
                'recordsFiltered' => $total_filtered,
                'data' => $rows,
            ]));
    }

    public function user($user_id = null)
    {
        if (!is_super_admin() && !can_action_by_label('timesync', 'view')) {
            redirect('404');
        }

        if (empty($user_id)) {
            redirect('admin/timesync');
        }

        $data['title'] = 'User Report';
        $data['user'] = $this->db
            ->select('tbl_users.*, tbl_account_details.fullname, tbl_account_details.avatar')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->where('tbl_users.user_id', $user_id)
            ->get('tbl_users')
            ->row();

        // Fetch teams this user belongs to for badge display
        $data['user_teams'] = $this->db
            ->select('t.name')
            ->from('tbl_teams t')
            ->join('tbl_team_members tm', 'tm.team_id = t.id')
            ->where('tm.user_id', $user_id)
            ->where('tm.status', 'approved')
            ->order_by('t.name', 'ASC')
            ->get()
            ->result();

        if (empty($data['user'])) {
            redirect('admin/timesync');
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');
        if (empty($from)) $from = date('Y-m-01');
        if (empty($to)) $to = date('Y-m-d');
        $to_end = $to . ' 23:59:59';

        $data['from'] = $from;
        $data['to'] = $to;
        $data['user_id'] = $user_id;

        $interval = $this->input->get('interval') ?: 'daily';
        $data['interval'] = $interval;

        $tab = $this->input->get('tab');
        $allowed_tabs = ['entries', 'screenshots', 'apps'];
        if (empty($tab) || !in_array($tab, $allowed_tabs)) {
            $tab = 'entries';
        }
        $data['active_tab'] = $tab;

        // Stat cards (computed for all tabs)
        $stats = $this->_user_stats($user_id, $from, $to);
        $data['total_seconds'] = $stats['total_seconds'];
        $data['entry_count'] = $stats['entry_count'];
        $data['screenshot_count'] = $stats['screenshot_count'];
        $data['day_count'] = $stats['day_count'];

        // Daily hours trend chart
        $daily = $this->_user_daily_hours($user_id, $from, $to);
        $data['chart_user_hours_labels'] = json_encode($daily['labels']);
        $data['chart_user_hours_values'] = json_encode($daily['values']);

        // Lazy-load tab data
        $entry_page = max(1, (int)$this->input->get('entry_page'));
        $screenshot_page = max(1, (int)$this->input->get('ss_page'));
        switch ($tab) {
            case 'entries':
                $total_entries = $this->db
                    ->where('user_id', $user_id)
                    ->where('started_at >=', $from . ' 00:00:00')
                    ->where('started_at <=', $to . ' 23:59:59')
                    ->count_all_results('tbl_desktop_time_entries');
                $entry_per_page = 25;
                $entry_total_pages = max(1, ceil($total_entries / $entry_per_page));
                $entry_page = min($entry_page, $entry_total_pages);
                $entry_offset = ($entry_page - 1) * $entry_per_page;
                $data['entries'] = $this->_user_entries($user_id, $from, $to, $entry_per_page, $entry_offset);
                $data['entry_page'] = $entry_page;
                $data['entry_total_pages'] = $entry_total_pages;
                break;
            case 'screenshots':
                $total_ss = $stats['screenshot_count'];
                $ss_per_page = 24;
                $ss_total_pages = max(1, ceil($total_ss / $ss_per_page));
                $screenshot_page = min($screenshot_page, $ss_total_pages);
                $ss_offset = ($screenshot_page - 1) * $ss_per_page;
                $data['screenshots'] = $this->_user_screenshots($user_id, $from, $to, $ss_per_page, $ss_offset);
                $data['ss_page'] = $screenshot_page;
                $data['ss_total_pages'] = $ss_total_pages;
                break;
            case 'apps':
                $total_apps = $this->db
                    ->where('user_id', $user_id)
                    ->where('recorded_at >=', $from)
                    ->where('recorded_at <=', $to_end)
                    ->count_all_results('tbl_desktop_app_usage');
                $app_per_page = 25;
                $app_page = max(1, (int)$this->input->get('app_page'));
                $app_total_pages = max(1, ceil($total_apps / $app_per_page));
                $app_page = min($app_page, $app_total_pages);
                $app_offset = ($app_page - 1) * $app_per_page;
                $data['app_usage'] = $this->_user_app_usage($user_id, $from, $to, $app_per_page, $app_offset);
                $data['app_page'] = $app_page;
                $data['app_total_pages'] = $app_total_pages;
                break;
        }

        $data['subview'] = $this->load->view('admin/timesync/user_report', $data, true);
        $this->_render_or_ajax($data);
    }

    public function screenshots()
    {
        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        $data['title'] = 'Screenshots';

        $user_id = $this->input->get('user_id');
        $task_id = $this->input->get('task_id');
        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $allowed_ids = get_authorized_user_ids_web();

        $page = max(1, (int)$this->input->get('page'));
        $per_page = 24;
        $offset = ($page - 1) * $per_page;

        // Count total matching
        $this->db->from('tbl_screenshots');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        if (!empty($user_id)) $this->db->where('user_id', (int)$user_id);
        if (!empty($task_id)) $this->db->where('task_id', (int)$task_id);
        if (!empty($from)) $this->db->where('captured_at >=', $from);
        if (!empty($to)) $this->db->where('captured_at <=', $to . ' 23:59:59');
        $total = $this->db->count_all_results();

        $total_pages = max(1, ceil($total / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        // Fetch page
        $this->db->select('tbl_screenshots.*, tbl_account_details.fullname, tbl_task.task_name');
        $this->db->from('tbl_screenshots');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_screenshots.user_id', 'left');
        $this->db->join('tbl_task', 'tbl_task.task_id = tbl_screenshots.task_id', 'left');
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_screenshots.user_id', $allowed_ids);
        }
        if (!empty($user_id)) $this->db->where('tbl_screenshots.user_id', (int)$user_id);
        if (!empty($task_id)) $this->db->where('tbl_screenshots.task_id', (int)$task_id);
        if (!empty($from)) $this->db->where('tbl_screenshots.captured_at >=', $from);
        if (!empty($to)) $this->db->where('tbl_screenshots.captured_at <=', $to . ' 23:59:59');
        $this->db->order_by('tbl_screenshots.captured_at', 'DESC');
        $this->db->limit($per_page, $offset);
        $data['screenshots'] = $this->db->get()->result();

        $data['screenshot_count'] = count($data['screenshots']);
        $this->db->reset_query();
        $data['total_screenshots'] = $this->db->from('tbl_screenshots');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $data['total_screenshots'] = $this->db->count_all_results();
        $data['page'] = $page;
        $data['total_pages'] = $total_pages;
        $data['per_page'] = $per_page;

        // Activity trend: screenshots per day for last 14 days
        $trend = $this->db
            ->select('DATE(captured_at) as day, COUNT(*) as cnt')
            ->where('captured_at >=', date('Y-m-d', strtotime('-13 days')) . ' 00:00:00');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $trend = $this->db->group_by('DATE(captured_at)')
            ->order_by('day', 'ASC')
            ->get('tbl_screenshots')
            ->result();
        $trend_labels = [];
        $trend_values = [];
        for ($d = 13; $d >= 0; $d--) {
            $day = date('Y-m-d', strtotime("-$d days"));
            $trend_labels[] = $day;
            $found = 0;
            foreach ($trend as $t) {
                if ($t->day === $day) { $found = (int)$t->cnt; break; }
            }
            $trend_values[] = $found;
        }
        $data['chart_trend_labels'] = json_encode($trend_labels);
        $data['chart_trend_values'] = json_encode($trend_values);

        $data['users'] = $this->db->select('tbl_users.user_id, tbl_account_details.fullname')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->where('tbl_users.activated', 1);
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_users.user_id', $allowed_ids);
        }
        $data['users'] = $this->db->get('tbl_users')
            ->result();

        $data['subview'] = $this->load->view('admin/timesync/screenshots', $data, true);
        $this->_render_or_ajax($data);
    }

    public function usage()
    {
        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        $data['title'] = 'App Usage Reports';

        $user_id = $this->input->get('user_id');
        $from = $this->input->get('from');
        $to = $this->input->get('to');
        if (empty($from)) $from = date('Y-m-01');
        if (empty($to)) $to = date('Y-m-d');
        $to_end = $to . ' 23:59:59';

        // Distinct active users in period
        $allowed_ids = get_authorized_user_ids_web();

        $this->db->reset_query();
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $data['usage_user_count'] = (int)$this->db
            ->select('COUNT(DISTINCT(user_id)) as cnt')
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to_end)
            ->get('tbl_desktop_app_usage')
            ->row()->cnt;

        // Total seconds in period
        $this->db->reset_query();
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $data['usage_total_seconds'] = (int)$this->db
            ->select('COALESCE(SUM(total_seconds), 0) as total')
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to_end)
            ->get('tbl_desktop_app_usage')
            ->row()->total;

        // Per-user totals and focus scores
        $this->db->reset_query();
        $user_totals_raw = $this->db
            ->select('user_id, SUM(total_seconds) as total_sec')
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to_end);
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $user_totals_raw = $this->db->group_by('user_id')
            ->get('tbl_desktop_app_usage')
            ->result();

        $user_scores = [];
        foreach ($user_totals_raw as $ut) {
            $uid = (int)$ut->user_id;
            $total = (int)$ut->total_sec;
            $this->db->reset_query();
            $top_app = $this->db
                ->select('SUM(total_seconds) as sec')
                ->where('user_id', $uid)
                ->where('recorded_at >=', $from)
                ->where('recorded_at <=', $to_end);
            if ($allowed_ids !== null) {
                $this->db->where_in('user_id', $allowed_ids);
            }
            $top_app = $this->db->group_by('app_name')
                ->order_by('sec', 'DESC')
                ->limit(1)
                ->get('tbl_desktop_app_usage')
                ->row();
            $top_sec = (int)($top_app->sec ?? 0);
            $focus_score = $total > 0 ? round(($top_sec / $total) * 100) : 0;
            $user_scores[$uid] = ['total_seconds' => $total, 'focus_score' => $focus_score];
        }
        $data['user_scores'] = $user_scores;

        // Focus distribution for chart
        $buckets = [0, 0, 0, 0];
        foreach ($user_scores as $s) {
            $f = $s['focus_score'];
            if ($f < 25) $buckets[0]++;
            elseif ($f < 50) $buckets[1]++;
            elseif ($f < 75) $buckets[2]++;
            else $buckets[3]++;
        }
        $data['chart_focus_labels'] = json_encode(['0-24%', '25-49%', '50-74%', '75-100%']);
        $data['chart_focus_values'] = json_encode($buckets);

        // Top apps for chart
        $this->db->reset_query();
        $top_apps = $this->db
            ->select('app_name, SUM(total_seconds) as total_sec')
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to_end);
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $top_apps = $this->db->group_by('app_name')
            ->order_by('total_sec', 'DESC')
            ->limit(10)
            ->get('tbl_desktop_app_usage')
            ->result();

        $data['chart_app_labels'] = json_encode(array_map(function($a) { return $a->app_name; }, $top_apps));
        $data['chart_app_values'] = json_encode(array_map(function($a) { return round($a->total_sec / 3600, 1); }, $top_apps));

        $this->db->reset_query();
        $data['users'] = $this->db
            ->select('tbl_users.user_id, tbl_account_details.fullname')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->where('tbl_users.activated', 1);
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_users.user_id', $allowed_ids);
        }
        $data['users'] = $this->db->get('tbl_users')
            ->result();

        $data['selected_user_id'] = $user_id;

        $data['subview'] = $this->load->view('admin/timesync/usage_report', $data, true);
        $this->_render_or_ajax($data);
    }

    public function usage_datatable()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        if ($length <= 0) $length = 25;

        $user_id = $this->input->get('user_id');
        $from = $this->input->get('from');
        $to = $this->input->get('to');
        if (empty($from)) $from = date('Y-m-01');
        if (empty($to)) $to = date('Y-m-d');
        $to_end = $to . ' 23:59:59';

        $columns = ['a.recorded_at', 'ad.fullname', 'a.app_name', 'a.window_title', 'a.url', 'a.total_seconds'];
        $order_col = (int)$this->input->post('order[0][column]');
        $order_dir = $this->input->post('order[0][dir]') === 'asc' ? 'ASC' : 'DESC';
        $order_by = $columns[$order_col] ?? 'a.recorded_at';

        $search_val = $this->input->post('search[value]');

        $allowed_ids = get_authorized_user_ids_web();

        $this->db->select('a.*, u.username, ad.fullname, a.url');
        $this->db->from('tbl_desktop_app_usage a');
        $this->db->join('tbl_users u', 'u.user_id = a.user_id', 'left');
        $this->db->join('tbl_account_details ad', 'ad.user_id = a.user_id', 'left');

        if ($allowed_ids !== null) {
            $this->db->where_in('a.user_id', $allowed_ids);
        }
        if (!empty($user_id)) $this->db->where('a.user_id', (int)$user_id);
        $this->db->where('a.recorded_at >=', $from);
        $this->db->where('a.recorded_at <=', $to_end);

        if (!empty($search_val)) {
            $this->db->group_start();
            $this->db->like('ad.fullname', $search_val);
            $this->db->or_like('a.app_name', $search_val);
            $this->db->or_like('a.window_title', $search_val);
            $this->db->group_end();
        }

        $total_filtered = $this->db->count_all_results(null, false);

        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);
        $data = $this->db->get()->result();

        // Count total matching filters (without search)
        $this->db->reset_query();
        $this->db->select('a.id');
        $this->db->from('tbl_desktop_app_usage a');
        $this->db->join('tbl_users u', 'u.user_id = a.user_id', 'left');
        $this->db->join('tbl_account_details ad', 'ad.user_id = a.user_id', 'left');
        if ($allowed_ids !== null) {
            $this->db->where_in('a.user_id', $allowed_ids);
        }
        if (!empty($user_id)) $this->db->where('a.user_id', (int)$user_id);
        $this->db->where('a.recorded_at >=', $from);
        $this->db->where('a.recorded_at <=', $to_end);
        $total_all = (int)$this->db->count_all_results();

        $rows = [];
        foreach ($data as $row) {
            $tz_utc = new DateTimeZone('UTC');
            $tz_local = new DateTimeZone(date_default_timezone_get());
            $dt = new DateTime($row->recorded_at, $tz_utc);
            $dt->setTimezone($tz_local);
            $rows[] = [
                $dt->format('Y-m-d h:i:s A'),
                htmlspecialchars($row->fullname ?? $row->username ?? 'User #' . $row->user_id, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($row->app_name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(mb_substr($row->window_title ?? '—', 0, 60), ENT_QUOTES, 'UTF-8'),
                !empty($row->url) ? htmlspecialchars(mb_substr($row->url, 0, 80), ENT_QUOTES, 'UTF-8') : '—',
                gmdate('H:i:s', (int)$row->total_seconds),
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'draw' => $draw,
                'recordsTotal' => $total_all,
                'recordsFiltered' => $total_filtered,
                'data' => $rows,
            ]));
    }

    public function view_image($id)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $screenshot = $this->db->where('id', $id)->get('tbl_screenshots')->row();
        if (empty($screenshot)) {
            $this->_output_transparent_pixel();
            return;
        }

        $file_path = FCPATH . $screenshot->file_path;
        if (!file_exists($file_path)) {
            log_message('error', 'Screenshot file missing: ' . $file_path . ' (DB id: ' . $id . ')');
            $this->_output_transparent_pixel();
            return;
        }

        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_types = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        $content_type = $mime_types[$ext] ?? 'image/png';

        header('Content-Type: ' . $content_type);
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: public, max-age=86400');
        readfile($file_path);
        exit;
    }

    public function get_screenshot_details($screenshot_id)
    {
        if (!is_super_admin()) {
            $can_view = can_action_by_label('timesync', 'view');
            if (!$can_view) {
                $this->output->set_status_header(403)->set_output(json_encode(['error' => 'Access denied']));
                return;
            }
        }

        $screenshot = $this->db->where('id', $screenshot_id)->get('tbl_screenshots')->row();
        if (empty($screenshot)) {
            $this->output->set_status_header(404)->set_output(json_encode(['error' => 'Screenshot not found']));
            return;
        }

        $app_usage = $this->db
            ->select('app_name, window_title, total_seconds')
            ->where('screenshot_id', $screenshot_id)
            ->order_by('total_seconds', 'DESC')
            ->get('tbl_desktop_app_usage')
            ->result();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => [
                    'id' => (int)$screenshot->id,
                    'file_url' => base_url('admin/timesync/view_image/' . $screenshot->id),
                    'captured_at' => $screenshot->captured_at,
                    'keystroke_count' => (int)$screenshot->keystroke_count,
                    'mouse_click_count' => (int)$screenshot->mouse_click_count,
                    'activity_percentage' => (float)$screenshot->activity_percentage,
                    'app_usage' => $app_usage,
                ]
            ]));
    }

    private function _output_transparent_pixel()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: image/png');
        header('Content-Length: 68');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
        exit;
    }

    private function _save_config($key, $value)
    {
        $this->db->where('config_key', $key)->update('tbl_config', ['value' => $value]);
        $exists = $this->db->where('config_key', $key)->get('tbl_config');
        if ($exists->num_rows() == 0) {
            $this->db->insert('tbl_config', ['config_key' => $key, 'value' => $value]);
        }
    }

    private function _dashboard_kpis($start_date, $end_date)
    {
        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $month_start = date('Y-m-01');

        $allowed_ids = get_authorized_user_ids_web();

        $active_users = $this->db->select('COUNT(DISTINCT user_id) as count')
            ->where('started_at >=', $today)
            ->where('is_running', 1);
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $active_users = (int) $active_users->get('tbl_desktop_time_entries')->row()->count ?? 0;

        $this->db->reset_query();
        $total_entries = $this->db->from('tbl_desktop_time_entries');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $total_entries = $this->db->count_all_results();

        $this->db->reset_query();
        $total_screenshots = $this->db->from('tbl_screenshots');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $total_screenshots = $this->db->count_all_results();

        return [
            'today_hours' => $this->_total_hours_since($today, $allowed_ids),
            'week_hours' => $this->_total_hours_since($week_start, $allowed_ids),
            'month_hours' => $this->_total_hours_since($month_start, $allowed_ids),
            'active_users' => $active_users,
            'total_entries' => $total_entries,
            'total_screenshots' => $total_screenshots,
            'period_hours' => $this->_total_hours_since($start_date, $allowed_ids),
        ];
    }

    private function _daily_hours_chart($start_date, $end_date)
    {
        $allowed_ids = get_authorized_user_ids_web();
        $result = $this->db
            ->select('DATE(started_at) as day, COALESCE(SUM(total_seconds), 0) as total_sec')
            ->where('started_at >=', $start_date . ' 00:00:00')
            ->where('started_at <=', $end_date . ' 23:59:59');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $result = $this->db->group_by('DATE(started_at)')
            ->order_by('day', 'ASC')
            ->get('tbl_desktop_time_entries')
            ->result();

        $labels = [];
        $values = [];
        foreach ($result as $row) {
            $labels[] = $row->day;
            $values[] = round($row->total_sec / 3600, 1);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function _user_distribution($start_date, $end_date)
    {
        $allowed_ids = get_authorized_user_ids_web();
        $this->db->select('tbl_desktop_time_entries.user_id, tbl_account_details.fullname, COALESCE(SUM(tbl_desktop_time_entries.total_seconds), 0) as total_sec, COUNT(DISTINCT tbl_desktop_time_entries.id) as entry_count')
            ->from('tbl_desktop_time_entries')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left')
            ->where('tbl_desktop_time_entries.started_at >=', $start_date . ' 00:00:00')
            ->where('tbl_desktop_time_entries.started_at <=', $end_date . ' 23:59:59');
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_desktop_time_entries.user_id', $allowed_ids);
        }
        return $this->db->group_by('tbl_desktop_time_entries.user_id')
            ->order_by('total_sec', 'DESC')
            ->limit(10)
            ->get()
            ->result();
    }

    private function _user_grid($start_date, $end_date)
    {
        $allowed_ids = get_authorized_user_ids_web();
        $users = $this->db
            ->select('tbl_users.user_id, tbl_users.last_active_ping, tbl_account_details.fullname, tbl_account_details.avatar')
            ->from('tbl_users')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->where('tbl_users.activated', 1);
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_users.user_id', $allowed_ids);
        }
        $users = $this->db->order_by('tbl_account_details.fullname', 'ASC')
            ->get()
            ->result();

        foreach ($users as &$u) {
            $stats = $this->db
                ->select('COALESCE(SUM(total_seconds), 0) as total_sec, COUNT(DISTINCT id) as entry_count, MAX(started_at) as last_active')
                ->where('user_id', $u->user_id)
                ->where('started_at >=', $start_date . ' 00:00:00')
                ->where('started_at <=', $end_date . ' 23:59:59')
                ->get('tbl_desktop_time_entries')
                ->row();
            $u->total_sec = (int) $stats->total_sec;
            $u->entry_count = (int) $stats->entry_count;
            $u->last_active = $stats->last_active;
            $u->screenshot_count = (int) $this->db
                ->where('user_id', $u->user_id)
                ->where('captured_at >=', $start_date . ' 00:00:00')
                ->where('captured_at <=', $end_date . ' 23:59:59')
                ->count_all_results('tbl_screenshots');
        }

        usort($users, function ($a, $b) {
            return $b->total_sec <=> $a->total_sec;
        });

        return $users;
    }

    public function settings()
    {
        if (!is_super_admin() && !can_action_by_label('timesync', 'edited')) {
            redirect('404');
        }

        $data['title'] = 'TimeSync Settings';

        if ($this->input->post()) {
            $demo_mode = $this->input->post('demo_mode') == '1' ? '1' : '0';
            $this->_save_config('timesync_demo_mode', $demo_mode);

            $retention_days = (int)$this->input->post('screenshot_retention_days');
            $this->_save_config('screenshot_retention_days', (string)$retention_days);

            $default_daily = $this->input->post('default_daily_hours');
            if ($default_daily !== null) {
                $this->_save_config('timesync_default_daily_hours', (string)max(0, (float)$default_daily));
                $this->db->insert('tbl_timesync_config_log', [
                    'config_key' => 'timesync_default_daily_hours',
                    'value' => (string)max(0, (float)$default_daily),
                    'changed_by' => $this->session->userdata('user_id'),
                ]);
            }

            $default_monthly = $this->input->post('default_monthly_hours');
            if ($default_monthly !== null) {
                $this->_save_config('timesync_default_monthly_hours', (string)max(0, (float)$default_monthly));
                $this->db->insert('tbl_timesync_config_log', [
                    'config_key' => 'timesync_default_monthly_hours',
                    'value' => (string)max(0, (float)$default_monthly),
                    'changed_by' => $this->session->userdata('user_id'),
                ]);
            }

            $daily_reqs = $this->input->post('required_daily');
            $monthly_reqs = $this->input->post('required_monthly');
            if (!empty($daily_reqs) && is_array($daily_reqs)) {
                foreach ($daily_reqs as $uid => $daily) {
                    $daily_val = max(0, (float)$daily);
                    $monthly_val = isset($monthly_reqs[$uid]) ? max(0, (float)$monthly_reqs[$uid]) : 204;
                    $this->_upsert_user_setting((int)$uid, $daily_val, $monthly_val);
                }
            }

            set_message('success', 'Settings updated');
            redirect('admin/timesync/settings');
        }

        $data['demo_mode'] = config_item('timesync_demo_mode');
        $data['screenshot_retention_days'] = config_item('screenshot_retention_days') ?: '90';

        $config_log = $this->db
            ->query("SELECT l1.* FROM tbl_timesync_config_log l1
                INNER JOIN (
                    SELECT config_key, MAX(changed_at) as max_changed
                    FROM tbl_timesync_config_log
                    GROUP BY config_key
                ) l2 ON l1.config_key = l2.config_key AND l1.changed_at = l2.max_changed")
            ->result();
        $config_map = [];
        foreach ($config_log as $c) {
            $config_map[$c->config_key] = $c->value;
        }

        $default_daily = $config_map['timesync_default_daily_hours'] ?? config_item('timesync_default_daily_hours') ?: '8.00';
        $default_monthly = $config_map['timesync_default_monthly_hours'] ?? config_item('timesync_default_monthly_hours') ?: '204.00';
        $data['default_daily_hours'] = $default_daily;
        $data['default_monthly_hours'] = $default_monthly;

        $data['all_users'] = $this->db
            ->select('tbl_users.user_id, tbl_account_details.fullname')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->where('tbl_users.activated', 1)
            ->order_by('tbl_account_details.fullname', 'ASC')
            ->get('tbl_users')
            ->result();

        $settings_log = $this->db
            ->query("SELECT l1.* FROM tbl_timesync_user_settings_log l1
                INNER JOIN (
                    SELECT user_id, MAX(changed_at) as max_changed
                    FROM tbl_timesync_user_settings_log
                    GROUP BY user_id
                ) l2 ON l1.user_id = l2.user_id AND l1.changed_at = l2.max_changed")
            ->result();
        $settings_map = [];
        foreach ($settings_log as $s) {
            $settings_map[(int)$s->user_id] = $s;
        }

        foreach ($data['all_users'] as $u) {
            $u->required_daily_hours = isset($settings_map[(int)$u->user_id]) ? $settings_map[(int)$u->user_id]->required_daily_hours : null;
            $u->required_monthly_hours = isset($settings_map[(int)$u->user_id]) ? $settings_map[(int)$u->user_id]->required_monthly_hours : null;
        }

        $data['subview'] = $this->load->view('admin/timesync/settings', $data, true);
        $this->_render_or_ajax($data);
    }

    private function _render_or_ajax($data)
    {
        if ($this->input->get('ajax')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'html'  => $data['subview'],
                    'title' => $data['title'] ?? '',
                ]));
            return;
        }
        $this->load->view('admin/_layout_main', $data);
    }

    private function _user_stats($user_id, $from, $to)
    {
        $result = $this->db
            ->select('COALESCE(SUM(total_seconds), 0) as total_sec, COUNT(DISTINCT id) as entries, COUNT(DISTINCT DATE(started_at)) as days')
            ->where('user_id', $user_id)
            ->where('started_at >=', $from . ' 00:00:00')
            ->where('started_at <=', $to . ' 23:59:59')
            ->get('tbl_desktop_time_entries')
            ->row();

        $screenshot_count = $this->db
            ->where('user_id', $user_id)
            ->where('captured_at >=', $from . ' 00:00:00')
            ->where('captured_at <=', $to . ' 23:59:59')
            ->count_all_results('tbl_screenshots');

        return [
            'total_seconds' => (int)$result->total_sec,
            'entry_count' => (int)$result->entries,
            'day_count' => (int)$result->days,
            'screenshot_count' => $screenshot_count,
        ];
    }

    private function _user_daily_hours($user_id, $from, $to)
    {
        $result = $this->db
            ->select('DATE(started_at) as day, COALESCE(SUM(total_seconds), 0) as total_sec')
            ->where('user_id', $user_id)
            ->where('started_at >=', $from . ' 00:00:00')
            ->where('started_at <=', $to . ' 23:59:59')
            ->group_by('DATE(started_at)')
            ->order_by('day', 'ASC')
            ->get('tbl_desktop_time_entries')
            ->result();
        $labels = [];
        $values = [];
        $data_map = [];
        foreach ($result as $r) {
            $data_map[$r->day] = round($r->total_sec / 3600, 1);
        }
        $d = new DateTime($from);
        $end = new DateTime($to);
        while ($d <= $end) {
            $day = $d->format('Y-m-d');
            $labels[] = $day;
            $values[] = $data_map[$day] ?? 0;
            $d->modify('+1 day');
        }
        return ['labels' => $labels, 'values' => $values];
    }

    private function _user_entries($user_id, $from, $to, $limit = null, $offset = null)
    {
        $this->db->select('tbl_desktop_time_entries.*, tbl_task.task_name');
        $this->db->from('tbl_desktop_time_entries');
        $this->db->join('tbl_task', 'tbl_task.task_id = tbl_desktop_time_entries.task_id', 'left');
        $this->db->where('tbl_desktop_time_entries.user_id', $user_id);
        $this->db->where('tbl_desktop_time_entries.started_at >=', $from . ' 00:00:00');
        $this->db->where('tbl_desktop_time_entries.started_at <=', $to . ' 23:59:59');
        $this->db->order_by('tbl_desktop_time_entries.started_at', 'DESC');
        if ($limit !== null) $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    private function _user_screenshots($user_id, $from, $to, $limit = null, $offset = null)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('captured_at >=', $from . ' 00:00:00');
        $this->db->where('captured_at <=', $to . ' 23:59:59');
        $this->db->order_by('captured_at', 'DESC');
        if ($limit !== null) $this->db->limit($limit, $offset);
        return $this->db->get('tbl_screenshots')->result();
    }

    private function _user_app_usage($user_id, $from, $to, $limit = null, $offset = null)
    {
        $to_end = !empty($to) ? $to . ' 23:59:59' : $to;
        $this->db
            ->select('app_name, window_title, url, total_seconds, recorded_at')
            ->where('user_id', $user_id)
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to_end)
            ->order_by('recorded_at', 'DESC')
            ->order_by('total_seconds', 'DESC');
        if ($limit !== null) $this->db->limit($limit, $offset);
        return $this->db->get('tbl_desktop_app_usage')->result();
    }

    private function _categorize_app($name)
    {
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

    private function _get_user_list_with_stats($from, $to, $allowed_ids = null)
    {
        $to_end = $to . ' 23:59:59';
        $users = $this->db
            ->select('tbl_users.user_id, tbl_account_details.fullname, tbl_account_details.avatar')
            ->from('tbl_users')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->where('tbl_users.activated', 1)
            ->where_in('tbl_users.role_id', [1, 3]);
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_users.user_id', $allowed_ids);
        }
        $users = $this->db->order_by('tbl_account_details.fullname', 'ASC')
            ->get()
            ->result();

        $entry_totals = $this->db
            ->select('user_id, COALESCE(SUM(total_seconds), 0) as total_sec')
            ->where('started_at >=', $from . ' 00:00:00')
            ->where('started_at <=', $to . ' 23:59:59');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $entry_totals = $this->db->group_by('user_id')
            ->get('tbl_desktop_time_entries')
            ->result();
        $entry_map = [];
        foreach ($entry_totals as $et) {
            $entry_map[(int)$et->user_id] = (int)$et->total_sec;
        }

        $app_totals = $this->db
            ->select('user_id, app_name, COALESCE(SUM(total_seconds), 0) as total_sec')
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to_end);
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $app_totals = $this->db->group_by(['user_id', 'app_name'])
            ->get('tbl_desktop_app_usage')
            ->result();

        $app_map = [];
        $prod_map = [];
        foreach ($app_totals as $at) {
            $uid = (int)$at->user_id;
            $sec = (int)$at->total_sec;
            if (!isset($app_map[$uid])) $app_map[$uid] = 0;
            $app_map[$uid] += $sec;
            $cat = $this->_categorize_app($at->app_name);
            if ($cat === 'productive') {
                if (!isset($prod_map[$uid])) $prod_map[$uid] = 0;
                $prod_map[$uid] += $sec;
            } elseif ($cat === 'neutral') {
                if (!isset($prod_map[$uid])) $prod_map[$uid] = 0;
                $prod_map[$uid] += $sec * 0.5;
            }
        }

        $as_of = $to . ' 23:59:59';
        $settings_log = $this->db
            ->query("SELECT l1.* FROM tbl_timesync_user_settings_log l1
                INNER JOIN (
                    SELECT user_id, MAX(changed_at) as max_changed
                    FROM tbl_timesync_user_settings_log
                    WHERE changed_at <= ?
                    GROUP BY user_id
                ) l2 ON l1.user_id = l2.user_id AND l1.changed_at = l2.max_changed", [$as_of])
            ->result();
        $settings_map = [];
        foreach ($settings_log as $s) {
            $settings_map[(int)$s->user_id] = $s;
        }

        $config_log = $this->db
            ->query("SELECT l1.* FROM tbl_timesync_config_log l1
                INNER JOIN (
                    SELECT config_key, MAX(changed_at) as max_changed
                    FROM tbl_timesync_config_log
                    WHERE changed_at <= ?
                    GROUP BY config_key
                ) l2 ON l1.config_key = l2.config_key AND l1.changed_at = l2.max_changed", [$as_of])
            ->result();
        $config_map = [];
        foreach ($config_log as $c) {
            $config_map[$c->config_key] = $c->value;
        }

        $default_daily = (float)(isset($config_map['timesync_default_daily_hours']) ? $config_map['timesync_default_daily_hours'] : (config_item('timesync_default_daily_hours') ?: 8.0));
        $default_monthly = (float)(isset($config_map['timesync_default_monthly_hours']) ? $config_map['timesync_default_monthly_hours'] : (config_item('timesync_default_monthly_hours') ?: 204.0));

        $working_days = $this->_count_working_days($from, $to);

        foreach ($users as &$u) {
            $uid = (int)$u->user_id;
            $u->total_sec = $entry_map[$uid] ?? 0;
            $u->activity_sec = $app_map[$uid] ?? 0;
            $prod_sec = $prod_map[$uid] ?? 0;
            $act = $u->activity_sec;
            $u->productive_pct = $act > 0 ? round(($prod_sec / $act) * 100, 1) : 0;
            $req = $settings_map[$uid] ?? null;
            $u->required_daily = $req ? (float)$req->required_daily_hours : $default_daily;
            $u->required_monthly = $req ? (float)$req->required_monthly_hours : $default_monthly;
            $leave_days = $this->_count_user_leave_days($uid, $from, $to);
            $u->leave_days = $leave_days;
            $adjusted_wd = max(0, $working_days - $leave_days);
            $required_in_range = $u->required_daily * $adjusted_wd;
            $logged_hours = $u->total_sec / 3600;
            $u->has_shortage = $logged_hours < $required_in_range;
            $u->shortage_hours = max(0, $required_in_range - $logged_hours);
        }

        return $users;
    }

    private function _aggregate_metrics($from, $to, $allowed_ids = null)
    {
        $to_end = $to . ' 23:59:59';
        $logged = $this->db
            ->select('COALESCE(SUM(total_seconds), 0) as total')
            ->where('started_at >=', $from . ' 00:00:00')
            ->where('started_at <=', $to . ' 23:59:59');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $logged = (int)$logged->get('tbl_desktop_time_entries')->row()->total;

        $app_usage = $this->db
            ->select('app_name, COALESCE(SUM(total_seconds), 0) as total_sec')
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to_end);
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $app_usage = $this->db->group_by('app_name')
            ->get('tbl_desktop_app_usage')->result();

        $activity_sec = 0;
        $productive_sec = 0;
        foreach ($app_usage as $au) {
            $sec = (int)$au->total_sec;
            $activity_sec += $sec;
            $cat = $this->_categorize_app($au->app_name);
            if ($cat === 'productive') {
                $productive_sec += $sec;
            } elseif ($cat === 'neutral') {
                $productive_sec += $sec * 0.5;
            }
        }

        $productive_pct = $activity_sec > 0 ? round(($productive_sec / $activity_sec) * 100, 1) : 0;

        return [
            'logged_sec' => $logged,
            'activity_sec' => $activity_sec,
            'productive_pct' => $productive_pct,
        ];
    }

    private function _get_aggregated_required_hours($from, $to, $allowed_ids = null)
    {
        $users = $this->db
            ->select('tbl_users.user_id')
            ->from('tbl_users')
            ->where('tbl_users.activated', 1);
        if ($allowed_ids !== null) {
            $this->db->where_in('tbl_users.user_id', $allowed_ids);
        }
        $users = $this->db->get()->result();

        $as_of = $to . ' 23:59:59';
        $settings_log = $this->db
            ->query("SELECT l1.* FROM tbl_timesync_user_settings_log l1
                INNER JOIN (
                    SELECT user_id, MAX(changed_at) as max_changed
                    FROM tbl_timesync_user_settings_log
                    WHERE changed_at <= ?
                    GROUP BY user_id
                ) l2 ON l1.user_id = l2.user_id AND l1.changed_at = l2.max_changed", [$as_of])
            ->result();
        $settings_map = [];
        foreach ($settings_log as $s) {
            $settings_map[(int)$s->user_id] = $s;
        }

        $config_log = $this->db
            ->query("SELECT l1.* FROM tbl_timesync_config_log l1
                INNER JOIN (
                    SELECT config_key, MAX(changed_at) as max_changed
                    FROM tbl_timesync_config_log
                    WHERE changed_at <= ?
                    GROUP BY config_key
                ) l2 ON l1.config_key = l2.config_key AND l1.changed_at = l2.max_changed", [$as_of])
            ->result();
        $config_map = [];
        foreach ($config_log as $c) {
            $config_map[$c->config_key] = $c->value;
        }
        $default_daily = (float)(isset($config_map['timesync_default_daily_hours']) ? $config_map['timesync_default_daily_hours'] : (config_item('timesync_default_daily_hours') ?: 8.0));

        $working_days = $this->_count_working_days($from, $to);
        $total_required_sec = 0;
        $total_daily_sum = 0;
        $user_count = count($users);

        foreach ($users as $u) {
            $uid = (int)$u->user_id;
            $req = $settings_map[$uid] ?? null;
            $daily = $req ? (float)$req->required_daily_hours : $default_daily;
            $leave_days = $this->_count_user_leave_days($uid, $from, $to);
            $adjusted_wd = max(0, $working_days - $leave_days);
            $total_required_sec += $daily * $adjusted_wd * 3600;
            $total_daily_sum += $daily;
        }

        $daily_avg = $user_count > 0 ? round($total_daily_sum / $user_count, 2) : 8.0;

        return [
            'required_sec' => $total_required_sec,
            'daily_avg' => $daily_avg,
        ];
    }

    private function _upsert_user_setting($user_id, $daily, $monthly)
    {
        $this->db->insert('tbl_timesync_user_settings_log', [
            'user_id' => $user_id,
            'required_daily_hours' => $daily,
            'required_monthly_hours' => $monthly,
            'changed_by' => $this->session->userdata('user_id'),
        ]);
    }

    private function _total_hours_since($since_date, $allowed_ids = null)
    {
        $this->db->select('COALESCE(SUM(total_seconds), 0) as total')
            ->where('started_at >=', $since_date . ' 00:00:00');
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $result = $this->db->get('tbl_desktop_time_entries')->row();
        $seconds = (int)$result->total;
        return round($seconds / 3600, 1);
    }

    public function batch_thumbnails()
    {
        $ids = $this->input->get('ids');
        if (empty($ids)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'ids parameter required']));
            return;
        }

        $id_array = array_map('intval', explode(',', $ids));
        $id_array = array_unique(array_filter($id_array, fn($v) => $v > 0));
        if (empty($id_array)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'No valid IDs']));
            return;
        }

        $placeholders = implode(',', array_fill(0, count($id_array), '?'));
        $screenshots = $this->db->query("SELECT id, file_path FROM tbl_screenshots WHERE id IN ($placeholders)", $id_array)->result();

        $result = [];
        foreach ($screenshots as $s) {
            $file_path = FCPATH . $s->file_path;
            if (file_exists($file_path)) {
                $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : ($ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/webp');
                $data = base64_encode(file_get_contents($file_path));
                $result[$s->id] = 'data:' . $mime . ';base64,' . $data;
            }
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true, 'data' => $result]));
    }
}

