-- Spreadsheet redesign e2e fixtures (idempotent) — local dev DB tic_crm
-- Run:  Get-Content plan\_tools\spreadsheet-fixtures.sql -Raw | mysql -u root tic_crm
--
-- Deterministic data for composer test:spreadsheet:
--   My Folder (admin, staffid=1 forid):
--                    910001 root folder / 910002 file (editor route) /
--                    910003 nested folder (drill-down)
--   My Folder (client, staffid=1790 mailbox_client):
--                    910006 root folder / 910007 file (drill on client tab)
--   Share Folder (both roles): 910004 shared root folder
--                    (staffs_share='1' AND clients_share='1') /
--                    910005 its file child (share-by-link route)
--   hash rows      : 910001 (staff/1, id_share=910004 — admin share root) /
--                    910002 (staff/1, id_share=910005 — file_view_share route) /
--                    910003 (client/1, id_share=910004 — client share root)
--   client grant   : (mailbox_client user_id 1790, menu 24)
--   menu link      : menu_id 24 -> spreadsheet (R1 data)
--   company        : tbl_account_details.company = 1 for user 1790
--
-- WHY company: session client_id comes from tbl_account_details.company
-- (Login_model.php:124). mailbox_client has company=NULL, so client_id() is
-- empty and Spreadsheet::index()/manage() took the STAFF branch (verified
-- live: GET /spreadsheet -> 307 -> admin/spreadsheet/manage, i.e. the ADMIN
-- layout). Spec 7 requires the client response to use client/_layout_main
-- content markers, so the fixture links mailbox_client to existing client_id 1
-- ("Moktadir Rahman", tbl_client row verified). Side effects checked: mailbox
-- gate hits client/Mailbox.php which loads client/_layout_main unconditionally
-- (no client_id branch); only mailbox-redesign-test.php uses mailbox_client;
-- select_client_roll has no ORDER BY so the login redirect (client_menu[0])
-- stays client/dashboard (grant 24 appends last); menu/smoke gates never touch
-- tbl_client_role/user 1790.
--
-- My-folder rows: staffid = my_id() (Spreadsheet_model.php:110-115), so the
-- admin sees staffid=1 rows and the client sees staffid=1790 rows — two trees
-- are required. Files copy data_form from an existing file so
-- file_view_share/new_file_view never pass NULL to strpos().

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910001, '0', 'SS Redesign Fixture Folder', 'folder', '', 1, 'my_folder', NULL, '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910001) AS a);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910002, '910001', 'SS Redesign Fixture File', 'file', '12 KB', 1, 'my_folder',
       (SELECT s.data_form FROM (SELECT data_form AS data_form FROM tbl_spreadsheet_my_folder WHERE type = 'file' AND data_form IS NOT NULL AND data_form <> '' LIMIT 1) AS s),
       '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910002) AS b);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910003, '910001', 'SS Redesign Nested Folder', 'folder', '', 1, 'my_folder', NULL, '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910003) AS c);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id, staffs_share, clients_share)
SELECT 910004, '0', 'SS Redesign Shared Folder', 'folder', '', 1, 'my_folder', NULL, '', '', '1', '1'
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910004) AS d);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910005, '910004', 'SS Redesign Shared File', 'file', '12 KB', 1, 'my_folder',
       (SELECT s.data_form FROM (SELECT data_form AS data_form FROM tbl_spreadsheet_my_folder WHERE type = 'file' AND data_form IS NOT NULL AND data_form <> '' LIMIT 1) AS s),
       '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910005) AS e);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910006, '0', 'SS Redesign Client Folder', 'folder', '', 1790, 'my_folder', NULL, '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910006) AS i);

INSERT INTO tbl_spreadsheet_my_folder (id, parent_id, name, type, size, staffid, category, data_form, rel_type, rel_id)
SELECT 910007, '910006', 'SS Redesign Client File', 'file', '12 KB', 1790, 'my_folder',
       (SELECT s.data_form FROM (SELECT data_form AS data_form FROM tbl_spreadsheet_my_folder WHERE type = 'file' AND data_form IS NOT NULL AND data_form <> '' LIMIT 1) AS s),
       '', ''
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_my_folder WHERE id = 910007) AS j);

INSERT INTO tbl_spreadsheet_hash_share (id, rel_type, rel_id, id_share, hash, role)
SELECT 910001, 'staff', 1, 910004, 'ssfolderhash910001', 2
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_hash_share WHERE id = 910001) AS f);

INSERT INTO tbl_spreadsheet_hash_share (id, rel_type, rel_id, id_share, hash, role)
SELECT 910002, 'staff', 1, 910005, 'ssfixturehash910002', 2
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_hash_share WHERE id = 910002) AS g);

INSERT INTO tbl_spreadsheet_hash_share (id, rel_type, rel_id, id_share, hash, role)
SELECT 910003, 'client', 1, 910004, 'ssfolderhash910003', 2
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM tbl_spreadsheet_hash_share WHERE id = 910003) AS k);

INSERT INTO tbl_client_role (user_id, menu_id)
SELECT 1790, 24
WHERE NOT EXISTS (SELECT 1 FROM (SELECT client_role_id FROM tbl_client_role WHERE user_id = 1790 AND menu_id = 24) AS h);

UPDATE tbl_client_menu SET link = 'spreadsheet'
WHERE menu_id = 24 AND label = 'spreadsheet' AND link = 'client/spreadsheet';

UPDATE tbl_account_details SET company = 1
WHERE user_id = 1790 AND (company IS NULL OR company = '');

SELECT (SELECT COUNT(*) FROM tbl_spreadsheet_my_folder WHERE id BETWEEN 910001 AND 910007) AS folder_rows,
       (SELECT COUNT(*) FROM tbl_spreadsheet_hash_share WHERE id IN (910001, 910002, 910003)) AS hash_rows,
       (SELECT COUNT(*) FROM tbl_client_role WHERE user_id = 1790 AND menu_id = 24) AS grant_rows,
       (SELECT link FROM tbl_client_menu WHERE menu_id = 24) AS menu_link,
       (SELECT company FROM tbl_account_details WHERE user_id = 1790) AS company;
