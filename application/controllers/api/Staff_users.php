<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staff_users extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    public function index()
    {
        $this->db->select('tbl_users.user_id, tbl_users.username, tbl_users.email, tbl_users.role_id, tbl_account_details.fullname, tbl_account_details.employment_id, tbl_account_details.phone, tbl_users.staff_position, tbl_users.facebook_url, tbl_users.instagram_url, tbl_users.x_url, tbl_users.linkedin_url, tbl_designations.designations');
        $this->db->from('tbl_users');
        $this->db->join('tbl_account_details', 'tbl_users.user_id = tbl_account_details.user_id', 'left');
        $this->db->join('tbl_designations', 'tbl_account_details.designations_id = tbl_designations.designations_id', 'left');
        $this->db->where('tbl_users.role_id', 3);
        $this->db->where('tbl_users.activated', 1);
        $this->db->where('tbl_users.banned', 0);

        $query = $this->db->get();
        $users = $query->result();

        foreach ($users as $user) {
            if (!empty($user->avatar) && file_exists($user->avatar)) {
                $user->image = base_url() . $user->avatar;
            } else {
                $user->image = base_url() . 'assets/img/user/default_avatar.jpg';
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['users' => $users]));
    }
}
