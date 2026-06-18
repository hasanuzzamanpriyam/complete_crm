<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Screenshots extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
        $this->load->helper('file');
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
                $this->_upload();
                break;
            default:
                $this->_respond(405, false, 'Method not allowed');
        }
    }

    private function _list()
    {
        $user = $this->api_auth->authenticate();
        $is_admin = $this->api_auth->is_super_admin();

        $user_id = $this->input->get('user_id');
        $task_id = $this->input->get('task_id');
        $from = $this->input->get('from');
        $to = $this->input->get('to');
        $limit = (int)$this->input->get('limit') ?: 50;

        $this->db->select('tbl_screenshots.*, tbl_account_details.fullname');
        $this->db->from('tbl_screenshots');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_screenshots.user_id', 'left');

        if (!$is_admin) {
            $this->db->where('tbl_screenshots.user_id', $user->user_id);
        } elseif (!empty($user_id)) {
            $this->db->where('tbl_screenshots.user_id', (int)$user_id);
        }

        if (!empty($task_id)) $this->db->where('tbl_screenshots.task_id', (int)$task_id);
        if (!empty($from)) $this->db->where('tbl_screenshots.captured_at >=', $from);
        if (!empty($to)) $this->db->where('tbl_screenshots.captured_at <=', $to);

        $this->db->order_by('tbl_screenshots.captured_at', 'DESC');
        $this->db->limit($limit);
        $screenshots = $this->db->get()->result();

        $result = array_map(function ($s) {
            return [
                'id' => (int)$s->id,
                'user_id' => (int)$s->user_id,
                'task_id' => $s->task_id ? (int)$s->task_id : null,
                'username' => $s->fullname ?? 'Unknown',
                'file_url' => base_url($s->file_path),
                'file_size' => (int)$s->file_size,
                'captured_at' => $s->captured_at,
                'uploaded_at' => $s->uploaded_at,
            ];
        }, $screenshots);

        return $this->_respond(200, true, 'OK', ['screenshots' => $result]);
    }

    private function _get($id)
    {
        $user = $this->api_auth->authenticate();
        $is_admin = $this->api_auth->is_super_admin();

        $this->db->where('id', $id);
        if (!$is_admin) {
            $this->db->where('user_id', $user->user_id);
        }
        $screenshot = $this->db->get('tbl_screenshots')->row();

        if (empty($screenshot)) {
            return $this->_respond(404, false, 'Screenshot not found');
        }

        $file_path = FCPATH . $screenshot->file_path;
        if (!file_exists($file_path)) {
            return $this->_respond(404, false, 'File not found on server');
        }

        $file_content = file_get_contents($file_path);
        $this->output
            ->set_status_header(200)
            ->set_content_type('image/png')
            ->set_output($file_content);
    }

    private function _upload()
    {
        $user = $this->api_auth->authenticate();
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['image_base64']) && empty($_FILES['image'])) {
            return $this->_respond(400, false, 'No image provided');
        }

        $task_id = !empty($input['task_id']) ? (int)$input['task_id'] : null;
        $captured_at = $input['captured_at'] ?? date('Y-m-d H:i:s');
        $user_id = $user->user_id;

        $upload_dir = FCPATH . 'uploads/screenshots/' . $user_id;
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0777, true)) {
                return $this->_respond(500, false, 'Failed to create upload directory');
            }
        }

        $filename = date('Ymd_His') . '_' . uniqid() . '.png';
        $file_path = 'uploads/screenshots/' . $user_id . '/' . $filename;

        if (!empty($input['image_base64'])) {
            $image_data = base64_decode($input['image_base64']);
            if ($image_data === false) {
                return $this->_respond(400, false, 'Invalid base64 image data');
            }
            $written = file_put_contents(FCPATH . $file_path, $image_data);
            if ($written === false) {
                return $this->_respond(500, false, 'Failed to save image');
            }
            $file_size = $written;
        } else {
            $config['upload_path'] = $upload_dir;
            $config['allowed_types'] = 'png|jpg|jpeg|gif';
            $config['max_size'] = 10240;
            $config['file_name'] = $filename;
            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('image')) {
                return $this->_respond(400, false, $this->upload->display_errors('', ''));
            }
            $file_data = $this->upload->data();
            $file_size = $file_data['file_size'];
        }

        $this->db->insert('tbl_screenshots', [
            'user_id' => $user_id,
            'task_id' => $task_id,
            'file_path' => $file_path,
            'file_size' => $file_size,
            'captured_at' => $captured_at,
        ]);
        $screenshot_id = $this->db->insert_id();

        return $this->_respond(201, true, 'Screenshot uploaded', [
            'id' => (int)$screenshot_id,
            'file_url' => base_url($file_path),
        ]);
    }

    public function delete($id)
    {
        $user = $this->api_auth->authenticate();
        $is_admin = $this->api_auth->is_super_admin();

        if (!$is_admin) {
            return $this->_respond(403, false, 'Only admins can delete screenshots');
        }

        $screenshot = $this->db->where('id', $id)->get('tbl_screenshots')->row();
        if (empty($screenshot)) {
            return $this->_respond(404, false, 'Screenshot not found');
        }

        $file_path = FCPATH . $screenshot->file_path;
        if (file_exists($file_path)) {
            @unlink($file_path);
        }

        $this->db->where('id', $id)->delete('tbl_screenshots');
        return $this->_respond(200, true, 'Screenshot deleted');
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
