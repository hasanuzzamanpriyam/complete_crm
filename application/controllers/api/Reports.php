<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function dashboard_analytics()
    {
        $user = $this->api_auth->authenticate();

        $user_id = $this->input->get('user_id');
        $period = $this->input->get('period') ?? 'weekly';

        if (empty($user_id)) {
            return $this->_respond(400, false, 'user_id is required');
        }

        $requested_user = (int)$user_id;
        $viewing_self = $requested_user === (int)$user->user_id;

        if (!$viewing_self && !$this->api_auth->is_super_admin()) {
            return $this->_respond(403, false, 'Only admins can view other users analytics');
        }

        switch ($period) {
            case 'daily':
                $since = date('Y-m-d', strtotime('-1 day'));
                $target_hours = 8;
                break;
            case 'weekly':
                $since = date('Y-m-d', strtotime('-7 days'));
                $target_hours = 56;
                break;
            case 'monthly':
                $since = date('Y-m-d', strtotime('-30 days'));
                $target_hours = 240;
                break;
            default:
                return $this->_respond(400, false, 'Invalid period. Use daily, weekly, or monthly');
        }

        $daily_hours = $this->_get_daily_hours($requested_user, $since);
        $total_seconds = array_sum(array_column($daily_hours, 'total_seconds'));
        $total_hours = round($total_seconds / 3600, 2);
        $task_completion_count = $this->_get_task_completion_count($requested_user, $since);
        $productivity_score = $target_hours > 0 ? min(100, round(($total_seconds / ($target_hours * 3600)) * 100)) : 0;
        $top_apps = $this->_get_top_apps($requested_user, $since);

        return $this->_respond(200, true, 'OK', [
            'analytics' => [
                'daily_hours' => $daily_hours,
                'total_hours_this_period' => $total_hours,
                'task_completion_count' => $task_completion_count,
                'productivity_score' => $productivity_score,
                'top_apps' => $top_apps,
            ],
        ]);
    }

    private function _get_daily_hours($user_id, $since)
    {
        $rows = $this->db
            ->select("DATE(started_at) as date, SUM(total_seconds) as total")
            ->from('tbl_desktop_time_entries')
            ->where('user_id', $user_id)
            ->where('started_at >=', $since)
            ->group_by('DATE(started_at)')
            ->order_by('DATE(started_at)', 'ASC')
            ->get()
            ->result();

        return array_map(function ($r) {
            return [
                'date' => $r->date,
                'total_seconds' => (int)$r->total,
            ];
        }, $rows);
    }

    private function _get_task_completion_count($user_id, $since)
    {
        $row = $this->db
            ->select("COUNT(DISTINCT te.task_id) as cnt")
            ->from('tbl_desktop_time_entries te')
            ->join('tbl_task t', 't.task_id = te.task_id', 'left')
            ->where('te.user_id', $user_id)
            ->where('te.started_at >=', $since)
            ->where('t.task_status', 'completed')
            ->get()
            ->row();

        $completed = $row ? (int)$row->cnt : 0;

        if ($completed === 0) {
            $fallback = $this->db
                ->select("COUNT(DISTINCT task_id) as cnt")
                ->from('tbl_desktop_time_entries')
                ->where('user_id', $user_id)
                ->where('started_at >=', $since)
                ->get()
                ->row();

            return $fallback ? (int)$fallback->cnt : 0;
        }

        return $completed;
    }

    private function _get_top_apps($user_id, $since)
    {
        $rows = $this->db
            ->select('app_name, SUM(total_seconds) as total')
            ->from('tbl_desktop_app_usage')
            ->where('user_id', $user_id)
            ->where('recorded_at >=', $since)
            ->group_by('app_name')
            ->order_by('total', 'DESC')
            ->limit(5)
            ->get()
            ->result();

        return array_map(function ($r) {
            return [
                'app_name' => $r->app_name,
                'total_seconds' => (int)$r->total,
            ];
        }, $rows);
    }

    private function _resolve_user_ids($requested_user_id = null)
    {
        $user = $this->api_auth->get_user();
        if ($this->api_auth->is_super_admin()) {
            if ($requested_user_id) return [(int)$requested_user_id];
            return null;
        }

        $managed_ids = $this->_get_managed_user_ids();
        if ($managed_ids === null) return null;
        if (empty($managed_ids)) return [$user->user_id];

        if ($requested_user_id) {
            return in_array((int)$requested_user_id, $managed_ids)
                ? [(int)$requested_user_id]
                : [$user->user_id];
        }
        return $managed_ids;
    }

    private function _get_managed_user_ids()
    {
        $user = $this->api_auth->get_user();
        if ($this->api_auth->is_super_admin()) return null;

        $departments = $this->db
            ->where('department_head_id', $user->user_id)
            ->get('tbl_departments')
            ->result();

        if (empty($departments)) return [];

        $dept_ids = array_map(function ($d) { return $d->departments_id; }, $departments);

        $designations = $this->db
            ->where_in('departments_id', $dept_ids)
            ->get('tbl_designations')
            ->result();

        if (empty($designations)) return [];

        $desig_ids = array_map(function ($d) { return $d->designations_id; }, $designations);

        $accounts = $this->db
            ->select('user_id')
            ->where_in('designations_id', $desig_ids)
            ->get('tbl_account_details')
            ->result();

        return array_map(function ($a) { return (int)$a->user_id; }, $accounts);
    }

    public function app_usage()
    {
        $user = $this->api_auth->authenticate();
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        $user_id = $this->input->get('user_id');

        if (empty($start_date) || empty($end_date)) {
            return $this->_respond(400, false, 'start_date and end_date required');
        }

        $user_ids = $this->_resolve_user_ids($user_id ? (int)$user_id : null);
        $this->db->select('app_name, window_title, SUM(total_seconds) as total_seconds');
        $this->db->from('tbl_desktop_app_usage');
        if (is_array($user_ids)) {
            $this->db->where_in('user_id', $user_ids);
        }
        $this->db->where('recorded_at >=', $start_date);
        $this->db->where('recorded_at <=', $end_date);
        $this->db->group_by('app_name, window_title');
        $this->db->order_by('total_seconds', 'DESC');
        $this->db->limit(100);
        $data = $this->db->get()->result();

        $result = array_map(function ($r) {
            return [
                'app_name' => $r->app_name,
                'window_title' => $r->window_title ?? '',
                'total_seconds' => (int)$r->total_seconds,
            ];
        }, $data);

        return $this->_respond(200, true, 'OK', ['app_usage' => $result]);
    }

    public function employee_summary()
    {
        $user = $this->api_auth->authenticate();
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        $user_id = $this->input->get('user_id');

        if (empty($start_date) || empty($end_date)) {
            return $this->_respond(400, false, 'start_date and end_date required');
        }

        $user_ids = $this->_resolve_user_ids($user_id ? (int)$user_id : null);
        $this->db->select("DATE(te.started_at) as date, te.user_id, ad.fullname as user_name, SUM(te.total_seconds) as total_seconds, COUNT(DISTINCT te.task_id) as task_count");
        $this->db->from('tbl_desktop_time_entries te');
        $this->db->join('tbl_account_details ad', 'ad.user_id = te.user_id', 'left');
        if (is_array($user_ids)) {
            $this->db->where_in('te.user_id', $user_ids);
        }
        $this->db->where('DATE(te.started_at) >=', $start_date);
        $this->db->where('DATE(te.started_at) <=', $end_date);
        $this->db->where('te.type', 'work');
        $this->db->group_by('DATE(te.started_at), te.user_id');
        $this->db->order_by('date', 'ASC');
        $data = $this->db->get()->result();

        $result = array_map(function ($r) {
            return [
                'date' => $r->date,
                'user_id' => (int)$r->user_id,
                'user_name' => $r->user_name ?? 'Unknown',
                'total_seconds' => (int)$r->total_seconds,
                'task_count' => (int)$r->task_count,
            ];
        }, $data);

        return $this->_respond(200, true, 'OK', ['employee_summary' => $result]);
    }

    public function project_summary()
    {
        $user = $this->api_auth->authenticate();
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        $user_id = $this->input->get('user_id');

        if (empty($start_date) || empty($end_date)) {
            return $this->_respond(400, false, 'start_date and end_date required');
        }

        $user_ids = $this->_resolve_user_ids($user_id ? (int)$user_id : null);
        $this->db->select('p.project_id, p.project_name, SUM(te.total_seconds) as total_seconds');
        $this->db->from('tbl_desktop_time_entries te');
        $this->db->join('tbl_task t', 't.task_id = te.task_id');
        $this->db->join('tbl_project p', 'p.project_id = t.project_id');
        if (is_array($user_ids)) {
            $this->db->where_in('te.user_id', $user_ids);
        }
        $this->db->where('DATE(te.started_at) >=', $start_date);
        $this->db->where('DATE(te.started_at) <=', $end_date);
        $this->db->where('te.type', 'work');
        $this->db->where('te.task_id IS NOT NULL');
        $this->db->group_by('p.project_id, p.project_name');
        $this->db->order_by('total_seconds', 'DESC');
        $data = $this->db->get()->result();

        $result = array_map(function ($r) {
            return [
                'project_id' => (int)$r->project_id,
                'project_name' => $r->project_name,
                'total_seconds' => (int)$r->total_seconds,
            ];
        }, $data);

        return $this->_respond(200, true, 'OK', ['project_summary' => $result]);
    }

    private function _respond($status_code, $success, $message, $data = null)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $response = array_merge($response, $data);
        }
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function calendar()
    {
        $user = $this->api_auth->authenticate();

        $target_user_id = $this->input->get('user_id') ? (int)$this->input->get('user_id') : null;
        $year = (int)($this->input->get('year') ?? date('Y'));
        $month = (int)($this->input->get('month') ?? date('m'));

        $user_ids = $this->_resolve_user_ids($target_user_id);

        $this->db->select("DATE(started_at) as date, SUM(total_seconds) as total_seconds");
        $this->db->from('tbl_desktop_time_entries');
        if (is_array($user_ids)) {
            $this->db->where_in('user_id', $user_ids);
        }
        $this->db->where('YEAR(started_at)', $year);
        $this->db->where('MONTH(started_at)', $month);
        $this->db->group_by('DATE(started_at)');
        $this->db->order_by('DATE(started_at)', 'ASC');
        $rows = $this->db->get()->result();

        $result = [];
        $month_total = 0;

        foreach ($rows as $r) {
            $hours = round((float)$r->total_seconds / 3600, 1);
            $status = $hours >= 8 ? 'present' : ($hours >= 4 ? 'half-day' : 'absent');
            $result[$r->date] = ['total_hours' => $hours, 'status' => $status];
            $month_total += $hours;
        }

        $days_in_month = (int)date('t', strtotime("$year-$month-01"));
        for ($d = 1; $d <= $days_in_month; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
            if (!isset($result[$date])) {
                $result[$date] = ['total_hours' => 0.0, 'status' => 'absent'];
            }
        }
        ksort($result);

        $result['monthly_summary'] = [
            'total_hours_this_month' => round($month_total, 1),
            'present_days' => count(array_filter($result, function ($v) { return $v['status'] === 'present'; })),
            'half_days' => count(array_filter($result, function ($v) { return $v['status'] === 'half-day'; })),
            'absent_days' => count(array_filter($result, function ($v) { return $v['status'] === 'absent'; })),
        ];

        return $this->_respond(200, true, 'OK', $result);
    }
}
