<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Time_entries extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function index($id = null)
    {
        $method = $this->input->server('REQUEST_METHOD');

        switch ($method) {
            case 'GET':
                $this->_list();
                break;
            case 'POST':
                $this->_create();
                break;
            case 'PUT':
                $this->_update($id);
                break;
            default:
                $this->_respond(405, false, 'Method not allowed');
        }
    }

    private function _list()
    {
        $user = $this->api_auth->authenticate();
        $requested_user_id = $this->input->get('user_id');

        $allowed_ids = $this->api_auth->get_authorized_user_ids($requested_user_id);
        if ($allowed_ids !== null) {
            $this->db->where_in('user_id', $allowed_ids);
        }
        $entries = $this->db->order_by('started_at', 'DESC')->get('tbl_desktop_time_entries')->result();

        $result = array_map(function ($e) {
            return [
                'id' => (int)$e->id,
                'task_id' => $e->task_id ? (int)$e->task_id : null,
                'user_id' => (int)$e->user_id,
                'type' => $e->type,
                'started_at' => $e->started_at,
                'paused_at' => $e->paused_at,
                'resumed_at' => $e->resumed_at,
                'stopped_at' => $e->stopped_at,
                'total_seconds' => (int)$e->total_seconds,
                'is_running' => (bool)$e->is_running,
                'created_at' => $e->created_at,
            ];
        }, $entries);

        return $this->_respond(200, true, 'OK', ['time_entries' => $result]);
    }

    private function _validate_timestamp($str)
    {
        if (empty($str)) return null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/', $str)) {
            return 'Invalid format, expected YYYY-MM-DD HH:MM:SS or ISO 8601';
        }
        $ts = strtotime($str);
        if ($ts === false) {
            return 'Invalid format, expected YYYY-MM-DD HH:MM:SS or ISO 8601';
        }
        if ($ts > time() + 60) {
            return 'Timestamp cannot be in the future';
        }
        return null;
    }

    private function _create()
    {
        $user = $this->api_auth->authenticate();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!array_key_exists('task_id', $input)) {
            return $this->_respond(400, false, 'Task ID is required');
        }

        $started_at = $input['started_at'] ?? null;
        $stopped_at = $input['stopped_at'] ?? null;
        $paused_at = $input['paused_at'] ?? null;
        $resumed_at = $input['resumed_at'] ?? null;

        if ($started_at !== null) {
            $err = $this->_validate_timestamp($started_at);
            if ($err) return $this->_respond(400, false, "started_at: $err");
        }
        if ($stopped_at !== null) {
            $err = $this->_validate_timestamp($stopped_at);
            if ($err) return $this->_respond(400, false, "stopped_at: $err");
            $start_ts = $started_at ? strtotime($started_at) : null;
            if ($start_ts && strtotime($stopped_at) <= $start_ts) {
                return $this->_respond(400, false, 'stopped_at must be after started_at');
            }
        }
        if ($paused_at !== null) {
            $err = $this->_validate_timestamp($paused_at);
            if ($err) return $this->_respond(400, false, "paused_at: $err");
        }
        if ($resumed_at !== null) {
            $err = $this->_validate_timestamp($resumed_at);
            if ($err) return $this->_respond(400, false, "resumed_at: $err");
        }

        $total_seconds = (int)($input['total_seconds'] ?? 0);
        // NOTE: do NOT hard-reject when total_seconds is outside the started/stopped
        // wall-clock span. Paused/idle time is excluded from total_seconds on the
        // desktop, so for sessions with pauses the two legitimately differ. We keep
        // the computed value as-is and let the client's total_seconds stand.

        $data = [
            'task_id' => !empty($input['task_id']) ? (int)$input['task_id'] : null,
            'user_id' => $user->user_id,
            'type' => $input['type'] ?? 'work',
            'started_at' => $started_at ?? date('Y-m-d H:i:s'),
            'paused_at' => $paused_at,
            'resumed_at' => $resumed_at,
            'stopped_at' => $stopped_at,
            'total_seconds' => $total_seconds,
            'is_running' => !empty($input['is_running']) ? 1 : 0,
        ];

        $this->db->insert('tbl_desktop_time_entries', $data);
        $entry_id = $this->db->insert_id();

        return $this->_respond(201, true, 'Time entry created', [
            'id' => (int)$entry_id,
            'task_id' => $data['task_id'] ? (int)$data['task_id'] : null,
            'total_seconds' => $data['total_seconds'],
        ]);
    }

    private function _update($id)
    {
        $user = $this->api_auth->authenticate();
        $input = json_decode(file_get_contents('php://input'), true);

        $entry = $this->db->where('id', $id)->where('user_id', $user->user_id)->get('tbl_desktop_time_entries')->row();
        if (empty($entry)) {
            return $this->_respond(404, false, 'Time entry not found');
        }

        if (!empty($entry->stopped_at)) {
            return $this->_respond(400, false, 'Cannot modify a completed time entry. Create a new one via POST.');
        }

        $update = [];
        if (isset($input['stopped_at'])) {
            $err = $this->_validate_timestamp($input['stopped_at']);
            if ($err) return $this->_respond(400, false, "stopped_at: $err");
            if (!empty($entry->started_at) && strtotime($input['stopped_at']) <= strtotime($entry->started_at)) {
                return $this->_respond(400, false, 'stopped_at must be after started_at');
            }
            $update['stopped_at'] = $input['stopped_at'];
        }
        if (isset($input['paused_at'])) {
            $err = $this->_validate_timestamp($input['paused_at']);
            if ($err) return $this->_respond(400, false, "paused_at: $err");
            $update['paused_at'] = $input['paused_at'];
        }
        if (isset($input['total_seconds'])) $update['total_seconds'] = (int)$input['total_seconds'];
        if (isset($input['is_running'])) $update['is_running'] = $input['is_running'] ? 1 : 0;

        if (!empty($update)) {
            $this->db->where('id', $id)->update('tbl_desktop_time_entries', $update);
        }

        return $this->_respond(200, true, 'Time entry updated');
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
