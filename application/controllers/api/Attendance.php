<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Attendance extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function index()
    {
        $user = $this->api_auth->authenticate();
        $user_id = $user->user_id;

        $from = $this->input->get('from');
        $to = $this->input->get('to');
        $limit = (int)$this->input->get('limit') ?: 30;

        $this->db->where('user_id', $user_id);
        if (!empty($from)) $this->db->where('date_in >=', $from);
        if (!empty($to)) $this->db->where('date_in <=', $to);
        $this->db->order_by('date_in', 'DESC');
        $this->db->limit($limit);
        $attendance = $this->db->get('tbl_attendance')->result();

        $result = array_map(function ($a) {
            $clocks = $this->db
                ->where('attendance_id', $a->attendance_id)
                ->order_by('clock_id', 'ASC')
                ->get('tbl_clock')
                ->result();

            $total_hours = 0;
            $clock_data = [];
            foreach ($clocks as $c) {
                $clock_data[] = [
                    'clockin_time' => $c->clockin_time,
                    'clockout_time' => $c->clockout_time,
                ];
                if (!empty($c->clockin_time) && !empty($c->clockout_time)) {
                    $in = strtotime($c->clockin_time);
                    $out = strtotime($c->clockout_time);
                    $total_hours += ($out - $in) / 3600;
                }
            }

            return [
                'date' => $a->date_in,
                'status' => $a->attendance_status == 1 ? 'present' : ($a->attendance_status == 2 ? 'late' : 'absent'),
                'clocking_status' => $a->clocking_status == 1 ? 'clocked_in' : 'clocked_out',
                'clocks' => $clock_data,
                'total_hours' => round($total_hours, 2),
            ];
        }, $attendance);

        return $this->_respond(200, true, 'OK', ['attendance' => $result]);
    }

    public function check_in()
    {
        $user = $this->api_auth->authenticate();
        $user_id = $user->user_id;
        $date_in = date('Y-m-d');

        $existing = $this->db
            ->where('user_id', $user_id)
            ->where('date_in', $date_in)
            ->get('tbl_attendance')
            ->row();

        if ($existing) {
            $last_clock = $this->db
                ->where('attendance_id', $existing->attendance_id)
                ->order_by('clock_id', 'DESC')
                ->get('tbl_clock')
                ->row();

            if ($last_clock && $last_clock->clocking_status == 1) {
                return $this->_respond(400, false, 'Already clocked in');
            }

            $this->db->insert('tbl_clock', [
                'attendance_id' => $existing->attendance_id,
                'clockin_time' => date('H:i:s'),
                'clocking_status' => 1,
                'ip_address' => $this->input->ip_address(),
            ]);

            $this->db->where('attendance_id', $existing->attendance_id)
                ->update('tbl_attendance', ['clocking_status' => 1, 'attendance_status' => 1]);

            return $this->_respond(200, true, 'Checked in', [
                'attendance_id' => (int)$existing->attendance_id,
                'clock_id' => $this->db->insert_id(),
            ]);
        }

        $this->db->insert('tbl_attendance', [
            'user_id' => $user_id,
            'date_in' => $date_in,
            'attendance_status' => 1,
            'clocking_status' => 1,
        ]);
        $attendance_id = $this->db->insert_id();

        $this->db->insert('tbl_clock', [
            'attendance_id' => $attendance_id,
            'clockin_time' => date('H:i:s'),
            'clocking_status' => 1,
            'ip_address' => $this->input->ip_address(),
        ]);

        return $this->_respond(200, true, 'Checked in', [
            'attendance_id' => (int)$attendance_id,
            'clock_id' => $this->db->insert_id(),
        ]);
    }

    public function check_out()
    {
        $user = $this->api_auth->authenticate();
        $user_id = $user->user_id;
        $date_in = date('Y-m-d');

        $attendance = $this->db
            ->where('user_id', $user_id)
            ->where('date_in', $date_in)
            ->get('tbl_attendance')
            ->row();

        if (empty($attendance)) {
            return $this->_respond(400, false, 'No check-in found for today');
        }

        $last_clock = $this->db
            ->where('attendance_id', $attendance->attendance_id)
            ->order_by('clock_id', 'DESC')
            ->get('tbl_clock')
            ->row();

        if (empty($last_clock) || $last_clock->clocking_status == 0) {
            return $this->_respond(400, false, 'Not currently checked in');
        }

        $this->db->where('clock_id', $last_clock->clock_id)
            ->update('tbl_clock', [
                'clockout_time' => date('H:i:s'),
                'clocking_status' => 0,
            ]);

        $this->db->where('attendance_id', $attendance->attendance_id)
            ->update('tbl_attendance', [
                'clocking_status' => 0,
                'date_out' => $date_in,
            ]);

        return $this->_respond(200, true, 'Checked out');
    }

    private function _respond($status_code, $success, $message, $data = null)
    {
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $response = array_merge($response, $data);
        }
        return $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
