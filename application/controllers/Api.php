<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    public function staff_users()
    {
        $this->db->select('tbl_users.user_id, tbl_users.username, tbl_users.email, tbl_users.role_id, tbl_account_details.fullname, tbl_account_details.employment_id, tbl_account_details.phone, tbl_account_details.avatar');
        $this->db->from('tbl_users');
        $this->db->join('tbl_account_details', 'tbl_users.user_id = tbl_account_details.user_id', 'left');
        $this->db->where('tbl_users.role_id', 3);
        $this->db->where('tbl_users.activated', 1);
        $this->db->where('tbl_users.banned', 0);

        $query = $this->db->get();
        $users = $query->result();

        foreach ($users as $user) {
            if (!empty($user->avatar) && file_exists($user->avatar)) {
                $user->image = base_url() . $user->avatar;
            }
            else {
                $user->image = base_url() . 'assets/img/user/default_avatar.jpg';
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($users));
    }
}
