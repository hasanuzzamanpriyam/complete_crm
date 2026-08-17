<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Consultation Model
 *
 * Handles database operations for consultants, weekly availability slots,
 * consultation appointments and consultation settings.
 */
class Consultation_model extends MY_Model
{
    public $_table_name = 'tbl_consultants';
    public $_primary_key = 'consultant_id';
    public $_order_by = 'name asc';

    public function __construct()
    {
        parent::__construct();
    }

    /* -----------------------------------------------------------------
     * Settings
     * ---------------------------------------------------------------- */

    /**
     * Get all consultation settings from tbl_config (consultation_ prefix)
     *
     * @return array
     */
    public function get_settings()
    {
        $this->db->like('config_key', 'consultation_', 'after');
        $rows = $this->db->get('tbl_config')->result();
        $settings = array();
        foreach ($rows as $row) {
            $key = str_replace('consultation_', '', $row->config_key);
            $settings[$key] = $row->value;
        }
        return $settings;
    }

    /**
     * Get a single consultation setting value
     *
     * @param string $key Setting key (without consultation_ prefix)
     * @param mixed $default Default value
     * @return mixed
     */
    public function get_setting($key, $default = null)
    {
        $row = $this->db->where('config_key', 'consultation_' . $key)->get('tbl_config')->row();
        return (!empty($row)) ? $row->value : $default;
    }

    /**
     * Save a single consultation setting value
     *
     * @param string $key Setting key (without consultation_ prefix)
     * @param string $value Value to store
     * @return void
     */
    public function set_setting($key, $value)
    {
        $this->db->where('config_key', 'consultation_' . $key);
        $exists = $this->db->count_all_results('tbl_config');

        $data = array('config_key' => 'consultation_' . $key, 'value' => $value);
        if ($exists > 0) {
            $this->db->where('config_key', 'consultation_' . $key);
            $this->db->update('tbl_config', array('value' => $value));
        } else {
            $this->db->insert('tbl_config', $data);
        }
    }

    /**
     * Save multiple settings at once
     *
     * @param array $settings key => value pairs (without consultation_ prefix)
     * @return void
     */
    public function save_settings($settings)
    {
        foreach ($settings as $key => $value) {
            $this->set_setting($key, $value);
        }
    }

    /* -----------------------------------------------------------------
     * Consultants
     * ---------------------------------------------------------------- */

    /**
     * Get consultants, optionally only active ones
     *
     * @param bool $active_only Only return active consultants
     * @return array
     */
    public function get_consultants($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        $this->db->order_by('name', 'asc');
        return $this->db->get('tbl_consultants')->result();
    }

    /**
     * Get a single consultant
     *
     * @param int $id Consultant id
     * @return object|null
     */
    public function get_consultant($id)
    {
        return $this->db->where('consultant_id', $id)->get('tbl_consultants')->row();
    }

    /**
     * Insert or update a consultant
     *
     * @param array $data Consultant fields
     * @param int|null $id Consultant id for update, null to insert
     * @return int Consultant id
     */
    public function save_consultant($data, $id = null)
    {
        if (empty($id)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_consultants', $data);
            return $this->db->insert_id();
        }
        $this->db->where('consultant_id', $id);
        $this->db->update('tbl_consultants', $data);
        return $id;
    }

    /**
     * Delete a consultant (also removes their slots and soft-cancels appointments)
     *
     * @param int $id Consultant id
     * @return void
     */
    public function delete_consultant($id)
    {
        $this->db->where('consultant_id', $id);
        $this->db->update('tbl_consultation_appointments', array('status' => 'cancelled'));
        $this->db->where('consultant_id', $id)->delete('tbl_consultation_slots');
        $this->db->where('consultant_id', $id)->delete('tbl_consultants');
    }

    /* -----------------------------------------------------------------
     * Weekly Slots
     * ---------------------------------------------------------------- */

