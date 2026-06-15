<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tasks extends CI_Controller
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

    private function _list()
    {
        $user = $this->api_auth->authenticate();
        $user_id = $user->user_id;

        $this->db->select('tbl_task.*');
        $this->db->from('tbl_task');
        $this->db->where('tbl_task.task_status !=', 'cancelled');

        $this->db->group_start();
        $this->db->where('tbl_task.created_by', $user_id);
        if ($this->db->version() >= 8) {
            $sq = '\\b' . ($user_id) . '\\b';
        } else {
            $sq = '[[:<:]]' . ($user_id) . '[[:>:]]';
        }
        $this->db->or_where('tbl_task.permission REGEXP', $this->db->escape($sq), false);
        $this->db->or_where('tbl_task.permission', 'all');
        $this->db->group_end();

        $tasks = $this->db->order_by('tbl_task.task_created_date', 'DESC')->get()->result();

        $result = array_map(function ($t) {
            $hours = 0;
            if (!empty($t->task_hour)) {
                $parts = explode(':', $t->task_hour);
                $hours = (int)$parts[0] * 60 + ((int)($parts[1] ?? 0));
            }
            return [
                'id' => (int)$t->task_id,
                'title' => $t->task_name ?? '',
                'description' => $t->task_description ?? '',
                'project_id' => $t->project_id ? (int)$t->project_id : null,
                'assigned_to' => null,
                'priority' => 'medium',
                'status' => $this->_map_status($t->task_status),
                'estimated_minutes' => $hours,
                'erp_id' => (int)$t->task_id,
                'created_by' => (int)$t->created_by,
                'created_at' => $t->task_created_date ?? date('Y-m-d H:i:s'),
                'updated_at' => $t->task_created_date ?? date('Y-m-d H:i:s'),
            ];
        }, $tasks);

        return $this->_respond(200, true, 'OK', ['tasks' => $result]);
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

        $task_data = [
            'task_name' => $input['title'],
            'task_description' => $input['description'] ?? '',
            'project_id' => !empty($input['project_id']) ? (int)$input['project_id'] : null,
            'task_status' => $this->_reverse_map_status($input['status'] ?? 'pending'),
            'task_hour' => $hours_minutes,
            'task_start_date' => date('Y-m-d'),
            'due_date' => null,
            'created_by' => $user->user_id,
            'permission' => 'all',
            'task_created_date' => date('Y-m-d H:i:s'),
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
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
        }
        return $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
