-- Mailbox redesign e2e fixtures (idempotent) — local dev DB tic_crm
-- Run:  Get-Content plan\_tools\mailbox-fixtures.sql -Raw | mysql -u root tic_crm
--
-- Provides the deterministic data the test:mailbox gate asserts on:
--   * dedicated role-2 (client) test user mailbox_client / ClientMail@123
--     (password hash = sha512(password . encryption_key))
--   * its account profile + client menu grants (copied from user 13)
--   * admin inbox message  inbox_id = 900001  (user_id=1, mail_folder=inbox,
--     smtp_host_name=system — matches the admin inboxList filters)
--   * client inbox message inbox_id = 900002
-- Fixed IDs keep the test deterministic; guards make re-runs safe.

-- 1) client test user -------------------------------------------------------
INSERT INTO tbl_users (username, password, email, role_id, is_super_admin, activated, banned, created)
SELECT 'mailbox_client', '4fe65013fb0707526e0f52d7044254ba804a37c24163849f316f8b8377e90d8205f84f5846c6a8953a9b2cade72790c100074e672c6aabab06997fe2cd39c4c0', 'mailbox_client@tic.test', 2, 0, 1, 0, NOW()
WHERE NOT EXISTS (SELECT 1 FROM (SELECT user_id FROM tbl_users WHERE username = 'mailbox_client') AS x);

SET @uid = (SELECT user_id FROM tbl_users WHERE username = 'mailbox_client');

-- 2) its profile -------------------------------------------------------------
INSERT INTO tbl_account_details (user_id, fullname, language)
SELECT @uid, 'Mailbox Test Client', 'english'
WHERE NOT EXISTS (SELECT 1 FROM (SELECT account_details_id FROM tbl_account_details WHERE user_id = @uid) AS y);

-- 3) its client menu grants (copied from user 13) ----------------------------
INSERT INTO tbl_client_role (user_id, menu_id)
SELECT @uid, r.menu_id FROM tbl_client_role r
WHERE r.user_id = 13
  AND NOT EXISTS (SELECT 1 FROM (SELECT client_role_id FROM tbl_client_role WHERE user_id = @uid) AS z);

-- 4) admin inbox fixture (inbox_id 900001) -----------------------------------
INSERT INTO tbl_inbox (inbox_id, user_id, `to`, `from`, subject, message_body, attach_file, message_time, view_status, favourites, notify_me, deleted, mail_folder, smtp_host_name)
SELECT 900001, 1, 'hostnate247@gmail.com', 'Mailbox Fixtures <fixtures@tic.test>', 'Mailbox redesign fixture', 'Seeded message so the mailbox redesign e2e test can open a real read page and show list markup.', '[]', NOW(), 2, 0, 1, 'No', 'inbox', 'system'
WHERE NOT EXISTS (SELECT 1 FROM (SELECT inbox_id FROM tbl_inbox WHERE inbox_id = 900001) AS a);

-- 5) client inbox fixture (inbox_id 900002) ----------------------------------
INSERT INTO tbl_inbox (inbox_id, user_id, `to`, `from`, subject, message_body, attach_file, message_time, view_status, favourites, notify_me, deleted, mail_folder, smtp_host_name)
SELECT 900002, @uid, 'mailbox_client@tic.test', 'Mailbox Fixtures <fixtures@tic.test>', 'Mailbox redesign fixture (client)', 'Seeded client message so the client read-page assertion executes.', '[]', NOW(), 2, 0, 1, 'No', 'inbox', 'system'
WHERE NOT EXISTS (SELECT 1 FROM (SELECT inbox_id FROM tbl_inbox WHERE inbox_id = 900002) AS b);

-- verification ---------------------------------------------------------------
SELECT (SELECT user_id FROM tbl_users WHERE username = 'mailbox_client') AS client_uid,
       (SELECT COUNT(*) FROM tbl_client_role WHERE user_id = (SELECT user_id FROM tbl_users WHERE username = 'mailbox_client')) AS client_menus,
       (SELECT COUNT(*) FROM tbl_inbox WHERE inbox_id = 900001) AS admin_fixture,
       (SELECT COUNT(*) FROM tbl_inbox WHERE inbox_id = 900002) AS client_fixture;
