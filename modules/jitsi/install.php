<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

$CI->db->query(
    "INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`)
    VALUES (NULL, 'jitsi', 'admin/jitsi', 'fa fa-video-camera', '0', '3', CURRENT_TIMESTAMP, '1');"
);

$CI->db->query(
    "INSERT INTO `tbl_client_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`)
    VALUES (NULL, 'jitsi', 'jitsi/meetings', 'fa fa-video-camera', '0', '3', CURRENT_TIMESTAMP, '1');"
);

$CI->db->query(
    "CREATE TABLE IF NOT EXISTS `tbl_jitsi_meetings` (
        `jitsi_meeting_id` INT NOT NULL AUTO_INCREMENT,
        `topic` VARCHAR(250) DEFAULT NULL,
        `meeting_time` DATETIME DEFAULT NULL,
        `duration` VARCHAR(100) DEFAULT NULL,
        `notes` TEXT,
        `host` INT DEFAULT NULL,
        `user_id` TEXT,
        `client_id` TEXT,
        `leads_id` VARCHAR(100) DEFAULT NULL,
        `meeting_room` VARCHAR(500) DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT 'waiting',
        `meeting_start` VARCHAR(40) DEFAULT NULL,
        `date_added` DATETIME DEFAULT NULL,
        `added_from` INT DEFAULT NULL,
        PRIMARY KEY (`jitsi_meeting_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"
);

$CI->db->query(
    "INSERT INTO `tbl_email_templates` (`email_templates_id`, `code`, `email_group`, `subject`, `template_body`)
    VALUES (NULL, 'jitsi_meeting_start', 'jitsi_meeting_start', 'Your Jitsi Meeting is Starting Now',
    '<p>Hello {USER}!</p>
    <p>A video meeting has started: <strong>{TOPIC}</strong></p>
    <p>Hosted by: {HOST}</p>
    <p>Click the link below to join:</p>
    <p><a href=\"{MEETING_URL}\"><strong>Join the Meeting</strong></a></p>
    <p>If the link does not work, copy and paste this URL into your browser:<br>
    <a href=\"{MEETING_URL}\">{MEETING_URL}</a></p>
    <p>Best regards,<br>{SITE_NAME} Team</p>');"
);
