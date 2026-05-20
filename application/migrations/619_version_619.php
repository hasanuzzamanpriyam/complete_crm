<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_619 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("UPDATE `tbl_config` SET `value` = '6.1.9' WHERE `tbl_config`.`config_key` = 'version';");

        // 1. Recruitment Skills Master
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_recruitment_skills` (
            `skill_id` INT(11) NOT NULL AUTO_INCREMENT,
            `skill_name` VARCHAR(100) NOT NULL,
            `skill_category` VARCHAR(50) DEFAULT NULL COMMENT 'e.g., Technical, Soft, Language, Tool',
            `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`skill_id`),
            UNIQUE KEY `unique_skill` (`skill_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        // 2. Job-Skills Mapping
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_job_skills` (
            `job_skill_id` INT(11) NOT NULL AUTO_INCREMENT,
            `job_circular_id` INT(11) NOT NULL,
            `skill_id` INT(11) NOT NULL,
            `is_mandatory` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Required, 0=Preferred',
            PRIMARY KEY (`job_skill_id`),
            UNIQUE KEY `unique_job_skill` (`job_circular_id`, `skill_id`),
            KEY `fk_job_skills_job` (`job_circular_id`),
            KEY `fk_job_skills_skill` (`skill_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        // 3. Extend tbl_job_appliactions with ATS columns
        $this->_add_column_if_not_exists('tbl_job_appliactions', 'ats_score', "DECIMAL(5,2) DEFAULT 0.00 COMMENT 'ATS match percentage (0.00-100.00)'");
        $this->_add_column_if_not_exists('tbl_job_appliactions', 'matched_skills', "TEXT DEFAULT NULL COMMENT 'JSON array of matched skill names'");
        $this->_add_column_if_not_exists('tbl_job_appliactions', 'missing_skills', "TEXT DEFAULT NULL COMMENT 'JSON array of missing required skill names'");
        $this->_add_column_if_not_exists('tbl_job_appliactions', 'resume_text', "LONGTEXT DEFAULT NULL COMMENT 'Extracted plain text from resume for parsing'");
        $this->_add_column_if_not_exists('tbl_job_appliactions', 'skill_match_details', "TEXT DEFAULT NULL COMMENT 'JSON: detailed breakdown of skill match per skill'");

        // 4. Interview Scheduling
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_interviews` (
            `interview_id` INT(11) NOT NULL AUTO_INCREMENT,
            `job_appliactions_id` INT(11) NOT NULL,
            `job_circular_id` INT(11) NOT NULL,
            `interview_type` ENUM('online','face_to_face','phone') NOT NULL DEFAULT 'online',
            `interview_date` DATE NOT NULL,
            `interview_time` TIME NOT NULL,
            `interviewer_name` VARCHAR(100) DEFAULT NULL,
            `interviewer_email` VARCHAR(100) DEFAULT NULL,
            `meeting_link` VARCHAR(500) DEFAULT NULL COMMENT 'Zoom/Jitsi/Teams link for online',
            `location_details` TEXT DEFAULT NULL COMMENT 'Address/room for face-to-face',
            `interview_notes` TEXT DEFAULT NULL,
            `status` ENUM('scheduled','completed','cancelled','no_show','rescheduled') NOT NULL DEFAULT 'scheduled',
            `feedback` TEXT DEFAULT NULL,
            `rating` TINYINT(1) DEFAULT NULL COMMENT '1-5 interviewer rating',
            `email_sent` TINYINT(1) NOT NULL DEFAULT 0,
            `email_sent_at` DATETIME DEFAULT NULL,
            `created_by` INT(11) DEFAULT NULL COMMENT 'user_id of admin who scheduled',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`interview_id`),
            KEY `fk_interviews_application` (`job_appliactions_id`),
            KEY `fk_interviews_job` (`job_circular_id`),
            KEY `idx_interview_date` (`interview_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        // 5. Offer Letters
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_offer_letters` (
            `offer_id` INT(11) NOT NULL AUTO_INCREMENT,
            `job_appliactions_id` INT(11) NOT NULL,
            `job_circular_id` INT(11) NOT NULL,
            `offer_template_id` INT(11) DEFAULT NULL,
            `offer_subject` VARCHAR(255) NOT NULL,
            `offer_body` LONGTEXT NOT NULL COMMENT 'HTML email body with placeholders replaced',
            `salary_offered` VARCHAR(100) DEFAULT NULL,
            `joining_date` DATE DEFAULT NULL,
            `additional_terms` TEXT DEFAULT NULL,
            `status` ENUM('draft','sent','accepted','declined','expired') NOT NULL DEFAULT 'draft',
            `sent_at` DATETIME DEFAULT NULL,
            `responded_at` DATETIME DEFAULT NULL,
            `created_by` INT(11) DEFAULT NULL COMMENT 'user_id of admin',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`offer_id`),
            KEY `fk_offers_application` (`job_appliactions_id`),
            KEY `fk_offers_job` (`job_circular_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        // 6. Offer Letter Templates
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_offer_templates` (
            `template_id` INT(11) NOT NULL AUTO_INCREMENT,
            `template_name` VARCHAR(100) NOT NULL,
            `template_subject` VARCHAR(255) NOT NULL,
            `template_body` LONGTEXT NOT NULL COMMENT 'HTML with placeholders',
            `is_default` TINYINT(1) NOT NULL DEFAULT 0,
            `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`template_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        // Seed default offer template
        $this->db->query("INSERT IGNORE INTO `tbl_offer_templates` 
            (`template_name`, `template_subject`, `template_body`, `is_default`) 
            VALUES (
                'Standard Offer Letter',
                'Job Offer: {JOB_TITLE} at {COMPANY_NAME}',
                '<p>Dear <strong>{CANDIDATE_NAME}</strong>,</p>
                 <p>We are pleased to offer you the position of <strong>{JOB_TITLE}</strong> at <strong>{COMPANY_NAME}</strong>.</p>
                 <p><strong>Position Details:</strong></p>
                 <ul>
                   <li><strong>Position:</strong> {JOB_TITLE}</li>
                   <li><strong>Salary:</strong> {SALARY}</li>
                   <li><strong>Joining Date:</strong> {JOINING_DATE}</li>
                   <li><strong>Employment Type:</strong> {EMPLOYMENT_TYPE}</li>
                 </ul>
                 <p>{ADDITIONAL_TERMS}</p>
                 <p>We look forward to having you on our team. Please confirm your acceptance by replying to this email.</p>
                 <p>Best Regards,<br/>The <strong>{COMPANY_NAME}</strong> Team</p>',
                1
            );");

        // Seed sample skills
        $skills = [
            ['PHP', 'Technical'], ['JavaScript', 'Technical'], ['MySQL', 'Technical'],
            ['CodeIgniter', 'Technical'], ['Laravel', 'Technical'], ['React', 'Technical'],
            ['Node.js', 'Technical'], ['Python', 'Technical'], ['HTML/CSS', 'Technical'],
            ['Communication', 'Soft'], ['Team Leadership', 'Soft'], ['Problem Solving', 'Soft'],
            ['Project Management', 'Soft'], ['English', 'Language'], ['Git', 'Tool'],
            ['Docker', 'Tool'], ['AWS', 'Tool'], ['REST API', 'Technical'], ['Agile/Scrum', 'Soft']
        ];
        foreach ($skills as $skill) {
            $this->db->query("INSERT IGNORE INTO `tbl_recruitment_skills` (`skill_name`, `skill_category`) VALUES ('{$skill[0]}', '{$skill[1]}');");
        }

        // Add new menu items under Recruitment (menu_id 102)
        $this->db->query("INSERT IGNORE INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES
            (240, 'skills_management', 'admin/job_circular/manage_skills', 'fa fa-tags', 102, 2, NOW(), 1),
            (241, 'interview_schedule', 'admin/job_circular/manage_interviews', 'fa fa-calendar-check-o', 102, 3, NOW(), 1),
            (242, 'offer_letters', 'admin/job_circular/manage_offers', 'fa fa-file-text-o', 102, 4, NOW(), 1);");

        // Add interview email template
        $this->db->query("INSERT IGNORE INTO `tbl_email_templates` (`email_group`, `subject`, `template_body`) VALUES 
            ('interview_invitation', 'Interview Invitation: {JOB_TITLE} at {COMPANY_NAME}',
             '<p>Dear <strong>{CANDIDATE_NAME}</strong>,</p>
              <p>We are pleased to invite you for an interview for the position of <strong>{JOB_TITLE}</strong> at <strong>{COMPANY_NAME}</strong>.</p>
              <p><strong>Interview Details:</strong></p>
              <ul>
                <li><strong>Date:</strong> {INTERVIEW_DATE}</li>
                <li><strong>Time:</strong> {INTERVIEW_TIME}</li>
                <li><strong>Type:</strong> {INTERVIEW_TYPE}</li>
                {MEETING_LINK_OR_LOCATION}
              </ul>
              <p><strong>Interviewer:</strong> {INTERVIEWER_NAME}</p>
              {INTERVIEW_NOTES}
              <p>Please confirm your availability by replying to this email.</p>
              <p>Best Regards,<br/>The <strong>{COMPANY_NAME}</strong> Recruitment Team</p>');");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `tbl_interviews`;");
        $this->db->query("DROP TABLE IF EXISTS `tbl_offer_letters`;");
        $this->db->query("DROP TABLE IF EXISTS `tbl_offer_templates`;");
        $this->db->query("DROP TABLE IF EXISTS `tbl_job_skills`;");
        $this->db->query("DROP TABLE IF EXISTS `tbl_recruitment_skills`;");
        $this->_drop_column_if_exists('tbl_job_appliactions', 'ats_score');
        $this->_drop_column_if_exists('tbl_job_appliactions', 'matched_skills');
        $this->_drop_column_if_exists('tbl_job_appliactions', 'missing_skills');
        $this->_drop_column_if_exists('tbl_job_appliactions', 'resume_text');
        $this->_drop_column_if_exists('tbl_job_appliactions', 'skill_match_details');
        $this->db->query("DELETE FROM `tbl_email_templates` WHERE `email_group` = 'interview_invitation';");
        $this->db->query("DELETE FROM `tbl_menu` WHERE `menu_id` IN (240, 241, 242);");
        $this->db->query("UPDATE `tbl_config` SET `value` = '6.1.8' WHERE `tbl_config`.`config_key` = 'version';");
    }

    private function _add_column_if_not_exists($table, $column, $definition)
    {
        $exists = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->row();
        if (empty($exists)) {
            $this->db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }

    private function _drop_column_if_exists($table, $column)
    {
        $exists = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->row();
        if (!empty($exists)) {
            $this->db->query("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
        }
    }
}