    /**
     * Get availability slots for a consultant
     *
     * @param int $consultant_id Consultant id
     * @param bool $active_only Only return active slots
     * @return array
     */
    public function get_slots($consultant_id, $active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        $this->db->where('consultant_id', $consultant_id);
        $this->db->order_by('day_of_week', 'asc');
        $this->db->order_by('start_time', 'asc');
        return $this->db->get('tbl_consultation_slots')->result();
    }

    /**
     * Get a single slot
     *
     * @param int $id Slot id
     * @return object|null
     */
    public function get_slot($id)
    {
        return $this->db->where('slot_id', $id)->get('tbl_consultation_slots')->row();
    }

    /**
     * Insert or update a slot
     *
     * @param array $data Slot fields
     * @param int|null $id Slot id for update, null to insert
     * @return int Slot id
     */
    public function save_slot($data, $id = null)
    {
        if (empty($id)) {
            $this->db->insert('tbl_consultation_slots', $data);
            return $this->db->insert_id();
        }
        $this->db->where('slot_id', $id);
        $this->db->update('tbl_consultation_slots', $data);
        return $id;
    }

    /**
     * Delete a slot
     *
     * @param int $id Slot id
     * @return void
     */
    public function delete_slot($id)
    {
        $this->db->where('slot_id', $id)->delete('tbl_consultation_slots');
    }

    /**
     * Replace all weekly slots for a consultant with the provided list.
     * Each item must contain day_of_week, start_time, end_time, is_active.
     *
     * @param int $consultant_id Consultant id
     * @param array $slots List of slot rows
     * @return void
     */
    public function save_slots($consultant_id, $slots)
    {
        $this->db->where('consultant_id', $consultant_id)->delete('tbl_consultation_slots');

        if (!empty($slots)) {
            $rows = array();
            foreach ($slots as $slot) {
                if (empty($slot['start_time']) || empty($slot['end_time'])) {
                    continue;
                }
                $rows[] = array(
                    'consultant_id' => $consultant_id,
                    'day_of_week'   => intval($slot['day_of_week']),
                    'start_time'    => $this->_normalize_time_string($slot['start_time']),
                    'end_time'      => $this->_normalize_time_string($slot['end_time']),
                    'is_active'     => !empty($slot['is_active']) ? 1 : 0,
                );
            }
            if (!empty($rows)) {
                $this->db->insert_batch('tbl_consultation_slots', $rows);
            }
        }
    }

    /* -----------------------------------------------------------------
     * Appointments
     * ---------------------------------------------------------------- */

    /**
     * Get appointments with optional filters
     *
     * @param array $filters Filter keys: status, consultant_id, from_date, to_date, search
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function get_appointments($filters = array(), $limit = null, $offset = null)
    {
        $this->_apply_appointment_filters($filters);
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $this->db->select('tbl_consultation_appointments.*, COALESCE(tbl_consultants.name, "") as consultant_name, COALESCE(tbl_consultants.email, "") as consultant_email');
        $this->db->from('tbl_consultation_appointments');
        $this->db->join('tbl_consultants', 'tbl_consultants.consultant_id = tbl_consultation_appointments.consultant_id', 'left');
        $this->db->order_by('appointment_date', 'desc');
        $this->db->order_by('appointment_time', 'desc');
        return $this->db->get()->result();
    }

    /**
     * Count appointments matching filters
     *
     * @param array $filters Filter keys: status, consultant_id, from_date, to_date, search
     * @return int
     */
    public function count_appointments($filters = array())
    {
        $this->_apply_appointment_filters($filters);
        return $this->db->count_all_results('tbl_consultation_appointments');
    }

    /**
     * Get a single appointment with consultant details
     *
     * @param int $id Appointment id
     * @return object|null
     */
    public function get_appointment($id)
    {
        $this->db->select('tbl_consultation_appointments.*, tbl_consultants.name as consultant_name, tbl_consultants.email as consultant_email, tbl_consultants.timezone as consultant_timezone');
        $this->db->from('tbl_consultation_appointments');
        $this->db->join('tbl_consultants', 'tbl_consultants.consultant_id = tbl_consultation_appointments.consultant_id', 'left');
        $this->db->where('tbl_consultation_appointments.appointment_id', $id);
        return $this->db->get()->row();
    }

