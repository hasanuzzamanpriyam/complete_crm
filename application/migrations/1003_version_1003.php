<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A2-7 — sidebar grouping.
 *
 * Adds one additive column. Safe to leave in place after a code rollback: the
 * renderer skips grouping when the column is absent, and the DEFAULT keeps
 * every existing row valid.
 */
class Migration_Version_1003 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query(
            "ALTER TABLE `tbl_menu`
             ADD COLUMN `menu_group` VARCHAR(32) NOT NULL DEFAULT 'tools' AFTER `parent`"
        );

        // Top-level menus only; children inherit their parent's group when the
        // sidebar renders. 45 ids, matching plan/_tools/menu-group-test.php.
        $map = [
            'crm'      => [1, 2, 4, 5, 227, 228, 139, 141],
            'projects' => [6, 54, 55, 56, 57, 58, 233, 234, 269],
            'hr'       => [70, 72, 73, 85, 100, 101, 102, 105, 209, 210, 211, 212],
            'finance'  => [12, 29, 75, 89, 150, 213],
            'tools'    => [24, 25, 26, 42, 110, 235, 236, 248, 263],
            'admin'    => [243],
        ];

        foreach ($map as $group => $ids) {
            $in = implode(',', array_map('intval', $ids));
            $this->db->query("UPDATE `tbl_menu` SET `menu_group` = '{$group}' WHERE `menu_id` IN ({$in})");
        }
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `tbl_menu` DROP COLUMN `menu_group`");
    }
}
