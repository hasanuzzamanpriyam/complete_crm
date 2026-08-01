<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('consultation_config')) {
    /**
     * Get consultation setting from application config (loaded from tbl_config)
     *
     * @param string $key Setting key (without consultation_ prefix)
     * @param mixed $default Default value if unset
     * @return mixed
     */
    function consultation_config($key, $default = null)
    {
        $value = config_item('consultation_' . $key);
        return ($value === null || $value === '') ? $default : $value;
    }
}

if (!function_exists('consultation_generate_room')) {
    /**
     * Generate a unique Jitsi meeting room name
     *
     * @return string
     */
    function consultation_generate_room()
    {
        return 'tic-crm-' . date('Ymd') . '-' . bin2hex(random_bytes(8));
    }
}

if (!function_exists('consultation_generate_password')) {
    /**
     * Generate a random meeting password
     *
     * @param int $length Password length
     * @return string
     */
    function consultation_generate_password($length = 10)
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $password = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        return $password;
    }
}

if (!function_exists('consultation_build_meeting_url')) {
    /**
     * Build a Jitsi meeting URL for a participant.
     * Uses the Jitsi module helpers when installed and configured,
     * otherwise falls back to a plain meet.jit.si URL.
     *
     * @param string $room Meeting room name
     * @param string $email Participant email
     * @param string $name Participant display name
     * @param bool $is_moderator Whether user is the moderator/host
     * @param int $exp Expiration timestamp for JWT (default: +2 hours)
     * @return string Full meeting URL
     */
    function consultation_build_meeting_url($room, $email = '', $name = '', $is_moderator = false, $exp = null)
    {
        if (function_exists('is_jitsi_configured') && is_jitsi_configured()) {
            if ($exp === null) {
                $exp = time() + (2 * 60 * 60);
            }
            return build_jitsi_url($room, $email, $name, $is_moderator, $exp);
        }

        $domain = config_item('jitsi_domain') ? rtrim(config_item('jitsi_domain'), '/') : 'https://meet.jit.si';
        return $domain . '/' . rawurlencode($room);
    }
}

if (!function_exists('consultation_build_guest_url')) {
    /**
     * Build the guest (non-moderator) Jitsi join URL
     *
     * @param string $room Meeting room name
     * @param string $email Guest email
     * @param string $name Guest display name
     * @return string
     */
    function consultation_build_guest_url($room, $email = '', $name = '')
    {
        return consultation_build_meeting_url($room, $email, $name, false);
    }
}

if (!function_exists('consultation_build_moderator_url')) {
    /**
     * Build the moderator Jitsi join URL
     *
     * @param string $room Meeting room name
     * @param string $email Moderator email
     * @param string $name Moderator display name
     * @return string
     */
    function consultation_build_moderator_url($room, $email = '', $name = '')
    {
        return consultation_build_meeting_url($room, $email, $name, true);
    }
}

if (!function_exists('consultation_timezone_list')) {
    /**
     * Get a flat list of supported PHP timezone identifiers
     *
     * @return array
     */
    function consultation_timezone_list()
    {
        return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    }
}

if (!function_exists('consultation_tz_convert')) {
    /**
     * Convert a datetime string between timezones
     *
     * @param string $datetime Datetime string (e.g. '2026-08-01 10:00:00')
     * @param string $from_tz Source timezone
     * @param string $to_tz Destination timezone
     * @param string $format Output format
     * @return string Converted datetime string
     */
    function consultation_tz_convert($datetime, $from_tz, $to_tz, $format = 'Y-m-d H:i:s')
    {
        try {
            $date = new DateTime($datetime, new DateTimeZone($from_tz));
            $date->setTimezone(new DateTimeZone($to_tz));
            return $date->format($format);
        } catch (Exception $e) {
            return $datetime;
        }
    }
}

if (!function_exists('consultation_company_timezone')) {
    /**
     * Get the application's central timezone
     *
     * @return string
     */
    function consultation_company_timezone()
    {
        $tz = config_item('timezone');
        return empty($tz) ? 'Australia/Sydney' : $tz;
    }
}

if (!function_exists('consultation_format_date')) {
    /**
     * Format an appointment date for human display
     *
     * @param string $date Y-m-d date
     * @return string
     */
    function consultation_format_date($date)
    {
        $ts = strtotime($date);
        return $ts ? date('D, M j, Y', $ts) : $date;
    }
}

