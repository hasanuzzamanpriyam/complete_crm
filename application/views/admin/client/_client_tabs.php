<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * Client-specific tab rail + content pane.
 *
 * Replicates the CONTRACT of application/views/admin/common/tabs.php
 * (echo $global → iterate $all_tabs → lang($v_tab['name']) + count badge →
 * tab_load_view($all_tabs, $active) + load the pane view) with Soft-UI
 * cl-side-nav / cl-count-badge markup. The shared rail serves project/lead/
 * task details too — it is NEVER edited for this redesign.
 *
 * Tabs are sorted by position; client_details_tabs() renumbers them 1..14
 * (the old duplicate position 6 on estimates/proposals is fixed) so the
 * order is deterministic regardless of declaration order.
 */
if (!empty($global)) {
    echo $global;
}

$cl_tabs = is_array($all_tabs ?? null) ? $all_tabs : array();
if (!empty($cl_tabs)) {
    uasort($cl_tabs, function ($a, $b) {
        return ((int) ($a['position'] ?? 0)) <=> ((int) ($b['position'] ?? 0));
    });
}
?>
<nav class="cl-side-nav">
    <ul style="list-style: none; padding: 0; margin: 0;">
        <?php foreach ($cl_tabs as $key => $v_tab) :
            $cl_active = ($active == $key);
            ?>
            <li<?= $cl_active ? ' class="active"' : '' ?>>
                <a href="<?= base_url($v_tab['url']) ?>"<?= $cl_active ? ' class="active"' : '' ?>>
                    <?php if (!empty($v_tab['icon'])) { ?>
                        <span class="cl-ico"><i class="<?= $v_tab['icon'] ?>"></i></span>
                    <?php } ?>
                    <?= lang($v_tab['name']) ?>
                    <?php if (!empty($v_tab['count'])) { ?>
                        <span class="cl-count-badge"><?= $v_tab['count'] ?></span>
                    <?php } ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
<div class="cl-details-pane">
    <?php
    $view = tab_load_view($all_tabs, $active);
    $this->load->view($view);
    ?>
</div>
