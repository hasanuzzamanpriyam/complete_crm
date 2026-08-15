<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_model extends MY_Model {

    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------
    // Settings
    // ------------------------------------------------------------------------

    public function is_enabled()
    {
        $row = $this->db->where('config_key', 'ai_assistant_enabled')->get('tbl_config')->row();
        return (!empty($row) && $row->value == '1');
    }

    public function set_setting($key, $value)
    {
        $exists = $this->db->where('config_key', $key)->get('tbl_config')->num_rows();
        if ($exists > 0) {
            return $this->db->where('config_key', $key)->update('tbl_config', array('value' => $value));
        }
        return $this->db->insert('tbl_config', array('config_key' => $key, 'value' => $value));
    }

    // ------------------------------------------------------------------------
    // Providers
    // ------------------------------------------------------------------------

    public function get_providers($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        $this->db->order_by('is_default', 'DESC');
        $this->db->order_by('provider_name', 'ASC');
        return $this->db->get('tbl_ai_providers')->result();
    }

    public function get_provider_by_code($provider_code)
    {
        return $this->db->where('provider_code', $provider_code)->get('tbl_ai_providers')->row();
    }

    public function get_provider_by_id($id)
    {
        return $this->db->where('id', (int) $id)->get('tbl_ai_providers')->row();
    }

    public function get_default_active_provider()
    {
        $provider = $this->db->where('is_active', 1)->where('is_default', 1)->get('tbl_ai_providers')->row();
        if (empty($provider)) {
            $provider = $this->db->where('is_active', 1)->order_by('id', 'ASC')->get('tbl_ai_providers')->row();
        }
        return $provider;
    }

    public function save_provider($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $id)->update('tbl_ai_providers', $data);
    }

    // ------------------------------------------------------------------------
    // Sessions
    // ------------------------------------------------------------------------

    public function create_session($user_id, $module_context = null)
    {
        $session_id = bin2hex(random_bytes(24));
        $this->db->insert('tbl_ai_sessions', array(
            'session_id'     => $session_id,
            'user_id'        => (int) $user_id,
            'title'          => '',
            'module_context' => $module_context,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ));
        return $session_id;
    }

    public function touch_session($session_id, $title = null, $module_context = null)
    {
        $data = array('updated_at' => date('Y-m-d H:i:s'));
        if ($title !== null) {
            $data['title'] = $title;
        }
        if ($module_context !== null) {
            $data['module_context'] = $module_context;
        }
        return $this->db->where('session_id', $session_id)->update('tbl_ai_sessions', $data);
    }

    public function get_session($session_id)
    {
        return $this->db->where('session_id', $session_id)->get('tbl_ai_sessions')->row();
    }

    public function get_user_sessions($user_id, $limit = 30)
    {
        return $this->db
            ->where('user_id', (int) $user_id)
            ->order_by('updated_at', 'DESC')
            ->limit((int) $limit)
            ->get('tbl_ai_sessions')
            ->result();
    }

    public function delete_session($session_id)
    {
        $this->db->where('session_id', $session_id)->delete('tbl_ai_messages');
        return $this->db->where('session_id', $session_id)->delete('tbl_ai_sessions');
    }

    // ------------------------------------------------------------------------
    // Pending actions (tool confirmation gate)
    // ------------------------------------------------------------------------

    public function set_pending_action($session_id, $action)
    {
        return $this->db->where('session_id', $session_id)->update('tbl_ai_sessions', array('pending_action' => json_encode($action)));
    }

    public function get_pending_action($session_id)
    {
        $row = $this->db->select('pending_action')->where('session_id', $session_id)->get('tbl_ai_sessions')->row();
        if (empty($row) || empty($row->pending_action)) {
            return null;
        }
        $data = json_decode($row->pending_action, true);
        return is_array($data) ? $data : null;
    }

    public function clear_pending_action($session_id)
    {
        return $this->db->where('session_id', $session_id)->update('tbl_ai_sessions', array('pending_action' => null));
    }

    // ------------------------------------------------------------------------
    // Messages
    // ------------------------------------------------------------------------

    public function add_message($session_id, $role, $content, $provider = null, $model = null, $tokens = 0)
    {
        return $this->db->insert('tbl_ai_messages', array(
            'session_id'    => $session_id,
            'role'          => $role,
            'content'       => $content,
            'provider_used' => $provider,
            'model_used'    => $model,
            'tokens_used'   => (int) $tokens,
            'created_at'    => date('Y-m-d H:i:s'),
        ));
    }

    public function get_session_messages($session_id, $limit = 30)
    {
        return $this->db
            ->where('session_id', $session_id)
            ->order_by('id', 'ASC')
            ->limit((int) $limit)
            ->get('tbl_ai_messages')
            ->result();
    }

    // ------------------------------------------------------------------------
    // Prompt templates
    // ------------------------------------------------------------------------

    public function get_prompts($category = null)
    {
        $this->db->where('status', 1);
        if (!empty($category)) {
            $this->db->where('category', $category);
        }
        $this->db->order_by('category', 'ASC');
        $this->db->order_by('prompt_id', 'ASC');
        return $this->db->get('tbl_ai_prompts')->result();
    }

    public function save_prompt($data, $id = null)
    {
        if (empty($id)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->db->insert('tbl_ai_prompts', $data);
        }
        return $this->db->where('prompt_id', (int) $id)->update('tbl_ai_prompts', $data);
    }

    public function delete_prompt($id)
    {
        return $this->db->where('prompt_id', (int) $id)->delete('tbl_ai_prompts');
    }
}
