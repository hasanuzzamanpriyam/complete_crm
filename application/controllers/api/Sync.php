<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sync extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function heartbeat()
    {
        $user = $this->api_auth->authenticate();

        $this->db->where('user_id', $user->user_id)
            ->update('tbl_users', ['last_active_ping' => date('Y-m-d H:i:s')]);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
    }
}
