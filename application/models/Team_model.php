<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Team_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_teams_for_user($user_id)
    {
        return $this->db->select('t.*')
            ->from('tbl_teams t')
            ->join('tbl_team_members tm', 'tm.team_id = t.id')
            ->where('tm.user_id', $user_id)
            ->where('tm.status', 'approved')
            ->get()
            ->result();
    }

    public function get_all_teams()
    {
        return $this->db->get('tbl_teams')->result();
    }

    public function get_team($team_id)
    {
        return $this->db->where('id', $team_id)->get('tbl_teams')->row();
    }

    public function is_team_manager($user_id, $team_id)
    {
        return $this->db->where('team_id', $team_id)
            ->where('user_id', $user_id)
            ->where('is_manager', 1)
            ->where('status', 'approved')
            ->get('tbl_team_members')
            ->num_rows() > 0;
    }

    public function is_team_member($user_id, $team_id)
    {
        return $this->db->where('team_id', $team_id)
            ->where('user_id', $user_id)
            ->where('status', 'approved')
            ->get('tbl_team_members')
            ->num_rows() > 0;
    }

    public function get_team_member_ids($team_id)
    {
        $members = $this->db->select('user_id')
            ->where('team_id', $team_id)
            ->where('status', 'approved')
            ->get('tbl_team_members')
            ->result();
        return array_map(function($m) { return (int)$m->user_id; }, $members);
    }

    public function get_managed_team_ids($user_id)
    {
        $teams = $this->db->select('team_id')
            ->where('user_id', $user_id)
            ->where('is_manager', 1)
            ->where('status', 'approved')
            ->get('tbl_team_members')
            ->result();
        return array_map(function($t) { return (int)$t->team_id; }, $teams);
    }

    public function get_team_members_with_users($team_id, $status = 'approved')
    {
        $this->db->select('tm.*, u.username, u.email')
            ->from('tbl_team_members tm')
            ->join('tbl_users u', 'u.user_id = tm.user_id')
            ->where('tm.team_id', $team_id);
        if ($status !== null) {
            $this->db->where('tm.status', $status);
        }
        return $this->db->get()->result();
    }

    public function get_pending_members($team_id)
    {
        return $this->get_team_members_with_users($team_id, 'pending');
    }

    public function get_user_pending_requests($user_id)
    {
        return $this->db->select('tm.*, t.name as team_name')
            ->from('tbl_team_members tm')
            ->join('tbl_teams t', 't.id = tm.team_id')
            ->where('tm.user_id', $user_id)
            ->where('tm.status', 'pending')
            ->get()
            ->result();
    }

    public function add_message($team_id, $user_id, $message)
    {
        $this->db->insert('tbl_team_messages', [
            'team_id' => $team_id,
            'user_id' => $user_id,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function get_messages($team_id, $since = null)
    {
        $this->db->select('tm.*, u.username, u.full_name')
            ->from('tbl_team_messages tm')
            ->join('tbl_users u', 'u.user_id = tm.user_id')
            ->where('tm.team_id', $team_id)
            ->order_by('tm.created_at', 'ASC')
            ->limit(200);
        if ($since) {
            $this->db->where('tm.created_at >', $since);
        }
        return $this->db->get()->result();
    }

    public function get_team_members_by_username($team_id, $username)
    {
        return $this->db->select('u.user_id, u.username')
            ->from('tbl_team_members tm')
            ->join('tbl_users u', 'u.user_id = tm.user_id')
            ->where('tm.team_id', $team_id)
            ->where('tm.status', 'approved')
            ->like('u.username', $username)
            ->get()
            ->result();
    }

    public function resolve_username($username)
    {
        return $this->db->select('user_id, username')
            ->where('username', $username)
            ->get('tbl_users')
            ->row();
    }
}
