<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tracker extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function config()
    {
        $user = $this->api_auth->authenticate();

        $default = [
            'idle_timeout_seconds' => 180,
            'min_activity_percent' => 40,
            'tracking_mode' => 'normal',
        ];

        $profile = $this->db->select('tbl_departments.departments_id')
            ->from('tbl_account_details')
            ->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id')
            ->join('tbl_departments', 'tbl_departments.departments_id = tbl_designations.departments_id')
            ->where('tbl_account_details.user_id', $user->user_id)
            ->get()->row();

        if (!empty($profile)) {
            switch ((int)$profile->departments_id) {
                case 4:
                    $default = [
                        'idle_timeout_seconds' => 300,
                        'min_activity_percent' => 20,
                        'tracking_mode' => 'lenient',
                    ];
                    break;
                case 2:
                    $default = [
                        'idle_timeout_seconds' => 120,
                        'min_activity_percent' => 60,
                        'tracking_mode' => 'strict',
                    ];
                    break;
            }
        }

        $db_interval = $this->db->where('config_key', 'screenshot_interval_minutes')->get('tbl_config')->row();
        if ($db_interval && (int)$db_interval->value > 0) {
            $default['screenshot_interval_minutes'] = (int)$db_interval->value;
        }

        // --- Hourly requirements ---
        $user_id = $user->user_id;
        $as_of = date('Y-m-d H:i:s');

        $setting = $this->db
            ->query("SELECT required_daily_hours, required_monthly_hours
                    FROM tbl_timesync_user_settings_log
                    WHERE user_id = ? AND changed_at <= ?
                    ORDER BY changed_at DESC LIMIT 1", [$user_id, $as_of])
            ->row();

        if ($setting) {
            $default['required_daily_hours'] = (float)$setting->required_daily_hours;
            $default['required_monthly_hours'] = (float)$setting->required_monthly_hours;
        } else {
            $config_log = $this->db
                ->query("SELECT config_key, value FROM tbl_timesync_config_log
                        WHERE config_key IN ('timesync_default_daily_hours', 'timesync_default_monthly_hours')
                        AND changed_at <= ?
                        ORDER BY changed_at DESC", [$as_of])
                ->result();
            $config_map = [];
            foreach ($config_log as $c) {
                $config_map[$c->config_key] = $c->value;
            }
            $default['required_daily_hours'] = (float)($config_map['timesync_default_daily_hours'] ?? 8.0);
            $default['required_monthly_hours'] = (float)($config_map['timesync_default_monthly_hours'] ?? 204.0);
        }

        return $this->_respond(200, true, 'OK', ['config' => $default]);
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
