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

        $visible_ids = $this->_resolve_user_ids();

        $this->db->select('u.user_id, u.online_time, a.fullname, a.avatar')
            ->from('tbl_users u')
            ->join('tbl_account_details a', 'a.user_id = u.user_id', 'left')
            ->where('u.activated', 1)
            ->where('u.banned', 0);
        if ($visible_ids !== null) {
            $this->db->where_in('u.user_id', $visible_ids);
        }
        $all_users = $this->db->get()->result();

        $active_entries = $this->db
            ->select('tde.user_id, t.task_name as task_title')
            ->from('tbl_desktop_time_entries tde')
            ->join('tbl_task t', 't.task_id = tde.task_id', 'left')
            ->where('tde.is_running', 1)
            ->where('tde.stopped_at IS NULL', null, false);
        if ($visible_ids !== null) {
            $this->db->where_in('tde.user_id', $visible_ids);
        }
        $active_rows = $active_entries->get()->result();

        $active_map = [];
        $active_user_ids = [];
        foreach ($active_rows as $e) {
            $active_map[$e->user_id] = ['task_title' => $e->task_title];
            $active_user_ids[] = $e->user_id;
        }

        $active_count = 0;
        $idle_count = 0;
        $offline_count = 0;
        $active_users_list = [];
        $now_ts = time();

        foreach ($all_users as $u) {
            $uid = (int)$u->user_id;
            $is_active = isset($active_map[$uid]);
            $online_time_ok = !empty($u->online_time) && (int)$u->online_time > ($now_ts - 300);

            if ($is_active) {
                $active_count++;

                $window = $this->db
                    ->select('app_name, window_title')
                    ->from('tbl_desktop_app_usage')
                    ->where('user_id', $uid)
                    ->where('time_entry_id IN (SELECT id FROM tbl_desktop_time_entries WHERE user_id = ' . (int)$uid . ' AND is_running = 1 AND stopped_at IS NULL)', null, false)
                    ->order_by('created_at', 'DESC')
                    ->limit(1)
                    ->get()->row();

                $active_users_list[] = [
                    'user_id' => $uid,
                    'name' => !empty($u->fullname) ? $u->fullname : $u->username,
                    'avatar' => base_url(!empty($u->avatar) ? $u->avatar : 'assets/img/user/default_avatar.jpg'),
                    'current_task' => $active_map[$uid]['task_title'],
                    'current_window' => $window ? (!empty($window->window_title) ? $window->window_title : $window->app_name) : null,
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
                'idle' => $idle_count,
                'offline' => $offline_count,
            ],
            'active_users' => $active_users_list,
        ]);
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
