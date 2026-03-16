<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Clients extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    public function index()
    {
        $this->db->select('tbl_users.user_id, tbl_users.username, tbl_users.email, tbl_users.role_id, tbl_account_details.fullname, tbl_account_details.employment_id, tbl_account_details.phone, tbl_users.staff_position, tbl_users.facebook_url, tbl_users.instagram_url, tbl_users.x_url, tbl_users.linkedin_url');
        $this->db->from('tbl_users');
        $this->db->join('tbl_account_details', 'tbl_users.user_id = tbl_account_details.user_id', 'left');
        $this->db->where('tbl_users.role_id', 2);
        $this->db->where('tbl_users.activated', 1);
        $this->db->where('tbl_users.banned', 0);

        $query = $this->db->get();
        $clients = $query->result();

        foreach ($clients as $client) {
            if (!empty($client->avatar) && file_exists($client->avatar)) {
                $client->image = base_url() . $client->avatar;
            } else {
                $client->image = base_url() . 'assets/img/user/default_avatar.jpg';
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['clients' => $clients]));
    }
}
