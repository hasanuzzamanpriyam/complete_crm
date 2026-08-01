<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_consultation_system extends CI_Migration {

    public function up()
    {
        // ------------------------------------------------------------------
        // Consultants
        // ------------------------------------------------------------------
        $this->db->query("CREATE TABLE IF NOT EXISTS tbl_consultants (
            consultant_id    INT AUTO_INCREMENT PRIMARY KEY,
            name             VARCHAR(150) NOT NULL,
            email            VARCHAR(150) NOT NULL,
            phone            VARCHAR(50) DEFAULT NULL,
            timezone         VARCHAR(100) NOT NULL DEFAULT 'UTC',
            department       VARCHAR(100) DEFAULT NULL,
            bio              TEXT DEFAULT NULL,
            avatar           VARCHAR(255) DEFAULT NULL,
            is_active        TINYINT(1) NOT NULL DEFAULT 1,
            created_at       DATETIME NOT NULL,
            KEY idx_email (email),
            KEY idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // ------------------------------------------------------------------
        // Consultation Appointments
        // ------------------------------------------------------------------
        $this->db->query("CREATE TABLE IF NOT EXISTS tbl_consultation_appointments (
            appointment_id      INT AUTO_INCREMENT PRIMARY KEY,
            consultant_id       INT NOT NULL,
            customer_name       VARCHAR(150) NOT NULL,
            customer_email      VARCHAR(150) NOT NULL,
            customer_phone      VARCHAR(50) DEFAULT NULL,
            company             VARCHAR(150) DEFAULT NULL,
            country             VARCHAR(100) DEFAULT NULL,
            customer_timezone   VARCHAR(100) NOT NULL DEFAULT 'UTC',
            appointment_date    DATE NOT NULL,
            appointment_time    TIME NOT NULL,
            duration_minutes    INT NOT NULL DEFAULT 30,
            consultation_type   VARCHAR(50) NOT NULL DEFAULT 'consultation',
            notes               TEXT DEFAULT NULL,
            meeting_room        VARCHAR(100) NOT NULL,
            meeting_url         TEXT DEFAULT NULL,
            moderator_url       TEXT DEFAULT NULL,
            meeting_password    VARCHAR(50) DEFAULT NULL,
            status              ENUM('pending','confirmed','completed','cancelled','no_show') NOT NULL DEFAULT 'confirmed',
            cancel_token        VARCHAR(64) NOT NULL,
            reminder_sent_at    DATETIME DEFAULT NULL,
            created_at          DATETIME NOT NULL,
            updated_at          DATETIME DEFAULT NULL,
            KEY idx_consultant (consultant_id),
            KEY idx_date (appointment_date),
            KEY idx_status (status),
            KEY idx_consultant_date (consultant_id, appointment_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // ------------------------------------------------------------------
        // Consultation Weekly Slots
        // ------------------------------------------------------------------
        $this->db->query("CREATE TABLE IF NOT EXISTS tbl_consultation_slots (
            slot_id        INT AUTO_INCREMENT PRIMARY KEY,
            consultant_id  INT NOT NULL,
            day_of_week    TINYINT NOT NULL,
            start_time     TIME NOT NULL,
            end_time       TIME NOT NULL,
            is_active      TINYINT(1) NOT NULL DEFAULT 1,
            KEY idx_consultant (consultant_id),
            KEY idx_consultant_day (consultant_id, day_of_week)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // ------------------------------------------------------------------
        // Settings
        // ------------------------------------------------------------------
        $settings = array(
            'consultation_booking_enabled'    => '1',
            'consultation_default_duration'   => '30',
            'consultation_min_advance_hours'  => '2',
            'consultation_buffer_minutes'     => '15',
            'consultation_reminder_hours'     => '24,1',
        );
        foreach ($settings as $key => $value) {
            $this->db->query(
                "INSERT INTO tbl_config (config_key, value)
                VALUES (" . $this->db->escape($key) . ", " . $this->db->escape($value) . ")
                ON DUPLICATE KEY UPDATE value = VALUES(value)"
            );
        }

        // ------------------------------------------------------------------
        // Email Templates
        // ------------------------------------------------------------------
        $templates = array(
            array(
                'email_group' => 'consultation_confirmation_customer',
                'subject'     => 'Your Free Consultation is Confirmed',
                'template_body' =>
                    '<p>Hello {CUSTOMER_NAME},</p>'
                    . '<p>Your free consultation has been <strong>confirmed</strong>.</p>'
                    . '<p><strong>Consultant:</strong> {CONSULTANT_NAME}<br>'
                    . '<strong>Date:</strong> {DATE}<br>'
                    . '<strong>Time:</strong> {TIME} ({TIMEZONE})<br>'
                    . '<strong>Duration:</strong> {DURATION} minutes</p>'
                    . '<p>Join the video meeting by clicking the link below:</p>'
                    . '<p><a href="{MEETING_URL}"><strong>Join the Meeting</strong></a></p>'
                    . '<p>If the link does not work, copy and paste this URL into your browser:<br>'
                    . '<a href="{MEETING_URL}">{MEETING_URL}</a></p>'
                    . '<p>Need to reschedule or cancel? <a href="{CANCEL_LINK}">Click here to cancel</a>.</p>'
                    . '<p>Best regards,<br>{SITE_NAME} Team</p>'
            ),
            array(
                'email_group' => 'consultation_confirmation_consultant',
                'subject'     => 'New Free Consultation: {CUSTOMER_NAME}',
                'template_body' =>
                    '<p>Hello {CONSULTANT_NAME},</p>'
                    . '<p>You have a new free consultation appointment.</p>'
                    . '<p><strong>Customer:</strong> {CUSTOMER_NAME} ({CUSTOMER_EMAIL})<br>'
                    . '<strong>Date:</strong> {DATE}<br>'
                    . '<strong>Time:</strong> {TIME} ({TIMEZONE})<br>'
                    . '<strong>Duration:</strong> {DURATION} minutes</p>'
                    . '<p><a href="{MEETING_URL}"><strong>Open the Meeting</strong></a></p>'
                    . '<p>Best regards,<br>{SITE_NAME} Team</p>'
            ),
            array(
                'email_group' => 'consultation_cancellation_customer',
                'subject'     => 'Your Free Consultation has been Cancelled',
                'template_body' =>
                    '<p>Hello {CUSTOMER_NAME},</p>'
                    . '<p>Your free consultation with <strong>{CONSULTANT_NAME}</strong> on <strong>{DATE} at {TIME} ({TIMEZONE})</strong> has been cancelled.</p>'
                    . '<p>If you would like to book a new consultation, please visit our website.</p>'
                    . '<p>Best regards,<br>{SITE_NAME} Team</p>'
            ),
            array(
                'email_group' => 'consultation_cancellation_consultant',
                'subject'     => 'Consultation Cancelled: {CUSTOMER_NAME}',
                'template_body' =>
                    '<p>Hello {CONSULTANT_NAME},</p>'
                    . '<p>The free consultation with <strong>{CUSTOMER_NAME}</strong> on <strong>{DATE} at {TIME} ({TIMEZONE})</strong> has been cancelled.</p>'
                    . '<p>Best regards,<br>{SITE_NAME} Team</p>'
            ),
            array(
                'email_group' => 'consultation_reminder',
                'subject'     => 'Reminder: Your Free Consultation with {CONSULTANT_NAME}',
                'template_body' =>
                    '<p>Hello {CUSTOMER_NAME},</p>'
                    . '<p>This is a friendly reminder about your upcoming free consultation:</p>'
                    . '<p><strong>Consultant:</strong> {CONSULTANT_NAME}<br>'
                    . '<strong>Date:</strong> {DATE}<br>'
                    . '<strong>Time:</strong> {TIME} ({TIMEZONE})<br>'
                    . '<strong>Duration:</strong> {DURATION} minutes</p>'
                    . '<p><a href="{MEETING_URL}"><strong>Join the Meeting</strong></a></p>'
                    . '<p>Best regards,<br>{SITE_NAME} Team</p>'
            ),
        );

        foreach ($templates as $template) {
            $exists = $this->db->where('email_group', $template['email_group'])->where('code', 'en')->count_all_results('tbl_email_templates');
            if ($exists > 0) {
                continue;
            }
            $this->db->insert('tbl_email_templates', array(
                'code'          => 'en',
                'email_group'   => $template['email_group'],
                'subject'       => $template['subject'],
                'template_body' => $template['template_body'],
            ));
        }

        // ------------------------------------------------------------------
        // Admin Menu
        // ------------------------------------------------------------------
        $menu = $this->db->where('link', 'admin/consultation')->get('tbl_menu')->row();
        if (empty($menu)) {
            $this->db->insert('tbl_menu', array(
                'label'  => 'Consultation',
                'link'   => 'admin/consultation',
                'icon'   => 'fa fa-calendar-check-o',
                'parent' => 0,
                'sort'   => 4,
                'status' => 1,
            ));
        }
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS tbl_consultants");
        $this->db->query("DROP TABLE IF EXISTS tbl_consultation_appointments");
        $this->db->query("DROP TABLE IF EXISTS tbl_consultation_slots");

        $keys = array(
            'consultation_booking_enabled',
            'consultation_default_duration',
            'consultation_min_advance_hours',
            'consultation_buffer_minutes',
            'consultation_reminder_hours',
        );
        $this->db->where_in('config_key', $keys)->delete('tbl_config');

        $groups = array(
            'consultation_confirmation_customer',
            'consultation_confirmation_consultant',
            'consultation_cancellation_customer',
            'consultation_cancellation_consultant',
            'consultation_reminder',
        );
        $this->db->where_in('email_group', $groups)->delete('tbl_email_templates');

        $this->db->where('link', 'admin/consultation')->delete('tbl_menu');
    }
}