    /**
     * Get an appointment by cancel token
     *
     * @param string $token Cancel token
     * @return object|null
     */
    public function get_appointment_by_token($token)
    {
        $this->db->select('tbl_consultation_appointments.*, tbl_consultants.name as consultant_name, tbl_consultants.email as consultant_email, tbl_consultants.timezone as consultant_timezone');
        $this->db->from('tbl_consultation_appointments');
        $this->db->join('tbl_consultants', 'tbl_consultants.consultant_id = tbl_consultation_appointments.consultant_id', 'left');
        $this->db->where('tbl_consultation_appointments.cancel_token', $token);
        return $this->db->get()->row();
    }

    /**
     * Insert an appointment
     *
     * @param array $data Appointment fields
     * @return int Appointment id
     */
    public function create_appointment($data)
    {
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        if (empty($data['status'])) {
            $data['status'] = 'confirmed';
        }
        $this->db->insert('tbl_consultation_appointments', $data);
        return $this->db->insert_id();
    }

    /**
     * Update an appointment
     *
     * @param int $id Appointment id
     * @param array $data Fields to update
     * @return bool
     */
    public function update_appointment($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('appointment_id', $id);
        return $this->db->update('tbl_consultation_appointments', $data);
    }

    /**
     * Change appointment status
     *
     * @param int $id Appointment id
     * @param string $status pending|confirmed|completed|cancelled|no_show
     * @return bool
     */
    public function change_status($id, $status)
    {
        return $this->update_appointment($id, array('status' => $status));
    }

