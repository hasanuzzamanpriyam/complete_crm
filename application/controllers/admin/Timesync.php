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
            $can_view = can_action('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }
    }

    public function index()
    {
        if (!is_super_admin()) {
            $can_view = can_action('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        $data['title'] = 'TimeSync Dashboard';

        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $month_start = date('Y-m-01');

        $data['today_hours'] = $this->_total_hours_since($today);
        $data['week_hours'] = $this->_total_hours_since($week_start);
        $data['month_hours'] = $this->_total_hours_since($month_start);

        $data['active_users'] = $this->db
            ->select('COUNT(DISTINCT user_id) as count')
            ->where('started_at >=', $today)
            ->where('is_running', 1)
            ->get('tbl_desktop_time_entries')
            ->row()->count ?? 0;

        $data['total_screenshots'] = $this->db->count_all('tbl_screenshots');
        $data['total_entries'] = $this->db->count_all('tbl_desktop_time_entries');

        $this->db->select('tbl_desktop_time_entries.user_id, tbl_account_details.fullname, SUM(tbl_desktop_time_entries.total_seconds) as total_sec');
        $this->db->from('tbl_desktop_time_entries');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_desktop_time_entries.user_id', 'left');
        $this->db->where('tbl_desktop_time_entries.started_at >=', $today);
        $this->db->group_by('tbl_desktop_time_entries.user_id');
        $this->db->order_by('total_sec', 'DESC');
        $this->db->limit(10);
        $data['top_users'] = $this->db->get()->result();

        $data['subview'] = $this->load->view('admin/timesync/dashboard', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function user($user_id = null)
    {
        if (!is_super_admin()) {
            redirect('404');
        }

        if (empty($user_id)) {
            redirect('admin/timesync');
        }

        $data['title'] = 'User Report';
        $data['user'] = $this->db
            ->select('tbl_users.*, tbl_account_details.fullname')
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

        $this->db->where('user_id', $user_id);
        $this->db->where('started_at >=', $from . ' 00:00:00');
        $this->db->where('started_at <=', $to . ' 23:59:59');
        $this->db->order_by('started_at', 'DESC');
        $data['entries'] = $this->db->get('tbl_desktop_time_entries')->result();

        $data['total_seconds'] = 0;
        foreach ($data['entries'] as $e) {
            $data['total_seconds'] += $e->total_seconds;
        }

        $this->db->where('user_id', $user_id);
        $this->db->where('captured_at >=', $from . ' 00:00:00');
        $this->db->where('captured_at <=', $to . ' 23:59:59');
        $this->db->order_by('captured_at', 'DESC');
        $data['screenshots'] = $this->db->get('tbl_screenshots')->result();

        $data['subview'] = $this->load->view('admin/timesync/user_report', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function screenshots()
    {
        if (!is_super_admin()) {
            $can_view = can_action('timesync', 'view');
            if (!$can_view) {
                redirect('404');
            }
        }

        $data['title'] = 'Screenshots';

        $user_id = $this->input->get('user_id');
        $task_id = $this->input->get('task_id');
        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $this->db->select('tbl_screenshots.*, tbl_account_details.fullname');
        $this->db->from('tbl_screenshots');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_screenshots.user_id', 'left');

        if (!empty($user_id)) $this->db->where('tbl_screenshots.user_id', (int)$user_id);
        if (!empty($task_id)) $this->db->where('tbl_screenshots.task_id', (int)$task_id);
        if (!empty($from)) $this->db->where('tbl_screenshots.captured_at >=', $from);
        if (!empty($to)) $this->db->where('tbl_screenshots.captured_at <=', $to . ' 23:59:59');

        $this->db->order_by('tbl_screenshots.captured_at', 'DESC');
        $data['screenshots'] = $this->db->get()->result();

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
            $can_view = can_action('timesync', 'view');
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

        $this->db->select('a.*, u.username, ad.fullname');
        $this->db->from('tbl_desktop_app_usage a');
        $this->db->join('tbl_users u', 'u.user_id = a.user_id', 'left');
        $this->db->join('tbl_account_details ad', 'ad.user_id = a.user_id', 'left');

        if (!empty($user_id)) $this->db->where('a.user_id', (int)$user_id);
        $this->db->where('a.recorded_at >=', $from);
        $this->db->where('a.recorded_at <=', $to);
        $this->db->order_by('a.recorded_at', 'DESC');
        $this->db->order_by('a.total_seconds', 'DESC');
        $data['records'] = $this->db->get()->result();

        // Compute per-user totals and focus scores
        $user_totals = [];
        $user_apps = [];
        foreach ($data['records'] as $r) {
            $uid = $r->user_id;
            if (!isset($user_totals[$uid])) {
                $user_totals[$uid] = 0;
                $user_apps[$uid] = [];
            }
            $user_totals[$uid] += $r->total_seconds;
            if (!isset($user_apps[$uid][$r->app_name])) {
                $user_apps[$uid][$r->app_name] = 0;
            }
            $user_apps[$uid][$r->app_name] += $r->total_seconds;
        }

        $data['user_scores'] = [];
        foreach ($user_totals as $uid => $total) {
            $top_app_seconds = 0;
            if (!empty($user_apps[$uid])) {
                $top_app_seconds = max($user_apps[$uid]);
            }
            $focus_score = $total > 0 ? round(($top_app_seconds / $total) * 100) : 0;
            $data['user_scores'][$uid] = [
                'total_seconds' => $total,
                'focus_score' => $focus_score,
            ];
        }

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

    public function view_image($id)
    {
        $screenshot = $this->db->where('id', $id)->get('tbl_screenshots')->row();
        if (empty($screenshot)) {
            $this->_output_transparent_pixel();
        }

        $file_path = FCPATH . $screenshot->file_path;
        if (!file_exists($file_path)) {
            log_message('error', 'Screenshot file missing: ' . $file_path . ' (DB id: ' . $id . ')');
            $this->_output_transparent_pixel();
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

    private function _output_transparent_pixel()
    {
        header('Content-Type: image/png');
        header('Content-Length: 68');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
        exit;
    }

    public function settings()
    {
        if (!is_super_admin()) {
            redirect('404');
        }

        $data['title'] = 'TimeSync Settings';

        if ($this->input->post()) {
            $demo_mode = $this->input->post('demo_mode') == '1' ? '1' : '0';
            $this->db->where('config_key', 'timesync_demo_mode')->update('tbl_config', ['value' => $demo_mode]);
            set_message('success', 'Settings updated');
            redirect('admin/timesync/settings');
        }

        $data['demo_mode'] = config_item('timesync_demo_mode');

        $data['subview'] = $this->load->view('admin/timesync/settings', $data, true);
        $this->load->view('admin/_layout_main', $data);
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
}
