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
}