    /**
     * Get appointment count by status (for dashboard KPIs)
     *
     * @return array status => count
     */
    public function count_by_status()
    {
        $this->db->select('status, COUNT(*) as total');
        $this->db->group_by('status');
        $rows = $this->db->get('tbl_consultation_appointments')->result();
        $result = array('pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0);
        foreach ($rows as $row) {
            $result[$row->status] = (int)$row->total;
        }
        return $result;
    }

    /**
     * Upcoming confirmed appointments (within the next N hours) for reminders
     *
     * @param string $status Status to look for
     * @return array
     */
    public function get_upcoming_appointments($status = 'confirmed')
    {
        $this->db->select('tbl_consultation_appointments.*, tbl_consultants.name as consultant_name, tbl_consultants.email as consultant_email, tbl_consultants.timezone as consultant_timezone');
        $this->db->from('tbl_consultation_appointments');
        $this->db->join('tbl_consultants', 'tbl_consultants.consultant_id = tbl_consultation_appointments.consultant_id', 'left');
        $this->db->where('tbl_consultation_appointments.status', $status);
        $this->db->where('tbl_consultation_appointments.appointment_date >=', date('Y-m-d'));
        $this->db->order_by('tbl_consultation_appointments.appointment_date', 'asc');
        $this->db->order_by('tbl_consultation_appointments.appointment_time', 'asc');
        return $this->db->get()->result();
    }

    /* -----------------------------------------------------------------
     * Availability engine
     * ---------------------------------------------------------------- */

    /**
     * Get available time slots for a consultant on a given customer-local date.
     *
     * The customer selects a wall-clock date in their own timezone. Candidates
     * are derived from the consultant's weekly schedule (in the consultant's
     * timezone), converted back to the customer's timezone, and filtered against
     * past times, minimum advance notice and already-booked appointments
     * (including the configured buffer).
     *
     * @param int $consultant_id Consultant id
     * @param string $date Customer-local date (Y-m-d)
     * @param string $customer_tz Customer IANA timezone
     * @param int|null $duration Meeting duration in minutes
     * @return array List of slots: array('time','time_display','date','datetime')
     */
    public function get_available_slots($consultant_id, $date, $customer_tz, $duration = null)
    {
        $consultant = $this->get_consultant($consultant_id);
        if (empty($consultant) || (int)$consultant->is_active !== 1) {
            return array();
        }
        if (empty($duration) || (int)$duration <= 0) {
            $duration = (int)$this->get_setting('default_duration', 30);
        }
        if (empty($customer_tz)) {
            $customer_tz = consultation_company_timezone();
        }
        $consultant_tz = !empty($consultant->timezone) ? $consultant->timezone : consultation_company_timezone();
        $buffer = (int)$this->get_setting('buffer_minutes', 15);
        $min_advance = (int)$this->get_setting('min_advance_hours', 2);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return array();
        }

        // Anchor at customer-local midday to determine the consultant's working day
        // (avoids DST/date-boundary ambiguity while mapping the date).
        $customer_date = new DateTime($date . ' 12:00:00', new DateTimeZone($customer_tz));
        $consultant_date_obj = clone $customer_date;
        $consultant_date_obj->setTimezone(new DateTimeZone($consultant_tz));
        $consultant_weekday = (int)$consultant_date_obj->format('w');
        $consultant_date_str = $consultant_date_obj->format('Y-m-d');

        $slots = $this->get_slots($consultant_id, true);
        $day_slots = array();
        foreach ($slots as $slot) {
            if ((int)$slot->day_of_week === $consultant_weekday) {
                $day_slots[] = $slot;
            }
        }
        if (empty($day_slots)) {
            return array();
        }

        $booked = $this->_get_booked_intervals_utc($consultant_id, $consultant_date_str);

        $candidates = array();
        foreach ($day_slots as $slot) {
            try {
                $sdt = new DateTime($consultant_date_str . ' ' . $this->_normalize_time_string($slot->start_time) . ':00', new DateTimeZone($consultant_tz));
                $edt = new DateTime($consultant_date_str . ' ' . $this->_normalize_time_string($slot->end_time) . ':00', new DateTimeZone($consultant_tz));
            } catch (Exception $e) {
                continue;
            }
            $start = $sdt->getTimestamp();
            $end = $edt->getTimestamp();
            if ($end <= $start) {
                continue;
            }
            for ($t = $start; ($t + $duration * 60) <= $end; $t += 30 * 60) {
                $candidates[] = $t;
            }
        }

        $result = array();
        $seen = array();
        foreach ($candidates as $cand_ts) {
            $cand_local = new DateTime('@' . $cand_ts);
            $cand_local->setTimezone(new DateTimeZone($consultant_tz));
            $cand_customer = clone $cand_local;
            $cand_customer->setTimezone(new DateTimeZone($customer_tz));

            // Candidate must land on the customer's selected date
            if ($cand_customer->format('Y-m-d') !== $date) {
                continue;
            }

            $start_utc = $cand_local->getTimestamp();
            $end_utc = $start_utc + $duration * 60;

            // Not in the past, respecting minimum advance notice
            if ($start_utc < (time() + $min_advance * 3600)) {
                continue;
            }

            // Must not overlap a booked appointment (with buffer)
            $overlap = false;
            foreach ($booked as $iv) {
                if ($start_utc < ($iv['end'] + $buffer * 60) && $end_utc > ($iv['start'] - $buffer * 60)) {
                    $overlap = true;
                    break;
                }
            }
            if ($overlap) {
                continue;
            }

            $time_key = $cand_customer->format('H:i');
            if (isset($seen[$time_key])) {
                continue;
            }
            $seen[$time_key] = true;

            $result[] = array(
                'time'         => $time_key,
                'time_display' => $cand_customer->format('g:i A'),
                'date'         => $date,
                'datetime'     => $cand_customer->format('Y-m-d H:i'),
            );
        }

        usort($result, function ($a, $b) {
            return strcmp($a['time'], $b['time']);
        });

        return $result;
    }

