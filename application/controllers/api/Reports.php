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
        $period = $this->input->get('period');
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        if (empty($user_id)) {
            return $this->_respond(400, false, 'user_id is required');
        }

        $requested_user = (int)$user_id;
        $allowed_ids = $this->_resolve_allowed_user_ids($requested_user);

        if (is_array($allowed_ids) && !in_array($requested_user, $allowed_ids)) {
            return $this->_respond(403, false, 'You do not have permission to view this users analytics');
        }

        if ($start_date && $end_date) {
            $since = $start_date;
            $until = $end_date;

            $working_days = $this->_count_working_days($since, $until);
            $leave_days = $this->_count_user_leave_days($requested_user, $since, $until);
            $adjusted_wd = max(0, $working_days - $leave_days);

            $as_of = $until . ' 23:59:59';
            $setting = $this->db
                ->query("SELECT required_daily_hours FROM tbl_timesync_user_settings_log
                    WHERE user_id = ? AND changed_at <= ?
                    ORDER BY changed_at DESC LIMIT 1", [$requested_user, $as_of])
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

            $target_sec = $daily_hours * $adjusted_wd * 3600;
        } else {
            $period = $period ?? 'weekly';
            switch ($period) {
                case 'daily':
                    $since = date('Y-m-d', strtotime('-1 day'));
                    $target_sec = 8 * 3600;
                    break;
                case 'weekly':
                    $since = date('Y-m-d', strtotime('-7 days'));
                    $target_sec = 56 * 3600;
                    break;
                case 'monthly':
                    $since = date('Y-m-d', strtotime('-30 days'));
                    $target_sec = 240 * 3600;
                    break;
                default:
                    return $this->_respond(400, false, 'Invalid period. Use daily, weekly, or monthly');
            }
            $until = null;
        }

        $daily_hours = $this->_get_daily_hours($requested_user, $since, $until);
        $total_seconds = array_sum(array_column($daily_hours, 'total_seconds'));
        $total_hours = round($total_seconds / 3600, 2);
        $task_completion_count = $this->_get_task_completion_count($requested_user, $since, $until);
        $productivity_score = $target_sec > 0 ? min(100, round(($total_seconds / $target_sec) * 100)) : 0;
        $top_apps = $this->_get_top_apps($requested_user, $since, $until);

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

    private function _get_daily_hours($user_id, $since, $until = null)
    {
        $this->db
            ->select("DATE(started_at) as date, SUM(total_seconds) as total")
            ->from('tbl_desktop_time_entries')
            ->where('user_id', $user_id)
            ->where('started_at >=', $since)
            ->where('type', 'work');
        if ($until) {
            $this->db->where('started_at <=', $until . ' 23:59:59');
        }
        $rows = $this->db
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

    private function _get_task_completion_count($user_id, $since, $until = null)
    {
        $this->db
            ->select("COUNT(DISTINCT te.task_id) as cnt")
            ->from('tbl_desktop_time_entries te')
            ->join('tbl_task t', 't.task_id = te.task_id', 'left')
            ->where('te.user_id', $user_id)
            ->where('te.started_at >=', $since)
            ->where('te.type', 'work')
            ->where('t.task_status', 'completed');
        if ($until) {
            $this->db->where('te.started_at <=', $until . ' 23:59:59');
        }
        $row = $this->db->get()->row();

        $completed = $row ? (int)$row->cnt : 0;

        if ($completed === 0) {
            $this->db
                ->select("COUNT(DISTINCT task_id) as cnt")
                ->from('tbl_desktop_time_entries')
                ->where('user_id', $user_id)
                ->where('type', 'work')
                ->where('started_at >=', $since);
            if ($until) {
                $this->db->where('started_at <=', $until . ' 23:59:59');
            }
            $fallback = $this->db->get()->row();

            return $fallback ? (int)$fallback->cnt : 0;
        }

        return $completed;
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

    private function _get_top_apps($user_id, $since, $until = null)
    {
        $this->db
            ->select('app_name, SUM(total_seconds) as total')
            ->from('tbl_desktop_app_usage')
            ->where('user_id', $user_id)
            ->where('recorded_at >=', $since);
        if ($until) {
            $this->db->where('recorded_at <=', $until . ' 23:59:59');
        }
        $rows = $this->db
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

    private function _resolve_allowed_user_ids($requested_user_id = null)
    {
        return $this->api_auth->get_authorized_user_ids($requested_user_id);
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

        $user_ids = $this->_resolve_allowed_user_ids($user_id ? (int)$user_id : null);
        $this->db->select('app_name, window_title, url, SUM(total_seconds) as total_seconds');
        $this->db->from('tbl_desktop_app_usage');
        if (is_array($user_ids)) {
            $this->db->where_in('user_id', $user_ids);
        }
        $this->db->where('recorded_at >=', $start_date);
        $this->db->where('recorded_at <=', $end_date);
        $this->db->group_by('app_name, window_title, url');
        $this->db->order_by('total_seconds', 'DESC');
        $this->db->limit(100);
        $data = $this->db->get()->result();

        $result = array_map(function ($r) {
            return [
                'app_name' => $r->app_name,
                'window_title' => $r->window_title ?? '',
                'url' => $r->url ?? null,
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

        $user_ids = $this->_resolve_allowed_user_ids($user_id ? (int)$user_id : null);
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
        $this->db->order_by('date', 'DESC');
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

        $user_ids = $this->_resolve_allowed_user_ids($user_id ? (int)$user_id : null);

        $visible_project_ids = $this->api_auth->get_visible_project_ids(
            is_array($user_ids) ? $user_ids : null
        );

        $time_sub = "(";
        $time_sub .= "SELECT p2.project_id, SUM(te.total_seconds) as total_seconds";
        $time_sub .= " FROM tbl_project p2";
        $time_sub .= " JOIN tbl_task t ON t.project_id = p2.project_id";
        $time_sub .= " JOIN tbl_desktop_time_entries te ON te.task_id = t.task_id";
        $time_sub .= " WHERE te.type = 'work'";
        $time_sub .= " AND DATE(te.started_at) >= " . $this->db->escape($start_date);
        $time_sub .= " AND DATE(te.started_at) <= " . $this->db->escape($end_date);
        if (is_array($user_ids)) {
            $time_sub .= " AND te.user_id IN (" . implode(',', $user_ids) . ")";
        }
        $time_sub .= " GROUP BY p2.project_id) sub";

        $this->db->select("p.project_id, p.project_name, p.progress, p.project_status,
            COALESCE(sub.total_seconds, 0) as total_seconds,
            (SELECT COUNT(*) FROM tbl_task t WHERE t.project_id = p.project_id) as task_count");
        $this->db->from('tbl_project p');
        $this->db->join($time_sub, 'sub.project_id = p.project_id', 'left');
        if ($visible_project_ids !== null) {
            $this->db->where_in('p.project_id', $visible_project_ids);
        }
        $this->db->group_by('p.project_id, p.project_name');
        $this->db->order_by('total_seconds', 'DESC');
        $data = $this->db->get()->result();

        $result = array_map(function ($r) {
            return [
                'project_id' => (int)$r->project_id,
                'project_name' => $r->project_name,
                'total_seconds' => (int)$r->total_seconds,
                'progress' => (int)($r->progress ?? 0),
                'project_status' => $r->project_status ?? '',
                'task_count' => (int)($r->task_count ?? 0),
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

        $user_ids = $this->_resolve_allowed_user_ids($target_user_id);

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

    public function day_details()
    {
        $user = $this->api_auth->authenticate();

        $year = $this->input->get('year');
        $month = $this->input->get('month');
        $day = $this->input->get('day');

        if (empty($year) || empty($month) || empty($day)) {
            return $this->_respond(400, false, 'year, month, and day are required');
        }

        $target_user_id = $this->input->get('user_id') ? (int)$this->input->get('user_id') : null;
        $user_ids = $this->_resolve_allowed_user_ids($target_user_id);

        $date = sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day);

        $this->db->select("te.task_id, t.task_name as task_title, p.project_name, SUM(te.total_seconds) as total_seconds");
        $this->db->from('tbl_desktop_time_entries te');
        $this->db->join('tbl_task t', 't.task_id = te.task_id', 'left');
        $this->db->join('tbl_project p', 'p.project_id = t.project_id', 'left');
        if (is_array($user_ids)) {
            $this->db->where_in('te.user_id', $user_ids);
        }
        $this->db->where('DATE(te.started_at)', $date);
        $this->db->where('te.type', 'work');
        $this->db->group_by('te.task_id, t.task_name, p.project_name');
        $this->db->order_by('total_seconds', 'DESC');
        $rows = $this->db->get()->result();

        $result = array_map(function ($r) {
            return [
                'task_id' => (int)$r->task_id,
                'task_title' => $r->task_title ?? 'Unknown',
                'project_name' => $r->project_name ?? 'No Project',
                'total_seconds' => (int)$r->total_seconds,
            ];
        }, $rows);

        return $this->_respond(200, true, 'OK', ['day_details' => $result]);
    }
}