if (!function_exists('consultation_format_time')) {
    /**
     * Format an appointment time for human display (12-hour)
     *
     * @param string $time H:i:s or H:i time
     * @return string
     */
    function consultation_format_time($time)
    {
        $ts = strtotime($time);
        return $ts ? date('g:i A', $ts) : $time;
    }
}

if (!function_exists('consultation_mail_tokens')) {
    /**
     * Build the shared placeholder map for consultation email templates.
     *
     * @param object $appointment Appointment row (optionally joined with consultant)
     * @return array
     */
    function consultation_mail_tokens($appointment)
    {
        return array(
            'CUSTOMER_NAME'   => !empty($appointment->customer_name) ? $appointment->customer_name : '',
            'CUSTOMER_EMAIL'  => !empty($appointment->customer_email) ? $appointment->customer_email : '',
            'CONSULTANT_NAME' => !empty($appointment->consultant_name) ? $appointment->consultant_name : '',
            'DATE'            => consultation_format_date($appointment->appointment_date),
            'TIME'            => consultation_format_time($appointment->appointment_time),
            'TIMEZONE'        => !empty($appointment->customer_timezone) ? $appointment->customer_timezone : '',
            'DURATION'        => (int)$appointment->duration_minutes,
            'CANCEL_LINK'     => site_url('booking/cancel/' . rawurlencode($appointment->cancel_token)),
        );
    }
}

if (!function_exists('consultation_send_mail')) {
    /**
     * Render and send a consultation email from a seeded tbl_email_templates row.
     *
     * @param string $group Email template group (email_group)
     * @param string $recipient Recipient email address
     * @param array $tokens Placeholder => value map (keys without braces)
     * @return bool
     */
    function consultation_send_mail($group, $recipient, $tokens = array())
    {
        if (empty($group) || empty($recipient)) {
            return false;
        }
        $template = email_templates(array('email_group' => $group), null, true);
        if (empty($template) || empty($template->template_body)) {
            return false;
        }

        $message = $template->template_body;
        $subject = $template->subject;
        foreach ($tokens as $key => $value) {
            $message = str_replace('{' . $key . '}', (string)$value, $message);
            $subject = str_replace('{' . $key . '}', (string)$value, $subject);
        }
        $message = str_replace('{SITE_NAME}', config_item('company_name'), $message);
        $subject = str_replace('{SITE_NAME}', config_item('company_name'), $subject);

        $data['message'] = $message;
        $CI = &get_instance();
        $html = $CI->load->view('email_template', $data, true);

        $params = array(
            'recipient'       => $recipient,
            'subject'         => $subject,
            'message'         => $html,
            'resourceed_file' => '',
        );
        if (isset($CI->consultation_model)) {
            return $CI->consultation_model->send_email($params);
        }
        return false;
    }
}

if (!function_exists('consultation_notify_cancellation')) {
    /**
     * Send cancellation notices to the customer and consultant.
     *
     * @param object $appointment Appointment row joined with consultant email
     * @return void
     */
    function consultation_notify_cancellation($appointment)
    {
        if (empty($appointment)) {
            return;
        }
        $tokens = consultation_mail_tokens($appointment);
        consultation_send_mail('consultation_cancellation_customer', $appointment->customer_email, $tokens);
        if (!empty($appointment->consultant_email)) {
            consultation_send_mail('consultation_cancellation_consultant', $appointment->consultant_email, $tokens);
        }
    }
}

if (!function_exists('consultation_reminder_lead_times')) {
    /**
     * Parse the reminder lead-time config into a sorted list of hours.
     *
     * @return array List of positive integer hours, sorted ascending
     */
    function consultation_reminder_lead_times()
    {
        $raw = consultation_config('reminder_hours', '');
        $list = array();
        foreach (explode(',', $raw) as $part) {
            $part = (int)trim($part);
            if ($part > 0) {
                $list[] = $part;
            }
        }
        $list = array_unique($list);
        sort($list);
        return $list;
    }
}

if (!function_exists('consultation_appointment_start_utc')) {
    /**
     * Appointment start time as a UTC Unix timestamp.
     *
     * @param object $appointment Appointment row
     * @return int Unix timestamp (0 on invalid data)
     */
    function consultation_appointment_start_utc($appointment)
    {
        $tz = !empty($appointment->customer_timezone) ? $appointment->customer_timezone : consultation_company_timezone();
        try {
            $dt = new DateTime($appointment->appointment_date . ' ' . $appointment->appointment_time, new DateTimeZone($tz));
        } catch (Exception $e) {
            return 0;
        }
        return $dt->getTimestamp();
    }
}
