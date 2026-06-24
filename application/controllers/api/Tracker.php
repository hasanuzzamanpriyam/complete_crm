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