    /**
     * Check whether a specific customer-local date/time slot is still available.
     * Used by the booking action to prevent double-booking.
     *
     * @param int $consultant_id Consultant id
     * @param string $date Customer-local date (Y-m-d)
     * @param string $time Customer-local time (H:i)
     * @param string $customer_tz Customer IANA timezone
     * @param int|null $duration Meeting duration in minutes
     * @return bool
     */
    public function is_slot_available($consultant_id, $date, $time, $customer_tz, $duration = null)
    {
        $consultant = $this->get_consultant($consultant_id);
        if (empty($consultant) || (int)$consultant->is_active !== 1) {
            return false;
        }
        if (empty($duration) || (int)$duration <= 0) {
            $duration = (int)$this->get_setting('default_duration', 30);
        }
        if (empty($customer_tz)) {
            $customer_tz = consultation_company_timezone();
        }
        $consultant_tz = !empty($consultant->timezone) ? $consultant->timezone : consultation_company_timezone();
        $buffer = (int)$this->get_setting('buffer_minutes', 15);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
            return false;
        }

        try {
            $dt = new DateTime($date . ' ' . $time . ':00', new DateTimeZone($customer_tz));
        } catch (Exception $e) {
            return false;
        }
        $start = $dt->getTimestamp();
        $end = $start + $duration * 60;

        // Must be in the future
        if ($start < time()) {
            return false;
        }

        // Must fall within the consultant's weekly schedule
        $c_dt = clone $dt;
        $c_dt->setTimezone(new DateTimeZone($consultant_tz));
        $weekday = (int)$c_dt->format('w');
        $cdate = $c_dt->format('Y-m-d');
        $slots = $this->get_slots($consultant_id, true);
        $in_slot = false;
        foreach ($slots as $slot) {
            if ((int)$slot->day_of_week !== $weekday) {
                continue;
            }
            try {
                $sdt = new DateTime($cdate . ' ' . $this->_normalize_time_string($slot->start_time) . ':00', new DateTimeZone($consultant_tz));
                $edt = new DateTime($cdate . ' ' . $this->_normalize_time_string($slot->end_time) . ':00', new DateTimeZone($consultant_tz));
            } catch (Exception $e) {
                continue;
            }
            if ($start >= $sdt->getTimestamp() && $end <= $edt->getTimestamp()) {
                $in_slot = true;
                break;
            }
        }
        if (!$in_slot) {
            return false;
        }

