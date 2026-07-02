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
        if (empty($from)) $from = date('Y-m-d', strtotime('-7 days'));
        if (empty($to)) $to = date('Y-m-d');

        $data['from'] = $from;
        $data['to'] = $to;

        $kpis = $this->_dashboard_kpis($from, $to);
        foreach ($kpis as $key => $val) {
            $data[$key] = $val;
        }

        $chart = $this->_daily_hours_chart($from, $to);
        $data['daily_chart_labels'] = json_encode($chart['labels']);
        $data['daily_chart_values'] = json_encode($chart['values']);

        $dist = $this->_user_distribution($from, $to);
        $data['user_distribution'] = json_encode($dist);
        $data['user_distribution_raw'] = $dist;

        $data['user_grid'] = $this->_user_grid($from, $to);
        $data['top_users'] = $this->_user_distribution($from, $to);

        $data['subview'] = $this->load->view('admin/timesync/dashboard', $data, true);
        $this->load->view('admin/_layout_main', $data);
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

        $this->db->select('tbl_desktop_time_entries.*, tbl_account_details.fullname, tbl_task.task_name');
        $this->db->from('tbl_desktop_time_entries');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left');
        $this->db->join('tbl_task', 'tbl_task.task_id = tbl_desktop_time_entries.task_id', 'left');
        $this->db->order_by('tbl_desktop_time_entries.started_at', 'DESC');
        $this->db->limit(100);
        $data['entries'] = $this->db->get()->result();

        $data['subview'] = $this->load->view('admin/timesync/entries', $data, true);
        $this->load->view('admin/_layout_main', $data);
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

        $data['entries'] = $this->db
            ->select('tbl_desktop_time_entries.*, tbl_account_details.fullname')
            ->from('tbl_desktop_time_entries')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left')
            ->where('tbl_desktop_time_entries.started_at >=', $start . ' 00:00:00')
            ->where('tbl_desktop_time_entries.started_at <=', $end . ' 23:59:59')
            ->order_by('tbl_desktop_time_entries.started_at', 'DESC')
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
        $this->load->view('admin/_layout_main', $data);
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

        $data['entries'] = $this->db
            ->select('tbl_desktop_time_entries.*, tbl_account_details.fullname, tbl_task.task_name')
            ->from('tbl_desktop_time_entries')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left')
            ->join('tbl_task', 'tbl_task.task_id = tbl_desktop_time_entries.task_id', 'left')
            ->where('tbl_desktop_time_entries.started_at >=', $date . ' 00:00:00')
            ->where('tbl_desktop_time_entries.started_at <=', $date . ' 23:59:59')
            ->order_by('tbl_desktop_time_entries.started_at', 'ASC')
            ->get()
            ->result();

        $data['total_seconds'] = 0;
        foreach ($data['entries'] as $e) {
            $data['total_seconds'] += (int)$e->total_seconds;
        }

        $data['subview'] = $this->load->view('admin/timesync/day_details', $data, true);
        $this->load->view('admin/_layout_main', $data);
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

        $this->db->select('tbl_desktop_time_entries.*, tbl_account_details.fullname, tbl_task.task_name');
        $this->db->from('tbl_desktop_time_entries');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left');
        $this->db->join('tbl_task', 'tbl_task.task_id = tbl_desktop_time_entries.task_id', 'left');

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

        $total_all = $this->db->count_all('tbl_desktop_time_entries');

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

        if (empty($data['user'])) {
            redirect('admin/timesync');
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');
        if (empty($from)) $from = date('Y-m-01');
        if (empty($to)) $to = date('Y-m-d');

        $data['from'] = $from;
        $data['to'] = $to;
        $data['user_id'] = $user_id;

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
                $data['app_usage'] = $this->_user_app_usage($user_id, $from, $to);
                break;
        }

        $data['subview'] = $this->load->view('admin/timesync/user_report', $data, true);
        $this->load->view('admin/_layout_main', $data);
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

        $page = max(1, (int)$this->input->get('page'));
        $per_page = 24;
        $offset = ($page - 1) * $per_page;

        // Count total matching
        $this->db->from('tbl_screenshots');
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
        if (!empty($user_id)) $this->db->where('tbl_screenshots.user_id', (int)$user_id);
        if (!empty($task_id)) $this->db->where('tbl_screenshots.task_id', (int)$task_id);
        if (!empty($from)) $this->db->where('tbl_screenshots.captured_at >=', $from);
        if (!empty($to)) $this->db->where('tbl_screenshots.captured_at <=', $to . ' 23:59:59');
        $this->db->order_by('tbl_screenshots.captured_at', 'DESC');
        $this->db->limit($per_page, $offset);
        $data['screenshots'] = $this->db->get()->result();

        $data['screenshot_count'] = count($data['screenshots']);
        $data['total_screenshots'] = $this->db->count_all('tbl_screenshots');
        $data['page'] = $page;
        $data['total_pages'] = $total_pages;
        $data['per_page'] = $per_page;

        // Activity trend: screenshots per day for last 14 days
        $trend = $this->db
            ->select('DATE(captured_at) as day, COUNT(*) as cnt')
            ->where('captured_at >=', date('Y-m-d', strtotime('-13 days')) . ' 00:00:00')
            ->group_by('DATE(captured_at)')
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
            ->where('tbl_users.activated', 1)
            ->get('tbl_users')
            ->result();

        $data['subview'] = $this->load->view('admin/timesync/screenshots', $data, true);
        $this->load->view('admin/_layout_main', $data);
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

        // Distinct active users in period
        $this->db->reset_query();
        $data['usage_user_count'] = (int)$this->db
            ->select('COUNT(DISTINCT(user_id)) as cnt')
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to)
            ->get('tbl_desktop_app_usage')
            ->row()->cnt;

        // Total seconds in period
        $this->db->reset_query();
        $data['usage_total_seconds'] = (int)$this->db
            ->select('COALESCE(SUM(total_seconds), 0) as total')
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to)
            ->get('tbl_desktop_app_usage')
            ->row()->total;

        // Per-user totals and focus scores
        $this->db->reset_query();
        $user_totals_raw = $this->db
            ->select('user_id, SUM(total_seconds) as total_sec')
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to)
            ->group_by('user_id')
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
                ->where('recorded_at <=', $to)
                ->group_by('app_name')
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
            ->where('recorded_at <=', $to)
            ->group_by('app_name')
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
            ->where('tbl_users.activated', 1)
            ->get('tbl_users')
            ->result();

        $data['selected_user_id'] = $user_id;

        $data['subview'] = $this->load->view('admin/timesync/usage_report', $data, true);
        $this->load->view('admin/_layout_main', $data);
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

        $columns = ['a.recorded_at', 'ad.fullname', 'a.app_name', 'a.window_title', 'a.total_seconds'];
        $order_col = (int)$this->input->post('order[0][column]');
        $order_dir = $this->input->post('order[0][dir]') === 'asc' ? 'ASC' : 'DESC';
        $order_by = $columns[$order_col] ?? 'a.recorded_at';

        $search_val = $this->input->post('search[value]');

        $this->db->reset_query();
        $this->db->select('a.*, u.username, ad.fullname');
        $this->db->from('tbl_desktop_app_usage a');
        $this->db->join('tbl_users u', 'u.user_id = a.user_id', 'left');
        $this->db->join('tbl_account_details ad', 'ad.user_id = a.user_id', 'left');

        if (!empty($user_id)) $this->db->where('a.user_id', (int)$user_id);
        $this->db->where('a.recorded_at >=', $from);
        $this->db->where('a.recorded_at <=', $to);

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

        $this->db->reset_query();
        $total_all = $this->db->count_all('tbl_desktop_app_usage');

        $rows = [];
        foreach ($data as $row) {
            $rows[] = [
                htmlspecialchars($row->recorded_at, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($row->fullname ?? $row->username ?? 'User #' . $row->user_id, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($row->app_name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(mb_substr($row->window_title ?? '—', 0, 60), ENT_QUOTES, 'UTF-8'),
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

        $captured_at = $screenshot->captured_at;
        $user_id = $screenshot->user_id;

        // Find the time entry session that was active when this screenshot was captured
        $this->db->where('user_id', $user_id);
        $this->db->where('started_at <=', $captured_at);
        $this->db->group_start();
        $this->db->where('stopped_at IS NULL');
        $this->db->or_where('stopped_at >=', $captured_at);
        $this->db->group_end();
        $this->db->order_by('started_at', 'DESC');
        $this->db->limit(1);
        $time_entry = $this->db->get('tbl_desktop_time_entries')->row();

        // Fetch app usage for that session
        $app_usage = [];
        if ($time_entry) {
            $app_usage = $this->db
                ->select('app_name, window_title, total_seconds')
                ->where('time_entry_id', $time_entry->id)
                ->order_by('total_seconds', 'DESC')
                ->limit(20)
                ->get('tbl_desktop_app_usage')
                ->result();
        }

        // Fallback to daily app usage if no session-linked data
        if (empty($app_usage)) {
            $recorded_date = date('Y-m-d', strtotime($captured_at));
            $app_usage = $this->db
                ->select('app_name, window_title, total_seconds')
                ->where('user_id', $user_id)
                ->where('recorded_at', $recorded_date)
                ->order_by('total_seconds', 'DESC')
                ->limit(20)
                ->get('tbl_desktop_app_usage')
                ->result();
        }

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

        return [
            'today_hours' => $this->_total_hours_since($today),
            'week_hours' => $this->_total_hours_since($week_start),
            'month_hours' => $this->_total_hours_since($month_start),
            'active_users' => (int) $this->db->select('COUNT(DISTINCT user_id) as count')
                ->where('started_at >=', $today)
                ->where('is_running', 1)
                ->get('tbl_desktop_time_entries')
                ->row()->count ?? 0,
            'total_entries' => $this->db->count_all('tbl_desktop_time_entries'),
            'total_screenshots' => $this->db->count_all('tbl_screenshots'),
            'period_hours' => $this->_total_hours_since($start_date),
        ];
    }

    private function _daily_hours_chart($start_date, $end_date)
    {
        $result = $this->db
            ->select('DATE(started_at) as day, COALESCE(SUM(total_seconds), 0) as total_sec')
            ->where('started_at >=', $start_date . ' 00:00:00')
            ->where('started_at <=', $end_date . ' 23:59:59')
            ->group_by('DATE(started_at)')
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
        return $this->db
            ->select('tbl_desktop_time_entries.user_id, tbl_account_details.fullname, COALESCE(SUM(tbl_desktop_time_entries.total_seconds), 0) as total_sec, COUNT(DISTINCT tbl_desktop_time_entries.id) as entry_count')
            ->from('tbl_desktop_time_entries')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left')
            ->where('tbl_desktop_time_entries.started_at >=', $start_date . ' 00:00:00')
            ->where('tbl_desktop_time_entries.started_at <=', $end_date . ' 23:59:59')
            ->group_by('tbl_desktop_time_entries.user_id')
            ->order_by('total_sec', 'DESC')
            ->limit(10)
            ->get()
            ->result();
    }

    private function _user_grid($start_date, $end_date)
    {
        $users = $this->db
            ->select('tbl_users.user_id, tbl_users.last_active_ping, tbl_account_details.fullname, tbl_account_details.avatar')
            ->from('tbl_users')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->where('tbl_users.activated', 1)
            ->order_by('tbl_account_details.fullname', 'ASC')
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

            set_message('success', 'Settings updated');
            redirect('admin/timesync/settings');
        }

        $data['demo_mode'] = config_item('timesync_demo_mode');
        $data['screenshot_retention_days'] = config_item('screenshot_retention_days') ?: '90';

        $data['subview'] = $this->load->view('admin/timesync/settings', $data, true);
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

    private function _user_app_usage($user_id, $from, $to)
    {
        return $this->db
            ->select('app_name, SUM(total_seconds) as total_sec, COUNT(*) as occurrences')
            ->where('user_id', $user_id)
            ->where('recorded_at >=', $from)
            ->where('recorded_at <=', $to)
            ->group_by('app_name')
            ->order_by('total_sec', 'DESC')
            ->get('tbl_desktop_app_usage')
            ->result();
    }

    private function _total_hours_since($since_date)
    {
        $result = $this->db
            ->select('COALESCE(SUM(total_seconds), 0) as total')
            ->where('started_at >=', $since_date . ' 00:00:00')
            ->get('tbl_desktop_time_entries')
            ->row();
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

