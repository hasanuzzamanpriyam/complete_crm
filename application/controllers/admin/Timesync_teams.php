<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Timesync_Teams extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Team_model');

        if (!is_super_admin()) {
            redirect('404');
        }
    }

    public function index()
    {
        $data['title'] = 'TimeSync Teams';

        // Pagination
        $page = max(1, (int)$this->input->get('page'));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        $total_teams = $this->db->count_all('tbl_teams');
        $total_pages = max(1, ceil($total_teams / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $teams = $this->Team_model->get_all_teams($per_page, $offset);

        $team_members = [];
        foreach ($teams as $team) {
            $members = $this->db
                ->select('tm.*, ad.fullname')
                ->from('tbl_team_members tm')
                ->join('tbl_account_details ad', 'ad.user_id = tm.user_id', 'left')
                ->where('tm.team_id', $team->id)
                ->order_by('ad.fullname', 'ASC')
                ->get()
                ->result();
            $team_members[$team->id] = $members;
        }

        $all_users = $this->db
            ->select('u.user_id, ad.fullname')
            ->from('tbl_users u')
            ->join('tbl_account_details ad', 'ad.user_id = u.user_id', 'left')
            ->where('u.activated', 1)
            ->order_by('ad.fullname', 'ASC')
            ->get()
            ->result();

        // Chart data: top 10 teams by member count
        $team_sizes = $this->db
            ->select('t.name, COUNT(tm.id) as member_count')
            ->from('tbl_teams t')
            ->join('tbl_team_members tm', 'tm.team_id = t.id')
            ->group_by('t.id')
            ->order_by('member_count', 'DESC')
            ->limit(10)
            ->get()
            ->result();
        $data['chart_team_labels'] = json_encode(array_map(function($r) { return $r->name; }, $team_sizes));
        $data['chart_team_values'] = json_encode(array_map(function($r) { return (int)$r->member_count; }, $team_sizes));

        // Status breakdown across all teams
        $status_counts = $this->db
            ->select('status, COUNT(*) as cnt')
            ->from('tbl_team_members')
            ->group_by('status')
            ->get()
            ->result();
        $data['chart_status_labels'] = json_encode(array_map(function($r) { return $r->status; }, $status_counts));
        $data['chart_status_values'] = json_encode(array_map(function($r) { return (int)$r->cnt; }, $status_counts));

        // Global counts for stat cards
        $data['total_teams_all'] = $this->db->reset_query()->count_all('tbl_teams');
        $data['total_members_all'] = $this->db->reset_query()->count_all('tbl_team_members');
        $data['pending_count_all'] = $this->db->reset_query()->where('status', 'pending')->count_all_results('tbl_team_members');

        $data['teams'] = $teams;
        $data['team_members'] = $team_members;
        $data['all_users'] = $all_users;
        $data['page'] = $page;
        $data['total_pages'] = $total_pages;
        $data['per_page'] = $per_page;

        $data['subview'] = $this->load->view('admin/timesync/teams', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function edit_member($user_id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/timesync_teams');
        }

        $teams = $this->Team_model->get_all_teams();

        $user_teams = $this->db
            ->select('team_id')
            ->where('user_id', $user_id)
            ->where('status', 'approved')
            ->get('tbl_team_members')
            ->result();

        $user_team_ids = array_map(function ($t) {
            return (int)$t->team_id;
        }, $user_teams);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'teams' => $teams,
                'user_team_ids' => $user_team_ids,
                'user_id' => (int)$user_id,
            ]));
    }

    public function save_member()
    {
        $user_id = (int)$this->input->post('user_id');
        $team_ids = $this->input->post('team_ids') ?: [];

        $current = $this->db
            ->select('team_id, is_manager')
            ->where('user_id', $user_id)
            ->where('status', 'approved')
            ->get('tbl_team_members')
            ->result();

        $current_ids = array_map(function ($m) {
            return (int)$m->team_id;
        }, $current);

        $manager_team_ids = [];
        foreach ($current as $m) {
            if ((int)$m->is_manager === 1) {
                $manager_team_ids[] = (int)$m->team_id;
            }
        }

        $to_add = array_diff($team_ids, $current_ids);
        $to_remove = array_diff($current_ids, $team_ids);

        foreach ($to_add as $tid) {
            $this->db->insert('tbl_team_members', [
                'team_id' => (int)$tid,
                'user_id' => $user_id,
                'is_manager' => 0,
                'status' => 'approved',
            ]);
        }

        $skipped = [];
        foreach ($to_remove as $tid) {
            if (in_array($tid, $manager_team_ids)) {
                $skipped[] = $tid;
                continue;
            }
            $this->db->where('team_id', (int)$tid)
                ->where('user_id', $user_id)
                ->where('status', 'approved')
                ->update('tbl_team_members', ['status' => 'left']);
        }

        $msg = 'Team memberships updated successfully.';
        if (!empty($skipped)) {
            $team_names = $this->db
                ->select('name')
                ->where_in('id', $skipped)
                ->get('tbl_teams')
                ->result();
            $names = array_map(function ($t) { return $t->name; }, $team_names);
            $msg .= ' Could not remove from (user is manager): ' . implode(', ', $names) . '.';
        }

        set_message('success', $msg);
        redirect('admin/timesync_teams');
    }
}
