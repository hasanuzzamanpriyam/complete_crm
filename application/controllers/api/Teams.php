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
                    if (!$this->api_auth->is_super_admin()) {
                        $this->_respond(403, false, 'Admin access required');
                    }
                    $this->_delete($id);
                } else {
                    $this->_respond(405, false, 'Method not allowed');
                }
                break;
            default:
                $this->_respond(405, false, 'Method not allowed');
        }
    }

    public function members($team_id, $user_id = null)
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

    public function messages($team_id)
    {
        $this->api_auth->authenticate();
        $method = $this->input->server('REQUEST_METHOD');

        if ($method === 'GET') {
            $this->_get_messages($team_id);
        } elseif ($method === 'POST') {
            $this->_send_message($team_id);
        } else {
            $this->_respond(405, false, 'Method not allowed');
        }
    }

    public function request_join()
    {
        try {
            $this->api_auth->authenticate();
            $data = json_decode($this->input->raw_input_stream, true);

            if (empty($data['team_id'])) {
                $this->_respond(400, false, 'team_id is required');
                return;
            }

            $team_id = (int)$data['team_id'];
            $user_id = $this->api_auth->get_user()->user_id;
            $leave_others = !empty($data['leave_others']);

            $team = $this->Team_model->get_team($team_id);
            if (!$team) {
                $this->_respond(404, false, 'Team not found');
                return;
            }

            $existing = $this->db->where('team_id', $team_id)
                ->where('user_id', $user_id)
                ->get('tbl_team_members')
                ->num_rows();

            if ($existing > 0) {
                $this->_respond(409, false, 'You already have a membership record for this team');
                return;
            }

            $this->db->trans_start();

            if ($leave_others) {
                $this->db->where('user_id', $user_id)
                    ->where('status', 'approved')
                    ->update('tbl_team_members', ['status' => 'left']);
            }

            $this->db->insert('tbl_team_members', [
                'team_id' => $team_id,
                'user_id' => $user_id,
                'is_manager' => 0,
                'status' => 'pending',
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $this->_respond(500, false, 'Database transaction failed');
                return;
            }

            $this->_respond(201, true, 'Join request submitted');
        } catch (Exception $e) {
            log_message('error', 'Team join request failed: ' . $e->getMessage());
            $this->_respond(500, false, 'Server error during join request');
        }
    }

    public function approve_member()
    {
        try {
            $this->api_auth->authenticate();
            $data = json_decode($this->input->raw_input_stream, true);

            if (empty($data['team_id']) || empty($data['user_id']) || empty($data['action'])) {
                $this->_respond(400, false, 'team_id, user_id, and action are required');
                return;
            }

            $team_id = (int)$data['team_id'];
            $target_user_id = (int)$data['user_id'];
            $action = $data['action'];

            if (!$this->Team_model->is_team_manager($this->api_auth->get_user()->user_id, $team_id)) {
                $this->_respond(403, false, 'Team manager access required');
                return;
            }

            if ($action === 'approve') {
                $this->db->where('team_id', $team_id)
                    ->where('user_id', $target_user_id)
                    ->where('status', 'pending')
                    ->update('tbl_team_members', ['status' => 'approved']);
                $this->_respond(200, true, 'Member approved');
            } elseif ($action === 'reject') {
                $this->db->where('team_id', $team_id)
                    ->where('user_id', $target_user_id)
                    ->where('status', 'pending')
                    ->delete('tbl_team_members');
                $this->_respond(200, true, 'Request rejected');
            } else {
                $this->_respond(400, false, 'Action must be "approve" or "reject"');
            }
        } catch (Exception $e) {
            log_message('error', 'Team approve/reject failed: ' . $e->getMessage());
            $this->_respond(500, false, 'Server error during approve/reject');
        }
    }

    public function pending_requests($team_id)
    {
        $this->api_auth->authenticate();

        if (!$this->Team_model->is_team_manager($this->api_auth->get_user()->user_id, $team_id)) {
            $this->_respond(403, false, 'Team manager access required');
        }

        $pending = $this->Team_model->get_pending_members($team_id);
        $this->_respond(200, true, 'OK', ['data' => $pending]);
    }

    public function my_requests()
    {
        $this->api_auth->authenticate();
        $requests = $this->Team_model->get_user_pending_requests(
            $this->api_auth->get_user()->user_id
        );
        $this->_respond(200, true, 'OK', ['data' => $requests]);
    }

    public function mentions()
    {
        $this->api_auth->authenticate();
        $user_id = $this->api_auth->get_user()->user_id;
        $since = $this->input->get('since');

        $this->db->where('to_user_id', $user_id);
        $this->db->where('icon', 'fa fa-at');
        if ($since) {
            $this->db->where('date >=', $since);
        }
        $this->db->order_by('date', 'DESC');
        $this->db->limit(50);
        $rows = $this->db->get('tbl_notifications')->result();

        $this->_respond(200, true, 'OK', ['data' => $rows]);
    }

    public function my_memberships()
    {
        $this->api_auth->authenticate();
        $user_id = $this->api_auth->get_user()->user_id;

        $memberships = $this->db->select('tm.*, u.username, u.email, t.name as team_name')
            ->from('tbl_team_members tm')
            ->join('tbl_users u', 'u.user_id = tm.user_id')
            ->join('tbl_teams t', 't.id = tm.team_id')
            ->where('tm.user_id', $user_id)
            ->where('tm.status', 'approved')
            ->get()
            ->result();

        $this->_respond(200, true, 'OK', ['data' => $memberships]);
    }

    private function _list()
    {
        $teams = $this->Team_model->get_all_teams();
        $this->_respond(200, true, 'OK', ['data' => $teams]);
    }

    private function _get($id)
    {
        $team = $this->Team_model->get_team($id);
        if (!$team) {
            $this->_respond(404, false, 'Team not found');
        }
        $this->_respond(200, true, 'OK', ['data' => $team]);
    }

    private function _create()
    {
        try {
            $user = $this->api_auth->get_user();
            $data = json_decode($this->input->raw_input_stream, true);
            if (empty($data['name'])) {
                $this->_respond(400, false, 'Team name is required');
                return;
            }

            // Idempotency: same name + same creator within the last 30 seconds → return existing
            $recent = $this->db
                ->where('name', $data['name'])
                ->where('created_by', $user->user_id)
                ->where('created_at >=', date('Y-m-d H:i:s', time() - 30))
                ->get('tbl_teams')
                ->row();
            if ($recent) {
                $team = $this->Team_model->get_team($recent->id);
                $this->_respond(200, true, 'Team already exists (recent duplicate)', ['data' => $team]);
                return;
            }

            $insert = [
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'created_by' => $user->user_id,
            ];

            $this->db->trans_start();
            $this->db->insert('tbl_teams', $insert);
            $team_id = $this->db->insert_id();
            $this->db->insert('tbl_team_members', [
                'team_id' => $team_id,
                'user_id' => $user->user_id,
                'is_manager' => 1,
                'status' => 'approved',
            ]);
            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $this->_respond(500, false, 'Database transaction failed');
                return;
            }

            $team = $this->Team_model->get_team($team_id);
            if (!$team) {
                $this->_respond(500, false, 'Team not found after creation');
                return;
            }
            $this->_respond(201, true, 'Team created', ['data' => $team, 'id' => (int)$team->id]);
        } catch (Exception $e) {
            log_message('error', 'Team creation failed: ' . $e->getMessage());
            $this->_respond(500, false, 'Server error during team creation');
        }
    }

    private function _update($id)
    {
        if (!$this->Team_model->is_team_manager($this->api_auth->get_user()->user_id, $id)
            && !$this->api_auth->is_super_admin()) {
            $this->_respond(403, false, 'Team manager access required');
        }

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
        $this->db->where('id', $id)->delete('tbl_teams');
        $this->_respond(200, true, 'Team deleted');
    }

    private function _members($team_id)
    {
        if (!$this->Team_model->is_team_manager($this->api_auth->get_user()->user_id, $team_id)
            && !$this->api_auth->is_super_admin()) {
            $this->_respond(403, false, 'Team manager access required');
        }

        $members = $this->Team_model->get_team_members_with_users($team_id);
        $this->_respond(200, true, 'OK', ['data' => $members]);
    }

    private function _add_member($team_id)
    {
        try {
            if (!$this->Team_model->is_team_manager($this->api_auth->get_user()->user_id, $team_id)
                && !$this->api_auth->is_super_admin()) {
                $this->_respond(403, false, 'Team manager access required');
                return;
            }

            $data = json_decode($this->input->raw_input_stream, true);

            if (empty($data['user_id'])) {
                $this->_respond(400, false, 'user_id is required');
                return;
            }

            $exists = $this->db->where('team_id', $team_id)
                ->where('user_id', $data['user_id'])
                ->get('tbl_team_members')
                ->num_rows();

            if ($exists > 0) {
                $this->_respond(409, false, 'User is already a member');
                return;
            }

            $this->db->insert('tbl_team_members', [
                'team_id' => $team_id,
                'user_id' => $data['user_id'],
                'is_manager' => 0,
                'status' => 'approved',
            ]);
            $member_id = $this->db->insert_id();

            $this->_respond(201, true, 'Member added', ['id' => $member_id]);
        } catch (Exception $e) {
            log_message('error', 'Add team member failed: ' . $e->getMessage());
            $this->_respond(500, false, 'Server error during add member');
        }
    }

    private function _remove_member($team_id, $user_id)
    {
        if (!$this->Team_model->is_team_manager($this->api_auth->get_user()->user_id, $team_id)
            && !$this->api_auth->is_super_admin()) {
            $this->_respond(403, false, 'Team manager access required');
        }
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

    private function _get_messages($team_id)
    {
        try {
            $user_id = $this->api_auth->get_user()->user_id;
            if (!$this->Team_model->is_team_member($user_id, $team_id)) {
                $this->_respond(403, false, 'Team member access required');
                return;
            }

            $since = $this->input->get('since');
            $messages = $this->Team_model->get_messages($team_id, $since);
            $this->_respond(200, true, 'OK', ['data' => $messages]);
        } catch (Exception $e) {
            log_message('error', 'Get messages failed: ' . $e->getMessage());
            $this->_respond(500, false, 'Server error');
        }
    }

    private function _send_message($team_id)
    {
        try {
            $user = $this->api_auth->get_user();
            $data = json_decode($this->input->raw_input_stream, true);

            if (empty($data['message'])) {
                $this->_respond(400, false, 'message is required');
                return;
            }

            if (!$this->Team_model->is_team_member($user->user_id, $team_id)) {
                $this->_respond(403, false, 'Team member access required');
                return;
            }

            $message = trim($data['message']);
            $msg_id = $this->Team_model->add_message($team_id, $user->user_id, $message);

            // Detect @mentions and create notifications
            preg_match_all('/\B@(\w+)/', $message, $matches);
            if (!empty($matches[1])) {
                $team = $this->Team_model->get_team($team_id);
                $team_name = $team ? $team->name : 'a team';

                foreach ($matches[1] as $username) {
                    $mentioned = $this->Team_model->resolve_username($username);
                    if ($mentioned && (int)$mentioned->user_id !== (int)$user->user_id) {
                        if ($this->Team_model->is_team_member($mentioned->user_id, $team_id)) {
                            $this->load->helper('admin_helper');
                            add_notification([
                                'to_user_id' => $mentioned->user_id,
                                'description' => $user->username . ' mentioned you in ' . $team_name,
                                'link' => 'team/' . $team_id,
                                'icon' => 'fa fa-at',
                                'value' => json_encode([
                                    'team_id' => $team_id,
                                    'message_id' => $msg_id,
                                ]),
                            ]);
                        }
                    }
                }
            }

            $created = $this->db->select('tm.*, u.username, u.full_name')
                ->from('tbl_team_messages tm')
                ->join('tbl_users u', 'u.user_id = tm.user_id')
                ->where('tm.id', $msg_id)
                ->get()
                ->row();

            $this->_respond(201, true, 'Message sent', ['data' => $created]);
        } catch (Exception $e) {
            log_message('error', 'Send message failed: ' . $e->getMessage());
            $this->_respond(500, false, 'Server error');
        }
    }

    private function _respond($status_code, $success, $message, $data = null)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
        }
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
