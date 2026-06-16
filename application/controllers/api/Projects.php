<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Projects extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function index()
    {
        $this->api_auth->authenticate();

        $user_id = $user->user_id;

        $this->db->select('project_id, project_name, description, progress');
        $this->db->group_start();
        $this->db->where('project_status', 'in_progress');
        $this->db->or_where('project_status', 'completed');
        $this->db->group_end();
        $this->db->group_start();
        $this->db->where('created_by', $user_id);
        if ($this->db->version() >= 8) {
            $sq = '\\b' . ($user_id) . '\\b';
        } else {
            $sq = '[[:<:]]' . ($user_id) . '[[:>:]]';
        }
        $this->db->or_where('permission REGEXP', $this->db->escape($sq), false);
        $this->db->or_where('permission', 'all');
        $this->db->group_end();

        $projects = $this->db->get('tbl_project')->result();

        $result = array_map(function ($p) {
            return [
                'id' => (int)$p->project_id,
                'name' => $p->project_name ?? '',
                'description' => $p->description ?? '',
                'progress' => (int)$p->progress,
                'erp_id' => (int)$p->project_id,
                'is_active' => true,
                'created_at' => '',
                'updated_at' => '',
            ];
        }, $projects);

        return $this->_respond(200, true, 'OK', ['projects' => $result]);
    }

    private function _respond($status_code, $success, $message, $data = null)
    {
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $response = array_merge($response, $data);
        }
        return $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
