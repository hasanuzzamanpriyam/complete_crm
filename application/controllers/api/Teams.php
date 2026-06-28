<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Teams extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
        $this->load->model('Team_model');
    }

    public function index($id = null)
    {
        $this->api_auth->authenticate();
        $method = $this->input->server('REQUEST_METHOD');

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->_get($id);
                } else {
                    $this->_list();
                }
                break;
            case 'POST':
                if ($id) {
                    $this->_respond(405, false, 'Method not allowed');
                } else {
                    $this->_create();
                }
                break;
            case 'PUT':
                if ($id) {
                    $this->_update($id);
                } else {
                    $this->_respond(405, false, 'Method not allowed');
                }
                break;
            case 'DELETE':
                if ($id) {
                    $this->_delete($id);
                } else {
                    $this->_respond(405, false, 'Method not allowed');
                }
                break;
            default:
                $this->_respond(405, false, 'Method not allowed');
        }
    }

    public function members($team_id, $action = null, $user_id = null)
    {
        $this->api_auth->authenticate();
        $method = $this->input->server('REQUEST_METHOD');

        if ($method === 'GET') {
            $this->_members($team_id);
        } elseif ($method === 'POST') {
            $this->_add_member($team_id);
        } elseif ($method === 'DELETE') {
            if (!$user_id) {
                $this->_respond(400, false, 'user_id is required');
            }
            $this->_remove_member($team_id, $user_id);
        } elseif ($method === 'PUT') {
            if (!$user_id) {
                $this->_respond(400, false, 'user_id is required');
            }
            $this->_set_manager($team_id, $user_id);
        } else {
            $this->_respond(405, false, 'Method not allowed');
        }
    }

    private function _require_team_access($team_id, $require_manager = false)
    {
        if ($this->api_auth->is_super_admin()) {
            return;
        }
        if ($require_manager) {
            if (!$this->Team_model->is_team_manager($this->api_auth->get_user()->user_id, $team_id)) {
                $this->_respond(403, false, 'Team manager access required');
            }
        } else {
            if (!$this->Team_model->is_team_member($this->api_auth->get_user()->user_id, $team_id)) {
                $this->_respond(403, false, 'Team access required');
            }
        }
    }

    private function _list()
    {
        if ($this->api_auth->is_super_admin()) {
            $teams = $this->Team_model->get_all_teams();
        } else {
            $teams = $this->Team_model->get_teams_for_user($this->api_auth->get_user()->user_id);
        }
        $this->_respond(200, true, 'OK', ['data' => $teams]);
    }

    private function _get($id)
    {
        $this->_require_team_access($id);
        $team = $this->Team_model->get_team($id);
        if (!$team) {
            $this->_respond(404, false, 'Team not found');
        }
        $this->_respond(200, true, 'OK', ['data' => $team]);
    }

    private function _create()
    {
        $data = json_decode($this->input->raw_input_stream, true);
        if (empty($data['name'])) {
            $this->_respond(400, false, 'Team name is required');
        }

        $insert = [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'created_by' => $this->api_auth->get_user()->user_id,
        ];

        $this->db->insert('tbl_teams', $insert);
        $team_id = $this->db->insert_id();

        $this->db->insert('tbl_team_members', [
            'team_id' => $team_id,
            'user_id' => $this->api_auth->get_user()->user_id,
            'is_manager' => 1,
        ]);

        $team = $this->Team_model->get_team($team_id);
        $this->_respond(201, true, 'Team created', ['data' => $team]);
    }

    private function _update($id)
    {
        $this->_require_team_access($id, true);
        $data = json_decode($this->input->raw_input_stream, true);

        $update = [];
        if (isset($data['name'])) $update['name'] = $data['name'];
        if (isset($data['description'])) $update['description'] = $data['description'];

        if (empty($update)) {
            $this->_respond(400, false, 'No fields to update');
        }

        $this->db->where('id', $id)->update('tbl_teams', $update);
        $team = $this->Team_model->get_team($id);
        $this->_respond(200, true, 'Team updated', ['data' => $team]);
    }

    private function _delete($id)
    {
        if (!$this->api_auth->is_super_admin()) {
            $this->_respond(403, false, 'Admin access required');
        }
        $this->db->where('id', $id)->delete('tbl_teams');
        $this->_respond(200, true, 'Team deleted');
    }

    private function _members($team_id)
    {
        $this->_require_team_access($team_id, true);
        $user_ids = $this->Team_model->get_team_member_ids($team_id);

        if (empty($user_ids)) {
            $this->_respond(200, true, 'OK', ['data' => []]);
            return;
        }

        $members = $this->db->where_in('user_id', $user_ids)
            ->select('user_id, username, email')
            ->get('tbl_users')
            ->result();

        $this->_respond(200, true, 'OK', ['data' => $members]);
    }

    private function _add_member($team_id)
    {
        $this->_require_team_access($team_id, true);
        $data = json_decode($this->input->raw_input_stream, true);

        if (empty($data['user_id'])) {
            $this->_respond(400, false, 'user_id is required');
        }

        $exists = $this->db->where('team_id', $team_id)
            ->where('user_id', $data['user_id'])
            ->get('tbl_team_members')
            ->num_rows();

        if ($exists > 0) {
            $this->_respond(409, false, 'User is already a member');
        }

        $this->db->insert('tbl_team_members', [
            'team_id' => $team_id,
            'user_id' => $data['user_id'],
            'is_manager' => 0,
        ]);

        $this->_respond(201, true, 'Member added');
    }

    private function _remove_member($team_id, $user_id)
    {
        $this->_require_team_access($team_id, true);
        $this->db->where('team_id', $team_id)
            ->where('user_id', $user_id)
            ->delete('tbl_team_members');
        $this->_respond(200, true, 'Member removed');
    }

    private function _set_manager($team_id, $user_id)
    {
        if (!$this->api_auth->is_super_admin()) {
            $this->_respond(403, false, 'Admin access required');
        }

        $data = json_decode($this->input->raw_input_stream, true);
        $is_manager = !empty($data['is_manager']) ? 1 : 0;

        $this->db->where('team_id', $team_id)
            ->where('user_id', $user_id)
            ->update('tbl_team_members', ['is_manager' => $is_manager]);

        $this->_respond(200, true, 'Manager status updated');
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
