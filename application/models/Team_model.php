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
            ->get('tbl_team_members')
            ->num_rows() > 0;
    }

    public function is_team_member($user_id, $team_id)
    {
        return $this->db->where('team_id', $team_id)
            ->where('user_id', $user_id)
            ->get('tbl_team_members')
            ->num_rows() > 0;
    }

    public function get_team_member_ids($team_id)
    {
        $members = $this->db->select('user_id')
            ->where('team_id', $team_id)
            ->get('tbl_team_members')
            ->result();
        return array_map(function($m) { return (int)$m->user_id; }, $members);
    }

    public function get_managed_team_ids($user_id)
    {
        $teams = $this->db->select('team_id')
            ->where('user_id', $user_id)
            ->where('is_manager', 1)
            ->get('tbl_team_members')
            ->result();
        return array_map(function($t) { return (int)$t->team_id; }, $teams);
    }
}
