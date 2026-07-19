<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Timesync_Teams extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Team_model');

        if (!is_super_admin()) {
            $user_id = (int)$this->session->userdata('user_id');
            $managed_ids = $this->Team_model->get_managed_team_ids($user_id);
            if (empty($managed_ids)) {
                redirect('404');
            }
        }
    }

    public function index()
    {
        $data['title'] = 'TimeSync Teams';

        // Pagination
        $page = max(1, (int)$this->input->get('page'));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $user_id = (int)$this->session->userdata('user_id');
        $is_super = is_super_admin();

        if ($is_super) {
            $total_teams = $this->db->count_all('tbl_teams');
            $teams = $this->Team_model->get_all_teams($per_page, $offset);
        } else {
            $managed_ids = $this->Team_model->get_managed_team_ids($user_id);
            $this->db->where_in('id', $managed_ids);
            $total_teams = $this->db->count_all_results('tbl_teams');
            $total_pages = max(1, ceil($total_teams / $per_page));
            $page = min($page, $total_pages);
            $offset = ($page - 1) * $per_page;
            $teams = $this->db->select('*')->from('tbl_teams')
                ->where_in('id', $managed_ids)
                ->order_by('name', 'ASC')
                ->limit($per_page, $offset)
                ->get()
                ->result();
        }

        $total_pages = max(1, ceil($total_teams / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

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
        $this->_render_or_ajax($data);
    }

    public function edit_member($user_id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/timesync_teams');
        }

        $teams = $this->Team_model->get_all_teams();

        $user_teams = $this->db
            ->select('team_id, is_manager')
            ->where('user_id', $user_id)
            ->where('status', 'approved')
            ->get('tbl_team_members')
            ->result();

        $user_team_ids = array_map(function ($t) {
            return (int)$t->team_id;
        }, $user_teams);

        $is_manager_teams = [];
        foreach ($user_teams as $t) {
            if ((int)$t->is_manager === 1) {
                $is_manager_teams[] = (int)$t->team_id;
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'teams' => $teams,
                'user_team_ids' => $user_team_ids,
                'is_manager_teams' => $is_manager_teams,
                'user_id' => (int)$user_id,
            ]));
    }

    public function save_member()
    {
        $user_id = (int)$this->input->post('user_id');
        $team_ids = $this->input->post('team_ids') ?: [];
        $is_manager = (int)$this->input->post('is_manager');

        $current_user_id = (int)$this->session->userdata('user_id');
        $is_super = is_super_admin();

        $current = $this->db
            ->select('team_id, is_manager')
            ->where('user_id', $user_id)
            ->where('status', 'approved')
            ->get('tbl_team_members')
            ->result();

        $current_ids = array_map(function ($m) {
            return (int)$m->team_id;
        }, $current);

        // Build per-team is_manager map to preserve existing values
        $current_manager_map = [];
        foreach ($current as $m) {
            $current_manager_map[(int)$m->team_id] = (int)$m->is_manager;
        }

        $manager_team_ids = [];
        foreach ($current as $m) {
            if ((int)$m->is_manager === 1) {
                $manager_team_ids[] = (int)$m->team_id;
            }
        }

        // Non-super-admins can only modify teams they manage
        $managed_ids = $is_super ? null : $this->Team_model->get_managed_team_ids($current_user_id);

        $to_keep = array_intersect($team_ids, $current_ids);
        $to_add = array_diff($team_ids, $current_ids);
        $to_remove = array_diff($current_ids, $team_ids);

        // Update existing memberships — preserve current is_manager (don't overwrite)
        foreach ($to_keep as $tid) {
            if (!$is_super && !in_array((int)$tid, $managed_ids)) {
                continue;
            }
            // Keep existing is_manager value; only update if status changed
        }

        // Add new memberships (UPSERT — handles re-adding 'left' members)
        // Apply the form's is_manager value only to newly assigned teams
        foreach ($to_add as $tid) {
            if (!$is_super && !in_array((int)$tid, $managed_ids)) {
                continue;
            }
            $this->db->where('team_id', (int)$tid)
                ->where('user_id', $user_id)
                ->update('tbl_team_members', [
                    'is_manager' => $is_manager,
                    'status' => 'approved',
                ]);
            if ($this->db->affected_rows() == 0) {
                $this->db->insert('tbl_team_members', [
                    'team_id' => (int)$tid,
                    'user_id' => $user_id,
                    'is_manager' => $is_manager,
                    'status' => 'approved',
                ]);
            }
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

    public function delete($id)
    {
        $team = $this->db->where('id', $id)->get('tbl_teams')->row();
        if (!$team) {
            set_message('error', 'Team not found.');
            redirect('admin/timesync_teams');
        }

        if (!is_super_admin()) {
            $user_id = (int)$this->session->userdata('user_id');
            $managed_ids = $this->Team_model->get_managed_team_ids($user_id);
            if (!in_array((int)$id, $managed_ids)) {
                set_message('error', 'You do not have permission to delete this team.');
                redirect('admin/timesync_teams');
            }
        }

        $this->db->where('team_id', $id)->delete('tbl_team_members');
        $this->db->where('team_id', $id)->delete('tbl_team_messages');
        $this->db->where('id', $id)->delete('tbl_teams');

        set_message('success', 'Team "' . htmlspecialchars($team->name) . '" deleted successfully.');
        redirect('admin/timesync_teams');
    }

    public function create()
    {
        $name = trim($this->input->post('name'));
        $description = trim($this->input->post('description') ?? '');

        if (empty($name)) {
            set_message('error', 'Team name is required.');
            redirect('admin/timesync_teams');
        }

        $user_id = (int)$this->session->userdata('user_id');

        $this->db->insert('tbl_teams', [
            'name' => $name,
            'description' => $description,
            'created_by' => $user_id,
        ]);
        $team_id = $this->db->insert_id();

        $this->db->insert('tbl_team_members', [
            'team_id' => $team_id,
            'user_id' => $user_id,
            'is_manager' => 1,
            'status' => 'approved',
        ]);

        set_message('success', 'Team "' . htmlspecialchars($name) . '" created successfully.');
        redirect('admin/timesync_teams');
    }

    public function readd_member($team_id, $user_id)
    {
        $this->db->where('team_id', (int)$team_id)
            ->where('user_id', (int)$user_id)
            ->update('tbl_team_members', ['status' => 'approved', 'is_manager' => 0]);

        if ($this->db->affected_rows() > 0) {
            set_message('success', 'Member re-added to the team.');
        } else {
            set_message('error', 'Member not found or already active.');
        }
        redirect('admin/timesync_teams');
    }

    public function toggle_manager()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/timesync_teams');
        }

        $team_id = (int)$this->input->post('team_id');
        $user_id = (int)$this->input->post('user_id');
        $current_user_id = (int)$this->session->userdata('user_id');
        $is_super = is_super_admin();

        if (!$team_id || !$user_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Invalid request.']));
        }

        if (!$is_super) {
            $managed_ids = $this->Team_model->get_managed_team_ids($current_user_id);
            if (!in_array($team_id, $managed_ids)) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'Permission denied.']));
            }
        }

        $member = $this->db
            ->where('team_id', $team_id)
            ->where('user_id', $user_id)
            ->where('status', 'approved')
            ->get('tbl_team_members')
            ->row();

        if (!$member) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Member not found.']));
        }

        $new_val = (int)$member->is_manager ? 0 : 1;
        $this->db->where('team_id', $team_id)
            ->where('user_id', $user_id)
            ->update('tbl_team_members', ['is_manager' => $new_val]);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'is_manager' => $new_val,
                'message' => $new_val ? 'Member promoted to manager.' : 'Member demoted from manager.',
            ]));
    }

    private function _render_or_ajax($data)
    {
        if ($this->input->get('ajax')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'html'  => $data['subview'],
                    'title' => $data['title'] ?? '',
                ]));
            return;
        }
        $this->load->view('admin/_layout_main', $data);
    }
}
