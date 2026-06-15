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

        $projects = $this->db
            ->select('project_id, project_name, description')
            ->where('status', 'in_progress')
            ->or_where('status', 'completed')
            ->get('tbl_project')
            ->result();

        $result = array_map(function ($p) {
            return [
                'id' => (int)$p->project_id,
                'name' => $p->project_name ?? '',
                'description' => $p->description ?? '',
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
