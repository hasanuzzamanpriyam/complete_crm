-- ============================================================
-- TIC CRM Demo Database Seed
-- Run AFTER: CREATE DATABASE tic_crm_demo;
-- Run AFTER: mysqldump --no-data tic_crm | mysql tic_crm_demo
-- Run AFTER: mysql -u root tic_crm_demo < seed.sql
-- Run AFTER: php seed_data.php  (seeds sample business data)
-- ============================================================

-- 1. Copy reference data from main DB (cross-database query)
-- These tables don't contain sensitive user data
INSERT IGNORE INTO tbl_migrations SELECT * FROM tic_crm.tbl_migrations;
INSERT IGNORE INTO tbl_currencies SELECT * FROM tic_crm.tbl_currencies;
INSERT IGNORE INTO tbl_modules SELECT * FROM tic_crm.tbl_modules;
INSERT IGNORE INTO tbl_designations SELECT * FROM tic_crm.tbl_designations;
INSERT IGNORE INTO tbl_languages SELECT * FROM tic_crm.tbl_languages;
INSERT IGNORE INTO tbl_leave_category SELECT * FROM tic_crm.tbl_leave_category;
INSERT IGNORE INTO tbl_announcements SELECT * FROM tic_crm.tbl_announcements;
INSERT IGNORE INTO tbl_menu SELECT * FROM tic_crm.tbl_menu;
-- Fix typo in menu link: settingh -> settings
UPDATE IGNORE tbl_menu SET link = 'admin/settings/award_rule_settings' WHERE link = 'admin/settings/award_rule_settingh';
INSERT IGNORE INTO tbl_departments SELECT * FROM tic_crm.tbl_departments;
INSERT IGNORE INTO tbl_user_role SELECT * FROM tic_crm.tbl_user_role;
INSERT IGNORE INTO tbl_locales SELECT * FROM tic_crm.tbl_locales;
INSERT IGNORE INTO tbl_goal_type SELECT * FROM tic_crm.tbl_goal_type;
INSERT IGNORE INTO tbl_accounts SELECT * FROM tic_crm.tbl_accounts;
INSERT IGNORE INTO tbl_form SELECT * FROM tic_crm.tbl_form;
INSERT IGNORE INTO tbl_priority SELECT * FROM tic_crm.tbl_priority;
INSERT IGNORE INTO tbl_status SELECT * FROM tic_crm.tbl_status;
INSERT IGNORE INTO tbl_lead_status SELECT * FROM tic_crm.tbl_lead_status;
INSERT IGNORE INTO tbl_lead_source SELECT * FROM tic_crm.tbl_lead_source;

-- Copy config (replace existing)
DELETE FROM tbl_config;
INSERT INTO tbl_config (config_key, value) SELECT config_key, value FROM tic_crm.tbl_config;

-- 2. Demo-specific designations
INSERT INTO tbl_designations (designations_id, departments_id, designations) VALUES
  (99991, 12, 'Demo Manager'),
  (99992, 12, 'Demo Employee')
ON DUPLICATE KEY UPDATE designations = VALUES(designations);

-- 3. Clone Office Manager permissions to Demo Manager
DELETE FROM tbl_user_role WHERE designations_id = 99991;
INSERT INTO tbl_user_role (designations_id, menu_id, view, created, edited, deleted)
SELECT 99991, menu_id, view, created, edited, deleted
FROM tbl_user_role WHERE designations_id = 29;

-- 4. Create demo users
INSERT INTO tbl_users (username, email, password, role_id, activated, banned, is_super_admin, created, last_login, online_time)
VALUES
  ('demo_admin',    'demo_admin@tic.com',    '5605a939a37bed7971305c64477c86df200f3df846efb3e8910e7673e303c45455b7e0cc54c224b75e61e64a26ffc2cc4450f4954aae16e1e76b1b49f0988fb4', 1, 1, 0, 1, NOW(), NOW(), UNIX_TIMESTAMP()),
  ('demo_manager',  'demo_manager@tic.com',  '5605a939a37bed7971305c64477c86df200f3df846efb3e8910e7673e303c45455b7e0cc54c224b75e61e64a26ffc2cc4450f4954aae16e1e76b1b49f0988fb4', 3, 1, 0, 0, NOW(), NOW(), UNIX_TIMESTAMP()),
  ('demo_employee', 'demo_employee@tic.com', '5605a939a37bed7971305c64477c86df200f3df846efb3e8910e7673e303c45455b7e0cc54c224b75e61e64a26ffc2cc4450f4954aae16e1e76b1b49f0988fb4', 3, 1, 0, 0, NOW(), NOW(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE username = VALUES(username);

SET @admin_id = (SELECT user_id FROM tbl_users WHERE username = 'demo_admin');
SET @manager_id = (SELECT user_id FROM tbl_users WHERE username = 'demo_manager');
SET @employee_id = (SELECT user_id FROM tbl_users WHERE username = 'demo_employee');

-- 5. Demo Account Details
INSERT INTO tbl_account_details (user_id, fullname, language, direction, avatar, designations_id)
VALUES
  (@admin_id,   'Demo Admin',    'english', 'ltr', 'uploads/default_avatar.jpg', 0),
  (@manager_id, 'Demo Manager',  'english', 'ltr', 'uploads/default_avatar.jpg', 99991),
  (@employee_id,'Demo Employee', 'english', 'ltr', 'uploads/default_avatar.jpg', 99992)
ON DUPLICATE KEY UPDATE fullname = VALUES(fullname), designations_id = VALUES(designations_id);

-- 6. Demo Employee permissions (self-service features: dash, tasks, leave, attendance, etc.)
DELETE FROM tbl_user_role WHERE designations_id = 99992;
INSERT INTO tbl_user_role (designations_id, menu_id, view, created, edited, deleted) VALUES
  (99992, 1, 1, 1, 0, 0),   -- dashboard
  (99992, 54, 1, 1, 0, 0),  -- tasks
  (99992, 72, 1, 1, 0, 0),  -- leave_management
  (99992, 69, 1, 1, 0, 0),  -- goal_tracking
  (99992, 148, 1, 1, 0, 0), -- mark_attendance
  (99992, 105, 1, 1, 0, 0), -- attendance# (parent)
  (99992, 108, 1, 1, 0, 0), -- time_history
  (99992, 107, 1, 1, 0, 0), -- attendance_report
  (99992, 106, 1, 1, 0, 0), -- timechange_request
  (99992, 100, 1, 1, 0, 0), -- announcements
  (99992, 71, 1, 1, 0, 0),  -- holiday
  (99992, 73, 1, 1, 0, 0),  -- utilities# (parent for holiday)
  (99992, 101, 1, 1, 0, 0), -- training
  (99992, 6, 1, 1, 0, 0),   -- tickets
  (99992, 141, 1, 1, 0, 0), -- knowledgebase# (parent)
  (99992, 142, 1, 1, 0, 0), -- categories
  (99992, 143, 1, 1, 0, 0), -- articles
  (99992, 144, 1, 1, 0, 0), -- knowledgebase
  (99992, 85, 1, 1, 0, 0),  -- performance# (parent)
  (99992, 86, 1, 1, 0, 0),  -- performance_indicator
  (99992, 87, 1, 1, 0, 0),  -- performance_report
  (99992, 88, 1, 1, 0, 0);  -- give_appraisal
