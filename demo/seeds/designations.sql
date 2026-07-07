INSERT INTO tbl_designations (designations_id, departments_id, designations) VALUES
  (99991, 12, 'Demo Manager'),
  (99992, 12, 'Demo Employee');

INSERT INTO tbl_user_role (designations_id, menu_id, view, created, edited, deleted)
SELECT 99991, menu_id, view, created, edited, deleted
FROM tbl_user_role WHERE designations_id = 29;

SELECT 'OK' as result;
