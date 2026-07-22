<?php
defined('BASEPATH') or exit('No direct script access allowed');

class App_usage extends MY_Controller
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
            default:
                $this->_respond(405, false, 'Method not allowed');
        }
    }

    private function _list()
    {
        $user = $this->api_auth->authenticate();

        $user_id = $this->input->get('user_id');
        $time_entry_id = $this->input->get('time_entry_id');
        $from = $this->input->get('from');
        $to = $this->input->get('to');
        $limit = (int)$this->input->get('limit') ?: 100;

        $this->db->select('a.*, u.username, ad.fullname');
        $this->db->from('tbl_desktop_app_usage a');
        $this->db->join('tbl_users u', 'u.user_id = a.user_id', 'left');
        $this->db->join('tbl_account_details ad', 'ad.user_id = a.user_id', 'left');

        $allowed_ids = $this->api_auth->get_authorized_user_ids($user_id);
        if ($allowed_ids !== null) {
            $this->db->where_in('a.user_id', $allowed_ids);
        }

        if (!empty($time_entry_id)) $this->db->where('a.time_entry_id', (int)$time_entry_id);
        if (!empty($from)) $this->db->where('a.recorded_at >=', $from);
        if (!empty($to)) $this->db->where('a.recorded_at <=', $to . ' 23:59:59');

        $this->db->order_by('a.recorded_at', 'DESC');
        $this->db->order_by('a.total_seconds', 'DESC');
        $this->db->limit($limit);
        $records = $this->db->get()->result();

        $result = array_map(function ($r) {
            return [
                'id' => (int)$r->id,
                'user_id' => (int)$r->user_id,
                'username' => $r->fullname ?? $r->username ?? 'Unknown',
                'time_entry_id' => $r->time_entry_id ? (int)$r->time_entry_id : null,
                'app_name' => $r->app_name,
                'window_title' => $r->window_title,
                'url' => $r->url ?? null,
                'total_seconds' => (int)$r->total_seconds,
                'recorded_at' => $r->recorded_at,
            ];
        }, $records);

        return $this->_respond(200, true, 'OK', ['app_usage' => $result]);
    }

    private function _create()
    {
        $user = $this->api_auth->authenticate();
        $input = json_decode(file_get_contents('php://input'), true);

        // Debug: log incoming payload
        $log_line = date('Y-m-d H:i:s') . ' ' . json_encode($input) . PHP_EOL;
        @file_put_contents(APPPATH . 'logs/sync_debug.log', $log_line, FILE_APPEND);

        if (empty($input['app_usage']) || !is_array($input['app_usage'])) {
            return $this->_respond(400, false, 'app_usage array is required');
        }

        $inserted = 0;
        $errors = 0;
        $batch_time = !empty($input['app_usage'][0]['recorded_at'])
            ? date('Y-m-d H:i:s', strtotime($input['app_usage'][0]['recorded_at']))
            : date('Y-m-d H:i:s');

        $total_batch_seconds = 0;
        foreach ($input['app_usage'] as $item) {
            $total_batch_seconds += (int)($item['total_seconds'] ?? 0);
        }

        $offset_seconds = $total_batch_seconds;

        foreach ($input['app_usage'] as $item) {
            if (empty($item['app_name'])) continue;
            if (empty($item['time_entry_id'])) {
                error_log('SYNC WARNING: time_entry_id is empty/missing for app=' . $item['app_name']);
            }

            $item_seconds = (int)($item['total_seconds'] ?? 0);
            $offset_seconds -= $item_seconds;
            $item_time = date('Y-m-d H:i:s', strtotime($batch_time) - $offset_seconds);

            $data = [
                'user_id' => $user->user_id,
                'time_entry_id' => !empty($item['time_entry_id']) ? (int)$item['time_entry_id'] : null,
                'app_name' => $item['app_name'],
                'window_title' => $item['window_title'] ?? null,
                'url' => isset($item['url']) ? $item['url'] : null,
                'total_seconds' => $item_seconds,
                'recorded_at' => !empty($item['recorded_at']) ? date('Y-m-d H:i:s', strtotime($item['recorded_at'])) : $item_time,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->db->insert('tbl_desktop_app_usage', $data)) {
                $inserted++;
            } else {
                $errors++;
            }
        }

        return $this->_respond(201, true, "$inserted records inserted, $errors errors", [
            'inserted' => $inserted,
            'errors' => $errors,
        ]);
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