        // Must not overlap a booked appointment (with buffer)
        $booked = $this->_get_booked_intervals_utc($consultant_id, $cdate);
        foreach ($booked as $iv) {
            if ($start < ($iv['end'] + $buffer * 60) && $end > ($iv['start'] - $buffer * 60)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get available slots for a given customer-local date across ALL active
     * consultants. A slot is available if at least one consultant is free at
     * that time. This powers the consultant-agnostic public booking calendar.
     *
     * @param string $date Customer-local date (Y-m-d)
     * @param string $customer_tz Customer IANA timezone
     * @param int|null $duration Meeting duration in minutes
     * @param bool $include_booked Also return booked times (flagged available=false)
     * @return array List of slots: array('time','time_display','date','datetime','available')
     */
    public function get_consultant_agnostic_slots($date, $customer_tz, $duration = null, $include_booked = false)
    {
        $consultants = $this->get_consultants(true);
        if (empty($consultants)) {
            return array();
        }
        if (empty($duration) || (int)$duration <= 0) {
            $duration = (int)$this->get_setting('default_duration', 30);
        }
        if (empty($customer_tz)) {
            $customer_tz = consultation_company_timezone();
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return array();
        }

        $min_advance = (int)$this->get_setting('min_advance_hours', 2);
        $now = time();

        // Union of candidate UTC timestamps from every consultant's schedule.
        $candidates = array();
        foreach ($consultants as $c) {
            if ((int)$c->is_active !== 1) {
                continue;
            }
            $cid = (int)$c->consultant_id;
            $c_tz = !empty($c->timezone) ? $c->timezone : consultation_company_timezone();

            $customer_date = new DateTime($date . ' 12:00:00', new DateTimeZone($customer_tz));
            $c_date_obj = clone $customer_date;
            $c_date_obj->setTimezone(new DateTimeZone($c_tz));
            $weekday = (int)$c_date_obj->format('w');
            $cdate = $c_date_obj->format('Y-m-d');

            $slots = $this->get_slots($cid, true);
            foreach ($slots as $slot) {
                if ((int)$slot->day_of_week !== $weekday) {
                    continue;
                }
                try {
                    $sdt = new DateTime($cdate . ' ' . $this->_normalize_time_string($slot->start_time) . ':00', new DateTimeZone($c_tz));
                    $edt = new DateTime($cdate . ' ' . $this->_normalize_time_string($slot->end_time) . ':00', new DateTimeZone($c_tz));
                } catch (Exception $e) {
                    continue;
                }
                $start = $sdt->getTimestamp();
                $end = $edt->getTimestamp();
                if ($end <= $start) {
                    continue;
                }
                for ($t = $start; ($t + $duration * 60) <= $end; $t += 30 * 60) {
                    $candidates[$t] = true;
                }
            }
        }

        if (empty($candidates)) {
            return array();
        }

        $result = array();
        foreach (array_keys($candidates) as $utc_ts) {
            $dt_cust = new DateTime('@' . $utc_ts);
            $dt_cust->setTimezone(new DateTimeZone($customer_tz));
            if ($dt_cust->format('Y-m-d') !== $date) {
                continue;
            }

            $available = false;
            if ($utc_ts >= ($now + $min_advance * 3600)) {
                foreach ($consultants as $c) {
                    if ((int)$c->is_active !== 1) {
                        continue;
                    }
                    if ($this->is_slot_available((int)$c->consultant_id, $date, $dt_cust->format('H:i'), $customer_tz, $duration)) {
                        $available = true;
                        break;
                    }
                }
            }

            if (!$available && !$include_booked) {
                continue;
            }

            $result[] = array(
                'time'         => $dt_cust->format('H:i'),
                'time_display' => $dt_cust->format('g:i A'),
                'date'         => $date,
                'datetime'     => $dt_cust->format('Y-m-d H:i'),
                'available'    => $available,
            );
        }

        usort($result, function ($a, $b) {
            return strcmp($a['time'], $b['time']);
        });

        return $result;
    }

    /**
     * Get the list of dates (within a range) that have at least one available
     * slot across all consultants. Used to highlight the booking calendar.
     *
     * @param string $from Y-m-d start date
     * @param string $to Y-m-d end date
     * @param string $customer_tz Customer IANA timezone
     * @param int|null $duration Meeting duration in minutes
     * @return array List of Y-m-d date strings
     */
    public function get_available_dates($from, $to, $customer_tz, $duration = null)
    {
        $dates = array();
        $from_ts = strtotime($from);
        $to_ts = strtotime($to);
        if ($from_ts === false || $to_ts === false || $to_ts < $from_ts) {
            return $dates;
        }
        $days = min(92, (int)(($to_ts - $from_ts) / 86400) + 1);
        for ($i = 0; $i < $days; $i++) {
            $d = date('Y-m-d', strtotime('+' . $i . ' days', $from_ts));
            $slots = $this->get_consultant_agnostic_slots($d, $customer_tz, $duration);
            if (!empty($slots)) {
                $dates[] = $d;
            }
        }
        return $dates;
    }

    /**
     * Assign the first available consultant for a given customer-local slot.
     * Returns the consultant id, or 0 if nobody is free.
     *
     * @param string $date Customer-local date (Y-m-d)
     * @param string $time Customer-local time (H:i)
     * @param string $customer_tz Customer IANA timezone
     * @param int|null $duration Meeting duration in minutes
     * @return int
     */
    public function assign_consultant_for_slot($date, $time, $customer_tz, $duration = null)
    {
        if (empty($duration) || (int)$duration <= 0) {
            $duration = (int)$this->get_setting('default_duration', 30);
        }
        if (empty($customer_tz)) {
            $customer_tz = consultation_company_timezone();
        }
        $consultants = $this->get_consultants(true);
        if (empty($consultants)) {
            return 0;
        }
        foreach ($consultants as $c) {
            if ((int)$c->is_active !== 1) {
                continue;
            }
            $cid = (int)$c->consultant_id;
            if ($this->is_slot_available($cid, $date, $time, $customer_tz, $duration)) {
                return $cid;
            }
        }
        return 0;
    }

    /**
     * Get an appointment by its meeting room name (used for the public confirm page)
     *
     * @param string $room Meeting room name
     * @return object|null
     */
    public function get_appointment_by_room($room)
    {
        $this->db->select('tbl_consultation_appointments.*, tbl_consultants.name as consultant_name, tbl_consultants.email as consultant_email, tbl_consultants.department as consultant_department');
        $this->db->from('tbl_consultation_appointments');
        $this->db->join('tbl_consultants', 'tbl_consultants.consultant_id = tbl_consultation_appointments.consultant_id', 'left');
        $this->db->where('tbl_consultation_appointments.meeting_room', $room);
        return $this->db->get()->row();
    }

    /**
     * Get booked appointment intervals (UTC) for a consultant around a date,
     * used to detect overlaps when generating/validating slots.
     *
     * @param int $consultant_id Consultant id
     * @param string $center_date Center date (Y-m-d) in consultant timezone
     * @return array List of array('start','end') unix timestamps (UTC)
     */
    private function _get_booked_intervals_utc($consultant_id, $center_date)
    {
        $from = date('Y-m-d', strtotime($center_date . ' -2 days'));
        $to = date('Y-m-d', strtotime($center_date . ' +2 days'));

        $this->db->select('appointment_date, appointment_time, duration_minutes, customer_timezone');
        $this->db->where('consultant_id', $consultant_id);
        $this->db->where_in('status', array('pending', 'confirmed'));
        $this->db->where('appointment_date >=', $from);
        $this->db->where('appointment_date <=', $to);
        $rows = $this->db->get('tbl_consultation_appointments')->result();

        $intervals = array();
        foreach ($rows as $row) {
            $tz = !empty($row->customer_timezone) ? $row->customer_timezone : consultation_company_timezone();
            try {
                $dt = new DateTime($row->appointment_date . ' ' . $row->appointment_time, new DateTimeZone($tz));
            } catch (Exception $e) {
                continue;
            }
            $duration = (int)$row->duration_minutes;
            if ($duration <= 0) {
                $duration = 30;
            }
            $start = $dt->getTimestamp();
            $intervals[] = array('start' => $start, 'end' => $start + $duration * 60);
        }
        return $intervals;
    }

    /* -----------------------------------------------------------------
     * Private helpers
     * ---------------------------------------------------------------- */

    /**
     * Normalize a stored slot time to HH:MM (strips any seconds).
     *
     * @param string $time Slot time (e.g. "09:00" or "09:00:00")
     * @return string
     */
    private function _normalize_time_string($time)
    {
        $time = trim((string)$time);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{1,2})?$/', $time, $m)) {
            return sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
        }
        return $time;
    }

    private function _apply_appointment_filters($filters)
    {
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['consultant_id'])) {
            $this->db->where('tbl_consultation_appointments.consultant_id', $filters['consultant_id']);
        }
        if (!empty($filters['from_date'])) {
            $this->db->where('appointment_date >=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $this->db->where('appointment_date <=', $filters['to_date']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('customer_name', $filters['search']);
            $this->db->or_like('customer_email', $filters['search']);
            $this->db->or_like('meeting_room', $filters['search']);
            $this->db->group_end();
        }
    }
}
