<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Projects extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function index()
    {
        $user = $this->api_auth->authenticate();
        $user_id = $user->user_id;

        $projects = $this->db
            ->select('project_id, project_name, description, progress, created_by, permission')
            ->where('project_status', 'in_progress')
            ->or_where('project_status', 'completed')
            ->get('tbl_project')
            ->result();

        $result = array_map(function ($p) use ($user_id) {
            $perm = $p->permission ?? '';
            $is_assigned = ($p->created_by == $user_id)
                || $perm === 'all'
                || preg_match('/\b' . $user_id . '\b/', $perm);

            return [
                'id' => (int)$p->project_id,
                'name' => $p->project_name ?? '',
                'description' => $p->description ?? '',
                'progress' => (int)$p->progress,
                'erp_id' => (int)$p->project_id,
                'is_active' => true,
                'is_assigned' => $is_assigned,
                'created_at' => '',
                'updated_at' => '',
            ];
        }, $projects);

        usort($result, function ($a, $b) {
            if ($a['is_assigned'] !== $b['is_assigned']) {
                return $b['is_assigned'] <=> $a['is_assigned'];
            }
            return $a['name'] <=> $b['name'];
        });

        return $this->_respond(200, true, 'OK', ['projects' => $result]);
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
