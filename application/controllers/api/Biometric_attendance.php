<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Biometric_attendance extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('attendance_model');
    }

    /**
     * Endpoint to receive biometric logs from Node.js middleware
     */
    public function sync()
    {
        // 0. Auto clock out users who forgot to sign out (after 8 hours)
        $this->auto_clock_out();

        // Simple token check (You can improve this)
        $token = $this->input->get_request_header('X-API-TOKEN');
        $valid_token = config_item('biometric_api_token') ?: 'zkteco_sync_token_123';

        if ($token !== $valid_token) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['logs']) || !is_array($input['logs'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid input']));
        }

        $processed_count = 0;
        $ignored_count = 0;

        foreach ($input['logs'] as $log) {
            $device_user_id = $log['deviceUserId'];
            $timestamp = isset($log['recordTime']) ? $log['recordTime'] : null;
            $status = isset($log['state']) ? $log['state'] : 0;

            // Log what we're receiving from the device (for debugging)
            if (empty($timestamp)) {
                error_log("[BIOMETRIC_DEBUG] Empty timestamp from device for user_id: $device_user_id - Full log: " . json_encode($log));
            }

            // Validate timestamp robustly:
            // 1. Check if empty or contains obvious invalid values
            // 2. Check if strtotime() can parse it (returns false if invalid)
            // 3. Check if the parsed date is reasonable (after year 2000)
            $parsed_time = strtotime($timestamp);

            if (empty($timestamp) || strpos($timestamp, '0000-00-00') !== false || $parsed_time === false || $parsed_time < strtotime('2000-01-01')) {
                error_log("[BIOMETRIC_DEBUG] Invalid or missing device timestamp for user_id: $device_user_id - timestamp: " . var_export($timestamp, true) . "; fallback to server time.");
                $timestamp = date('Y-m-d H:i:s');
                $parsed_time = strtotime($timestamp);
            }

            // 1. Save to raw logs table (duplicate check via UNIQUE key)
            $this->db->db_debug = FALSE; // Disable debug to handle duplicate errors gracefully
            $log_data = [
                'device_user_id' => $device_user_id,
                'timestamp'      => $timestamp,
                'status'         => $status
            ];

            if ($this->db->insert('biometric_attendance_logs', $log_data)) {
                // 2. Map device_user_id to ZiscoERP user_id
                $mapping = $this->db->get_where('biometric_employee_mapping', ['device_user_id' => $device_user_id])->row();

                if ($mapping) {
                    $this->process_attendance($mapping->user_id, $timestamp);
                    $processed_count++;

                    // Mark as processed in raw logs
                    $this->db->where('id', $this->db->insert_id());
                    $this->db->update('biometric_attendance_logs', ['processed' => 1]);
                } else {
                    $ignored_count++;
                }
            } else {
                $ignored_count++;
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => 'success',
                'processed' => $processed_count,
                'ignored_or_duplicate' => $ignored_count
            ]));
    }

    /**
     * Logic to update tbl_attendance and tbl_clock
     */
    private function process_attendance($user_id, $timestamp)
    {
        $date_in = date('Y-m-d', strtotime($timestamp));
        $time_in = date('H:i:s', strtotime($timestamp));

        // Check if attendance record exists for this day
        $check_attendance = $this->db->get_where('tbl_attendance', [
            'user_id' => $user_id,
            'date_in' => $date_in
        ])->row();

        if (!$check_attendance) {
            // Create new attendance record
            $adata = [
                'user_id' => $user_id,
                'date_in' => $date_in,
                'attendance_status' => 1,
                'clocking_status' => 0 // 0 means clocked out, we will update it
            ];
            $this->db->insert('tbl_attendance', $adata);
            $attendance_id = $this->db->insert_id();
        } else {
            $attendance_id = $check_attendance->attendance_id;
        }

        // Check last clocking status for this attendance
        $last_clock = $this->db->order_by('clock_id', 'DESC')
            ->get_where('tbl_clock', ['attendance_id' => $attendance_id])
            ->row();

        if (!$last_clock || $last_clock->clocking_status == 0) {
            // This is a Clock In
            $clock_data = [
                'attendance_id' => $attendance_id,
                'clockin_time' => $time_in,
                'clocking_status' => 1,
                'ip_address' => 'biometric'
            ];
            $this->db->insert('tbl_clock', $clock_data);

            // Update attendance record status
            $this->db->where('attendance_id', $attendance_id);
            $this->db->update('tbl_attendance', ['clocking_status' => 1]);
        } else {
            // This is a Clock Out
            $this->db->where('clock_id', $last_clock->clock_id);
            $this->db->update('tbl_clock', [
                'clockout_time' => $time_in,
                'clocking_status' => 0
            ]);

            // Update attendance record status
            $this->db->where('attendance_id', $attendance_id);
            $this->db->update('tbl_attendance', ['clocking_status' => 0, 'date_out' => $date_in]);
        }
    }
    /**
     * Cleanup endpoint to remove invalid biometric logs
     * Call with: /api/biometric_attendance/cleanup_invalid_logs?token=YOUR_TOKEN
     */
    public function cleanup_invalid_logs()
    {
        // Token check for security
        $token = $this->input->get('token');
        $valid_token = config_item('biometric_api_token') ?: 'zkteco_sync_token_123';

        if ($token !== $valid_token) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
        }

        // Disable strict datetime validation in this session so zero dates can be removed safely
        $this->db->query("SET SESSION sql_mode = ''");

        // Delete rows with invalid timestamps
        $this->db->where('timestamp', '0000-00-00 00:00:00');
        $count_zeros = $this->db->delete('biometric_attendance_logs');

        $this->db->where('timestamp <', '2000-01-01');
        $this->db->where('timestamp !=', '0000-00-00 00:00:00');
        $count_old = $this->db->delete('biometric_attendance_logs');

        $this->db->where('timestamp IS NULL');
        $count_null = $this->db->delete('biometric_attendance_logs');

        $total_deleted = $count_zeros + $count_old + $count_null;

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Cleanup completed',
                'deleted_count' => $total_deleted,
                'details' => [
                    'zero_timestamps' => $count_zeros,
                    'old_dates_before_2000' => $count_old,
                    'null_timestamps' => $count_null
                ]
            ]));
    }

    /**
     * Automatically clock out users who have been clocked in for more than 8 hours
     */
    private function auto_clock_out()
    {
        // Find all active clock-ins
        $this->db->select('tbl_clock.*, tbl_attendance.date_in, tbl_attendance.user_id');
        $this->db->from('tbl_clock');
        $this->db->join('tbl_attendance', 'tbl_attendance.attendance_id = tbl_clock.attendance_id');
        $this->db->where('tbl_clock.clocking_status', 1);
        $query = $this->db->get();
        $active_clocks = $query->result();

        foreach ($active_clocks as $clock) {
            $clock_in_time = strtotime($clock->date_in . ' ' . $clock->clockin_time);
            $current_time = time();
            $diff_hours = ($current_time - $clock_in_time) / 3600;

            // If clocked in for more than 8 hours, auto clock out
            if ($diff_hours >= 8) {
                $auto_clock_out_time = date('H:i:s', $clock_in_time + (8 * 3600));

                // Update tbl_clock
                $this->db->where('clock_id', $clock->clock_id);
                $this->db->update('tbl_clock', [
                    'clockout_time' => $auto_clock_out_time,
                    'clocking_status' => 0
                ]);

                // Update tbl_attendance
                $this->db->where('attendance_id', $clock->attendance_id);
                $this->db->update('tbl_attendance', [
                    'clocking_status' => 0,
                    'date_out' => $clock->date_in // Assuming they sign out same day
                ]);
            }
        }
    }

    /**
     * Get latest biometric logs since a given ID (for real-time display updates)
     */
    public function get_latest_logs()
    {
        $after_id = $this->input->get('after_id') ? intval($this->input->get('after_id')) : 0;

        $this->db->select('biometric_attendance_logs.id, biometric_attendance_logs.device_user_id, biometric_attendance_logs.timestamp, biometric_attendance_logs.status, biometric_attendance_logs.processed, biometric_attendance_logs.created_at, tbl_account_details.fullname');
        $this->db->from('biometric_attendance_logs');
        $this->db->join('biometric_employee_mapping', 'biometric_employee_mapping.device_user_id = biometric_attendance_logs.device_user_id', 'left');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = biometric_employee_mapping.user_id', 'left');
        $this->db->where('biometric_attendance_logs.id >', $after_id);
        $this->db->order_by('biometric_attendance_logs.id', 'DESC');
        $this->db->limit(50);

        $logs = $this->db->get()->result();

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => 'success',
                'logs' => $logs
            ]));
    }
}
