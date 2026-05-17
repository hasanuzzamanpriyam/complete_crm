<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Jitsi Model
 *
 * Handles database operations for Jitsi meetings
 */
class Jitsi_model extends MY_Model
{
    public $_table_name = 'tbl_jitsi_meetings';
    public $_primary_key = 'jitsi_meeting_id';
    public $_order_by = 'jitsi_meeting_id desc';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all meetings with optional filters
     */
    public function get_all_meetings($where = [])
    {
        $this->db->select('tbl_jitsi_meetings.*, tbl_account_details.fullname as host_name')
            ->from('tbl_jitsi_meetings')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_jitsi_meetings.host', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db->get()->result();
    }

    /**
     * Get meeting by ID with host details
     */
    public function get_meeting_detail($id)
    {
        $this->db->select('tbl_jitsi_meetings.*, tbl_account_details.fullname as host_name')
            ->from('tbl_jitsi_meetings')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_jitsi_meetings.host', 'left')
            ->where('tbl_jitsi_meetings.jitsi_meeting_id', $id);

        return $this->db->get()->row();
    }

    /**
     * Get meetings for a specific user (staff)
     */
    public function get_user_meetings($user_id)
    {
        $meetings = $this->get_all_meetings();
        $user_meetings = [];

        foreach ($meetings as $meeting) {
            $invited_users = json_decode($meeting->user_id, true);
            if (!empty($invited_users) && is_array($invited_users)) {
                if (in_array($user_id, $invited_users) || $meeting->host == $user_id) {
                    $user_meetings[] = $meeting;
                }
            }
        }

        return $user_meetings;
    }

    /**
     * Get meetings for a specific client
     */
    public function get_client_meetings($client_id)
    {
        $meetings = $this->get_all_meetings();
        $client_meetings = [];

        foreach ($meetings as $meeting) {
            $invited_clients = json_decode($meeting->client_id, true);
            if (!empty($invited_clients) && is_array($invited_clients)) {
                if (in_array($client_id, $invited_clients)) {
                    $client_meetings[] = $meeting;
                }
            }
        }

        return $client_meetings;
    }

    /**
     * Generate a unique room name
     */
    public function generate_room_name()
    {
        return 'tic-crm-' . date('Ymd') . '-' . bin2hex(random_bytes(8));
    }

    /**
     * Get upcoming meetings (meeting_time > now)
     */
    public function get_upcoming_meetings()
    {
        return $this->db->where('meeting_time >', date('Y-m-d H:i:s'))
            ->where('status', 'waiting')
            ->order_by('meeting_time', 'ASC')
            ->get($this->_table_name)
            ->result();
    }
}
