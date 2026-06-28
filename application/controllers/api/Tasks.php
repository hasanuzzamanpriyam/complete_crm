<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tasks extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
        $this->load->model('tasks_model');
    }

    public function index($id = null)
    {
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
                $this->_create();
                break;
            case 'PUT':
                $this->_update($id);
                break;
            case 'DELETE':
                $this->_delete($id);
                break;
            default:
                $this->_respond(405, false, 'Method not allowed');
        }
    }

    private function _apply_task_visibility($user_id, $user)
    {
        $this->db->where('tbl_task.task_status !=', 'cancelled');

        if (!$this->api_auth->is_super_admin() && $user->role_id != 1) {
            $this->db->group_start();
            $this->db->where('tbl_task.created_by', $user_id);
            if ($this->db->version() >= 8) {
                $sq = '\\b' . ($user_id) . '\\b';
            } else {
                $sq = '[[:<:]]' . ($user_id) . '[[:>:]]';
            }
            $this->db->or_where('tbl_task.permission REGEXP', $this->db->escape($sq), false);
            $this->db->or_where('tbl_task.permission', 'all');

            $team_ids = $this->api_auth->get_user_team_ids();
            if (!empty($team_ids)) {
                $team_ids_str = implode(',', $team_ids);
                $this->db->or_where("tbl_task.team_id IN ($team_ids_str)", null, false);
            }

            $this->db->group_end();
        }
    }

    private function _list()
    {
        $user = $this->api_auth->authenticate();
        $user_id = $user->user_id;

        $page = max(1, (int)($this->input->get('page') ?: 1));
        $limit = max(1, min(100, (int)($this->input->get('limit') ?: 20)));
        $offset = ($page - 1) * $limit;

        // Count total matching rows
        $this->db->from('tbl_task');
        $this->db->join('tbl_project', 'tbl_task.project_id = tbl_project.project_id', 'left');
        $this->_apply_task_visibility($user_id, $user);
        $total = (int)$this->db->count_all_results();

        // Fetch paginated results
        $this->db->select("
            tbl_task.*,
            tbl_project.project_name,
            ad_reporter.fullname AS reporter_name,
            ad_reporter.avatar AS reporter_avatar
        ");
        $this->db->from('tbl_task');
        $this->db->join('tbl_project', 'tbl_task.project_id = tbl_project.project_id', 'left');
        $this->db->join('tbl_users u_reporter', 'u_reporter.user_id = tbl_task.created_by', 'left');
        $this->db->join('tbl_account_details ad_reporter', 'ad_reporter.user_id = u_reporter.user_id', 'left');
        $this->_apply_task_visibility($user_id, $user);
        $this->db->order_by('tbl_task.task_created_date', 'DESC');
        $this->db->limit($limit, $offset);
        $tasks = $this->db->get()->result();

        // Collect unique assignee user IDs for batch lookup
        $assigneeIds = [];
        $assigneePermissions = [];
        foreach ($tasks as $t) {
            $firstId = null;
            if (!empty($t->permission) && $t->permission !== 'all') {
                $perm = @json_decode($t->permission, true);
                if (is_array($perm)) {
                    $keys = array_keys($perm);
                    if (!empty($keys)) {
                        $firstId = (int)$keys[0];
                    }
                }
            }
            $assigneePermissions[$t->task_id] = $firstId;
            if ($firstId !== null) {
                $assigneeIds[] = $firstId;
            }
        }

        $assigneeMap = [];
        if (!empty($assigneeIds)) {
            $assigneeIds = array_unique($assigneeIds);
            $idList = implode(',', $assigneeIds);
            $assigneeRows = $this->db->query("SELECT u.user_id, ad.fullname, ad.avatar FROM tbl_users u LEFT JOIN tbl_account_details ad ON ad.user_id = u.user_id WHERE u.user_id IN ($idList)")->result();
            foreach ($assigneeRows as $row) {
                $assigneeMap[(int)$row->user_id] = $row;
            }
        }

        $result = array_map(function ($t) use ($assigneePermissions, $assigneeMap) {
            $hours = 0;
            if (!empty($t->task_hour)) {
                $parts = explode(':', $t->task_hour);
                $hours = (int)$parts[0] * 60 + ((int)($parts[1] ?? 0));
            }
            $assigned = 'everyone';
            if (!empty($t->permission) && $t->permission !== 'all') {
                $perm = @json_decode($t->permission, true);
                if (is_array($perm)) {
                    $assigned = array_map('intval', array_keys($perm));
                }
            }

            $assigneeUserId = $assigneePermissions[$t->task_id] ?? null;
            $assigneeName = '';
            $assigneeAvatar = '';
            if ($assigneeUserId !== null && isset($assigneeMap[$assigneeUserId])) {
                $assigneeName = $assigneeMap[$assigneeUserId]->fullname ?? '';
                $assigneeAvatar = $assigneeMap[$assigneeUserId]->avatar ?? '';
            }

            return [
                'id' => (int)$t->task_id,
                'title' => $t->task_name ?? '',
                'description' => $t->task_description ?? '',
                'project_id' => $t->project_id ? (int)$t->project_id : null,
                'project_name' => $t->project_name ?? '',
                'assigned_to' => $assigned,
                'assignee_name' => $assigneeName,
                'assignee_avatar' => $assigneeAvatar,
                'reporter_name' => $t->reporter_name ?? '',
                'reporter_avatar' => $t->reporter_avatar ?? '',
                'report_to' => $t->report_to ? (int)$t->report_to : null,
                'priority' => $t->priority ?? 'medium',
                'status' => $this->_map_status($t->task_status),
                'estimated_minutes' => $hours,
                'progress' => (int)($t->task_progress ?? 0),
                'erp_id' => (int)$t->task_id,
                'created_by' => (int)$t->created_by,
                'created_at' => $t->task_created_date ?? date('Y-m-d H:i:s'),
                'updated_at' => $t->task_created_date ?? date('Y-m-d H:i:s'),
            ];
        }, $tasks);

        $total_pages = (int)ceil($total / $limit);

        return $this->_respond(200, true, 'OK', [
            'tasks' => $result,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => $total_pages,
            ],
        ]);
    }

    private function _get($id)
    {
        $user = $this->api_auth->authenticate();
        $task = $this->db->where('task_id', $id)->get('tbl_task')->row();

        if (empty($task)) {
            return $this->_respond(404, false, 'Task not found');
        }

        return $this->_respond(200, true, 'OK', ['task' => [
            'id' => (int)$task->task_id,
            'title' => $task->task_name ?? '',
            'description' => $task->task_description ?? '',
            'status' => $this->_map_status($task->task_status),
            'priority' => $task->priority ?? 'medium',
            'progress' => (int)($task->task_progress ?? 0),
        ]]);
    }

    private function _create()
    {
        $user = $this->api_auth->authenticate();
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['title'])) {
            return $this->_respond(400, false, 'Title is required');
        }

        $hours_minutes = '';
        if (!empty($input['estimated_minutes'])) {
            $mins = (int)$input['estimated_minutes'];
            $hours_minutes = sprintf('%d:%02d', intdiv($mins, 60), $mins % 60);
        }

        $permission = 'all';
        if (isset($input['assigned_to'])) {
            if (is_array($input['assigned_to'])) {
                $perm_map = [];
                foreach ($input['assigned_to'] as $uid) {
                    $perm_map[(int)$uid] = ['view', 'edit', 'delete'];
                }
                $admins = $this->db->select('user_id')
                    ->group_start()
                    ->where('role_id', 1)
                    ->or_where('is_super_admin', 1)
                    ->group_end()
                    ->get('tbl_users')
                    ->result();
                foreach ($admins as $a) {
                    $perm_map[(int)$a->user_id] = ['view', 'edit', 'delete'];
                }
                $permission = json_encode($perm_map);
            }
        }

        $team_id = $input['team_id'] ?? null;
        if ($team_id) {
            $this->load->model('Team_model');
            if (!$this->api_auth->is_super_admin() &&
                !$this->Team_model->is_team_manager($this->api_auth->get_user()->user_id, $team_id)) {
                $this->_respond(403, false, 'Team manager access required');
            }
            $permission = 'all';
        }

        $task_data = [
            'team_id' => $team_id,
            'task_name' => $input['title'],
            'task_description' => $input['description'] ?? '',
            'project_id' => !empty($input['project_id']) ? (int)$input['project_id'] : null,
            'task_status' => $this->_reverse_map_status($input['status'] ?? 'pending'),
            'task_hour' => $hours_minutes,
            'task_start_date' => date('Y-m-d'),
            'due_date' => null,
            'created_by' => $user->user_id,
            'permission' => $permission,
            'report_to' => !empty($input['report_to']) ? (int)$input['report_to'] : null,
            'task_created_date' => date('Y-m-d H:i:s'),
            'priority' => $input['priority'] ?? 'medium',
            'task_progress' => !empty($input['task_progress']) ? (int)$input['task_progress'] : (!empty($input['progress']) ? (int)$input['progress'] : 0),
        ];

        if (!empty($input['due_date'])) {
            $task_data['due_date'] = date('Y-m-d', strtotime($input['due_date']));
        }

        $this->db->insert('tbl_task', $task_data);
        $task_id = $this->db->insert_id();

        return $this->_respond(201, true, 'Task created', [
            'id' => (int)$task_id,
            'title' => $input['title'],
            'erp_id' => (int)$task_id,
            'created_at' => $task_data['task_created_date'],
        ]);
    }

    private function _update($id)
    {
        $user = $this->api_auth->authenticate();
        $input = json_decode(file_get_contents('php://input'), true);

        $task = $this->db->where('task_id', $id)->get('tbl_task')->row();
        if (empty($task)) {
            return $this->_respond(404, false, 'Task not found');
        }

        $update = [];
        if (isset($input['title'])) $update['task_name'] = $input['title'];
        if (isset($input['description'])) $update['task_description'] = $input['description'];
        if (isset($input['status'])) $update['task_status'] = $this->_reverse_map_status($input['status']);
        if (isset($input['project_id'])) $update['project_id'] = (int)$input['project_id'];
        if (isset($input['estimated_minutes'])) {
            $mins = (int)$input['estimated_minutes'];
            $update['task_hour'] = sprintf('%d:%02d', intdiv($mins, 60), $mins % 60);
        }
        if (isset($input['priority'])) $update['priority'] = $input['priority'];
        $progress = $input['task_progress'] ?? $input['progress'] ?? null;
        if ($progress !== null) $update['task_progress'] = (int)$progress;
        if (isset($input['assigned_to'])) {
            if (is_array($input['assigned_to'])) {
                $perm_map = [];
                foreach ($input['assigned_to'] as $uid) {
                    $perm_map[(int)$uid] = ['view', 'edit', 'delete'];
                }
                $admins = $this->db->select('user_id')
                    ->group_start()
                    ->where('role_id', 1)
                    ->or_where('is_super_admin', 1)
                    ->group_end()
                    ->get('tbl_users')
                    ->result();
                foreach ($admins as $a) {
                    $perm_map[(int)$a->user_id] = ['view', 'edit', 'delete'];
                }
                $update['permission'] = json_encode($perm_map);
            } else {
                $update['permission'] = 'all';
            }
        }
        if (array_key_exists('report_to', $input)) {
            $update['report_to'] = !empty($input['report_to']) ? (int)$input['report_to'] : null;
        }

        if (isset($input['team_id'])) {
            $this->load->model('Team_model');
            if (!$this->api_auth->is_super_admin() &&
                !$this->Team_model->is_team_manager($this->api_auth->get_user()->user_id, $input['team_id'])) {
                $this->_respond(403, false, 'Team manager access required');
            }
            $update['team_id'] = $input['team_id'];
        }

        if (!empty($update)) {
            $this->db->where('task_id', $id)->update('tbl_task', $update);
        }

        return $this->_respond(200, true, 'Task updated');
    }

    private function _delete($id)
    {
        $user = $this->api_auth->authenticate();

        $task = $this->db->where('task_id', $id)->get('tbl_task')->row();
        if (empty($task)) {
            return $this->_respond(404, false, 'Task not found');
        }

        $this->db->where('task_id', $id)->delete('tbl_tasks_timer');
        $this->db->query('DELETE FROM tbl_desktop_app_usage WHERE time_entry_id IN (SELECT id FROM tbl_desktop_time_entries WHERE task_id = ' . (int)$id . ')');
        $this->db->where('task_id', $id)->delete('tbl_desktop_time_entries');

        $screenshots = $this->db->where('task_id', $id)->get('tbl_screenshots')->result();
        foreach ($screenshots as $s) {
            if (!empty($s->file_path) && file_exists(FCPATH . $s->file_path)) {
                @unlink(FCPATH . $s->file_path);
            }
        }
        $this->db->where('task_id', $id)->delete('tbl_screenshots');
        $this->db->where('task_id', $id)->delete('tbl_task');

        return $this->_respond(200, true, 'Task deleted');
    }

    private function _map_status($status)
    {
        $map = [
            'not_started' => 'pending',
            'in_progress' => 'in_progress',
            'completed' => 'completed',
            'deferred' => 'on_hold',
            'waiting_for_someone' => 'on_hold',
        ];
        return $map[$status] ?? 'pending';
    }

    private function _reverse_map_status($status)
    {
        $map = [
            'pending' => 'not_started',
            'in_progress' => 'in_progress',
            'completed' => 'completed',
            'on_hold' => 'deferred',
            'cancelled' => 'deferred',
        ];
        return $map[$status] ?? 'not_started';
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

    public function comments($task_id)
    {
        $this->api_auth->authenticate();
        $task_id = (int)$task_id;

        if ($this->input->method() === 'post') {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $text = trim($input['comment_text'] ?? '');
            if (empty($text)) {
                return $this->_respond(400, false, 'comment_text is required');
            }
            $user = $this->api_auth->get_user();
            $data = [
                'user_id' => $user->user_id,
                'comment' => $text,
                'module' => 'tasks',
                'module_field_id' => $task_id,
                'comment_datetime' => date('Y-m-d H:i:s'),
            ];
            $this->db->insert('tbl_task_comment', $data);
            return $this->_respond(201, true, 'Comment created', ['id' => (int)$this->db->insert_id()]);
        }

        $comments = $this->db
            ->select('tc.task_comment_id as id, tc.user_id, u.username, tc.comment, tc.comment_datetime as created_at')
            ->from('tbl_task_comment tc')
            ->join('tbl_users u', 'u.user_id = tc.user_id')
            ->where('tc.module', 'tasks')
            ->where('tc.module_field_id', $task_id)
            ->order_by('tc.comment_datetime', 'ASC')
            ->get()->result();

        return $this->_respond(200, true, 'OK', ['comments' => $comments]);
    }

    public function recent_comments()
    {
        $user = $this->api_auth->authenticate();
        $since = $this->input->get('since');

        if (empty($since)) {
            return $this->_respond(400, false, 'since parameter required (ISO datetime)');
        }

        $visible = $this->db
            ->select('task_id')
            ->from('tbl_task')
            ->group_start()
            ->where('created_by', (int)$user->user_id)
            ->or_where("JSON_VALID(permission) AND JSON_CONTAINS_PATH(permission, 'one', '$.\"" . (int)$user->user_id . "\"')", null, false)
            ->or_where('permission', 'all')
            ->group_end()
            ->get()->result();

        if (empty($visible)) {
            return $this->_respond(200, true, 'OK', ['comments' => []]);
        }

        $task_ids = array_map(function ($t) { return (int)$t->task_id; }, $visible);

        $comments = $this->db
            ->select('tc.task_comment_id as id, tc.user_id, u.username, tc.comment, tc.module_field_id as task_id, tc.comment_datetime as created_at')
            ->from('tbl_task_comment tc')
            ->join('tbl_users u', 'u.user_id = tc.user_id')
            ->where('tc.module', 'tasks')
            ->where_in('tc.module_field_id', $task_ids)
            ->where('tc.comment_datetime >', $since)
            ->where('tc.user_id !=', (int)$user->user_id)
            ->order_by('tc.comment_datetime', 'ASC')
            ->get()->result();

        return $this->_respond(200, true, 'OK', ['comments' => $comments]);
    }
}
