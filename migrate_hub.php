<?php
/**
 * Master Migration Script for Payment Hub
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tic_crm';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("DB Connection Failed: " . $e->getMessage() . "\n");
}

function tableExists($pdo, $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) { return false; }
}

function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) { return false; }
}

echo "Starting Payment Hub Master Migration...\n";

// 1. Create Base Tables if missing (Legacy naming)
$base_sqls = [
    "CREATE TABLE IF NOT EXISTS `tbl_payment_projects` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `project_name` VARCHAR(255) NOT NULL,
        `api_key` VARCHAR(100) NOT NULL,
        `api_secret` VARCHAR(100) NOT NULL,
        `callback_url` VARCHAR(255) DEFAULT NULL,
        `webhook_url` VARCHAR(255) DEFAULT NULL,
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `api_key` (`api_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_api_tokens` (
        `id`              INT(11) NOT NULL AUTO_INCREMENT,
        `project_id`      INT(11) NOT NULL,
        `token_name`      VARCHAR(100) NOT NULL DEFAULT 'Default Token',
        `token_prefix`    VARCHAR(12)  NOT NULL,
        `token_hash`      VARCHAR(64)  NOT NULL,
        `signing_secret`  VARCHAR(64)  NOT NULL,
        `ip_whitelist`    TEXT         DEFAULT NULL,
        `status`          ENUM('active','disabled','revoked') NOT NULL DEFAULT 'active',
        `expires_at`      DATETIME     DEFAULT NULL,
        `last_used_at`    DATETIME     DEFAULT NULL,
        `last_used_ip`    VARCHAR(45)  DEFAULT NULL,
        `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `token_hash` (`token_hash`),
        KEY `project_id` (`project_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_external_transactions` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `project_id` INT(11) NOT NULL,
        `external_reference` VARCHAR(100) NOT NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `currency` VARCHAR(10) DEFAULT 'BDT',
        `gateway_name` VARCHAR(50) DEFAULT 'PipraPay',
        `gateway_transaction_id` VARCHAR(100) DEFAULT NULL,
        `status` ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending',
        `payment_method` VARCHAR(50) DEFAULT NULL,
        `raw_response` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `project_id` (`project_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
];

foreach ($base_sqls as $sql) {
    $pdo->exec($sql);
}
echo "✔ Base legacy tables ensured.\n";

// 2. Renames (Migration 613 logic)
if (tableExists($pdo, 'tbl_payment_projects') && !tableExists($pdo, 'tbl_api_clients')) {
    $pdo->exec("RENAME TABLE `tbl_payment_projects` TO `tbl_api_clients` ");
    echo "✔ Renamed tbl_payment_projects to tbl_api_clients\n";
}
if (tableExists($pdo, 'tbl_external_transactions') && !tableExists($pdo, 'tbl_payments')) {
    $pdo->exec("RENAME TABLE `tbl_external_transactions` TO `tbl_payments` ");
    echo "✔ Renamed tbl_external_transactions to tbl_payments\n";
}

// 3. New Tables (Migration 613 logic)
$new_tables = [
    "CREATE TABLE IF NOT EXISTS `tbl_payment_gateways` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `gateway_slug` VARCHAR(50) NOT NULL,
        `config` TEXT DEFAULT NULL,
        `status` ENUM('active','inactive') DEFAULT 'active',
        `is_default` TINYINT(1) DEFAULT '0',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `gateway_slug` (`gateway_slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_payment_transactions` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_id` INT(11) NOT NULL,
        `gateway_id` INT(11) DEFAULT NULL,
        `gateway_txn_id` VARCHAR(100) DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL,
        `raw_response` LONGTEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `payment_id` (`payment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_payment_logs` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_id` INT(11) DEFAULT NULL,
        `log_level` VARCHAR(10) DEFAULT 'info',
        `message` TEXT NOT NULL,
        `context` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_webhook_logs` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_id` INT(11) DEFAULT NULL,
        `direction` ENUM('incoming','outgoing') NOT NULL,
        `url` VARCHAR(255) DEFAULT NULL,
        `payload` LONGTEXT DEFAULT NULL,
        `response_code` INT(5) DEFAULT NULL,
        `response_body` TEXT DEFAULT NULL,
        `status` ENUM('success','failed','pending') DEFAULT 'pending',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `tbl_refunds` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `payment_id` INT(11) NOT NULL,
        `amount` DECIMAL(15,2) NOT NULL,
        `reason` TEXT DEFAULT NULL,
        `gateway_refund_id` VARCHAR(100) DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
];

foreach ($new_tables as $sql) {
    $pdo->exec($sql);
}
echo "✔ New hub tables ensured.\n";

// Seed Piprapay if missing
$stmt = $pdo->query("SELECT id FROM tbl_payment_gateways WHERE gateway_slug = 'piprapay'");
if ($stmt->rowCount() == 0) {
    $pdo->exec("INSERT INTO tbl_payment_gateways (name, gateway_slug, is_default) VALUES ('PipraPay', 'piprapay', 1)");
    echo "✔ Seeded Piprapay gateway.\n";
}

// 4. Schema Refinement (Migration 613 & 614 logic)
if (tableExists($pdo, 'tbl_api_clients')) {
    if (!columnExists($pdo, 'tbl_api_clients', 'client_id')) {
        $pdo->exec("ALTER TABLE `tbl_api_clients` ADD COLUMN `client_id` VARCHAR(64) NULL AFTER `project_name`, ADD COLUMN `client_secret` VARCHAR(128) NULL AFTER `client_id`, ADD COLUMN `updated_at` DATETIME NULL AFTER `created_at` ");
        $pdo->exec("UPDATE `tbl_api_clients` SET `client_id` = `api_key`, `client_secret` = `api_secret` WHERE `client_id` IS NULL");
        echo "✔ Updated tbl_api_clients columns.\n";
    }
}

if (tableExists($pdo, 'tbl_payments')) {
    if (!columnExists($pdo, 'tbl_payments', 'customer_name')) {
        $pdo->exec("ALTER TABLE `tbl_payments` ADD COLUMN `customer_name` VARCHAR(100) NULL AFTER `currency`, ADD COLUMN `customer_email` VARCHAR(100) NULL AFTER `customer_name` ");
        echo "✔ Updated tbl_payments columns.\n";
    }
}

// Webhook Retry Columns (614)
addColumnIfMissing($pdo, 'tbl_webhook_logs', 'retry_count', '`retry_count` INT(5) DEFAULT 0 AFTER `status`');
addColumnIfMissing($pdo, 'tbl_webhook_logs', 'next_retry_at', '`next_retry_at` DATETIME NULL AFTER `retry_count`');
addColumnIfMissing($pdo, 'tbl_webhook_logs', 'last_error', '`last_error` TEXT NULL AFTER `next_retry_at`');

// Audit Columns (614)
addColumnIfMissing($pdo, 'tbl_payment_logs', 'ip_address', '`ip_address` VARCHAR(45) NULL AFTER `context`');
addColumnIfMissing($pdo, 'tbl_payment_logs', 'user_agent', '`user_agent` VARCHAR(255) NULL AFTER `ip_address`');

function addColumnIfMissing($pdo, $table, $column, $definition) {
    if (!columnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN $definition");
        echo "✔ Added $column to $table\n";
    }
}

// 5. Update migration version
try {
    $pdo->exec("UPDATE `tbl_migrations` SET `version` = 614");
    echo "✔ Updated tbl_migrations version to 614\n";
} catch (Exception $e) {}

// ========================================
// 6. Recruitment & ATS Module (Migration 619)
// ========================================
echo "\n--- Recruitment & ATS Module (Migration 619) ---\n";

// Helper functions for migration 619
function tblExists619($pdo, $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) { return false; }
}

function colExists619($pdo, $table, $col) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) { return false; }
}

function addCol619($pdo, $table, $col, $def) {
    if (!colExists619($pdo, $table, $col)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $def");
        echo "✔ Added $col to $table\n";
    }
}

// 1. Recruitment Skills Master
$pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_recruitment_skills` (
    `skill_id` INT(11) NOT NULL AUTO_INCREMENT,
    `skill_name` VARCHAR(100) NOT NULL,
    `skill_category` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`skill_id`),
    UNIQUE KEY `unique_skill` (`skill_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
echo "✔ Created tbl_recruitment_skills\n";

// 2. Job-Skills Mapping
$pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_job_skills` (
    `job_skill_id` INT(11) NOT NULL AUTO_INCREMENT,
    `job_circular_id` INT(11) NOT NULL,
    `skill_id` INT(11) NOT NULL,
    `is_mandatory` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`job_skill_id`),
    UNIQUE KEY `unique_job_skill` (`job_circular_id`, `skill_id`),
    KEY `fk_job_skills_job` (`job_circular_id`),
    KEY `fk_job_skills_skill` (`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
echo "✔ Created tbl_job_skills\n";

// 3. Extend tbl_job_appliactions with ATS columns
if (tblExists619($pdo, 'tbl_job_appliactions')) {
    addCol619($pdo, 'tbl_job_appliactions', 'ats_score', "DECIMAL(5,2) DEFAULT 0.00");
    addCol619($pdo, 'tbl_job_appliactions', 'matched_skills', "TEXT DEFAULT NULL");
    addCol619($pdo, 'tbl_job_appliactions', 'missing_skills', "TEXT DEFAULT NULL");
    addCol619($pdo, 'tbl_job_appliactions', 'resume_text', "LONGTEXT DEFAULT NULL");
    addCol619($pdo, 'tbl_job_appliactions', 'skill_match_details', "TEXT DEFAULT NULL");
} else {
    echo "⚠ tbl_job_appliactions not found - skipping ATS columns\n";
}

// 4. Interview Scheduling
$pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_interviews` (
    `interview_id` INT(11) NOT NULL AUTO_INCREMENT,
    `job_appliactions_id` INT(11) NOT NULL,
    `job_circular_id` INT(11) NOT NULL,
    `interview_type` ENUM('online','face_to_face','phone') NOT NULL DEFAULT 'online',
    `interview_date` DATE NOT NULL,
    `interview_time` TIME NOT NULL,
    `interviewer_name` VARCHAR(100) DEFAULT NULL,
    `interviewer_email` VARCHAR(100) DEFAULT NULL,
    `meeting_link` VARCHAR(500) DEFAULT NULL,
    `location_details` TEXT DEFAULT NULL,
    `interview_notes` TEXT DEFAULT NULL,
    `status` ENUM('scheduled','completed','cancelled','no_show','rescheduled') NOT NULL DEFAULT 'scheduled',
    `feedback` TEXT DEFAULT NULL,
    `rating` TINYINT(1) DEFAULT NULL,
    `email_sent` TINYINT(1) NOT NULL DEFAULT 0,
    `email_sent_at` DATETIME DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`interview_id`),
    KEY `fk_interviews_application` (`job_appliactions_id`),
    KEY `fk_interviews_job` (`job_circular_id`),
    KEY `idx_interview_date` (`interview_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
echo "✔ Created tbl_interviews\n";

// 5. Offer Letters
$pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_offer_letters` (
    `offer_id` INT(11) NOT NULL AUTO_INCREMENT,
    `job_appliactions_id` INT(11) NOT NULL,
    `job_circular_id` INT(11) NOT NULL,
    `offer_template_id` INT(11) DEFAULT NULL,
    `offer_subject` VARCHAR(255) NOT NULL,
    `offer_body` LONGTEXT NOT NULL,
    `salary_offered` VARCHAR(100) DEFAULT NULL,
    `joining_date` DATE DEFAULT NULL,
    `additional_terms` TEXT DEFAULT NULL,
    `status` ENUM('draft','sent','accepted','declined','expired') NOT NULL DEFAULT 'draft',
    `sent_at` DATETIME DEFAULT NULL,
    `responded_at` DATETIME DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`offer_id`),
    KEY `fk_offers_application` (`job_appliactions_id`),
    KEY `fk_offers_job` (`job_circular_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
echo "✔ Created tbl_offer_letters\n";

// 6. Offer Letter Templates
$pdo->exec("CREATE TABLE IF NOT EXISTS `tbl_offer_templates` (
    `template_id` INT(11) NOT NULL AUTO_INCREMENT,
    `template_name` VARCHAR(100) NOT NULL,
    `template_subject` VARCHAR(255) NOT NULL,
    `template_body` LONGTEXT NOT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
echo "✔ Created tbl_offer_templates\n";

// Seed default offer template
$stmt = $pdo->query("SELECT template_id FROM tbl_offer_templates WHERE template_name = 'Standard Offer Letter'");
if ($stmt->rowCount() == 0) {
    $pdo->exec("INSERT INTO `tbl_offer_templates` 
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
    echo "✔ Seeded default offer template\n";
}

// Seed sample skills
$skills = [
    ['PHP', 'Technical'], ['JavaScript', 'Technical'], ['MySQL', 'Technical'],
    ['CodeIgniter', 'Technical'], ['Laravel', 'Technical'], ['React', 'Technical'],
    ['Node.js', 'Technical'], ['Python', 'Technical'], ['HTML/CSS', 'Technical'],
    ['Communication', 'Soft'], ['Team Leadership', 'Soft'], ['Problem Solving', 'Soft'],
    ['Project Management', 'Soft'], ['English', 'Language'], ['Git', 'Tool'],
    ['Docker', 'Tool'], ['AWS', 'Tool'], ['REST API', 'Technical'], ['Agile/Scrum', 'Soft']
];
$seeded = 0;
foreach ($skills as $skill) {
    $stmt = $pdo->query("SELECT skill_id FROM tbl_recruitment_skills WHERE skill_name = '{$skill[0]}'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("INSERT INTO `tbl_recruitment_skills` (`skill_name`, `skill_category`) VALUES ('{$skill[0]}', '{$skill[1]}')");
        $seeded++;
    }
}
echo "✔ Seeded $seeded sample skills\n";

// Add new menu items under Recruitment (menu_id 102)
$menuItems = [
    [240, 'skills_management', 'admin/job_circular/manage_skills', 'fa fa-tags', 102, 2],
    [241, 'interview_schedule', 'admin/job_circular/manage_interviews', 'fa fa-calendar-check-o', 102, 3],
    [242, 'offer_letters', 'admin/job_circular/manage_offers', 'fa fa-file-text-o', 102, 4]
];
foreach ($menuItems as $menu) {
    $stmt = $pdo->query("SELECT menu_id FROM tbl_menu WHERE menu_id = {$menu[0]}");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `time`, `status`) VALUES
            ({$menu[0]}, '{$menu[1]}', '{$menu[2]}', '{$menu[3]}', {$menu[4]}, {$menu[5]}, NOW(), 1)");
        echo "✔ Added menu: {$menu[1]}\n";
    }
}

// Add interview email template
$stmt = $pdo->query("SELECT email_templates_id FROM tbl_email_templates WHERE email_group = 'interview_invitation'");
if ($stmt->rowCount() == 0) {
    $pdo->exec("INSERT INTO `tbl_email_templates` (`email_group`, `subject`, `template_body`) VALUES 
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
    echo "✔ Added interview_invitation email template\n";
}

// Update migration version
try {
    $pdo->exec("UPDATE `tbl_migrations` SET `version` = 619");
    echo "✔ Updated tbl_migrations version to 619\n";
} catch (Exception $e) {}

echo "\nMigration Complete! All tables and columns are synchronized.\n";
