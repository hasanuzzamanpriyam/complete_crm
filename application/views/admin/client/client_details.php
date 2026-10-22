<?= message_box('success'); ?>
<?= message_box('error');

$eeror_message = $this->session->userdata('error');
if (!empty($eeror_message)) : foreach ($eeror_message as $key => $message) :
?>
        <div class="alert alert-danger">
            <?php echo $message; ?>
        </div>
<?php
    endforeach;
endif;
$this->session->unset_userdata('error');

?>
<?php
$all_bug_info = $this->client_model->get_permission('tbl_bug');
$total_bugs = 0;
if (!empty($all_bug_info)) {
    foreach ($all_bug_info as $v_bugs) {
        if (!empty($v_bugs)) {
            $profile = $this->db->where(array('user_id' => $v_bugs->reporter))->get('tbl_account_details')->row();
            if (!empty($profile->company)) {
                if ($profile->company == $client_details->client_id) {
                    $total_bugs += count(array($v_bugs->bug_id));
                }
            }
        }
    }
}
$recently_paid = $this->db
    ->where('paid_by', $client_details->client_id)
    ->order_by('created_date', 'desc')
    ->get('tbl_payments')
    ->result();
$all_tickets_info = $this->client_model->get_permission('tbl_tickets');
$total_tickets = 0;
if (!empty($all_tickets_info)) {
    foreach ($all_tickets_info as $v_tickets_info) {
        if (!empty($v_tickets_info)) {
            $profile_info = $this->db->where(array('user_id' => $v_tickets_info->reporter))->get('tbl_account_details')->row();
            if (!empty($profile_info->company))
                if ($profile_info->company == $client_details->client_id) {
                    $total_tickets += count(array($v_tickets_info->tickets_id));
                }
        }
    }
}
$all_project = $this->db->where('client_id', $client_details->client_id)->get('tbl_project')->result();
$client_notes = $this->db->where(array('user_id' => $client_details->client_id, 'is_client' => 'Yes'))->get('tbl_notes')->result();

$client_outstanding = $this->invoice_model->client_outstanding($client_details->client_id);
$client_payments = $this->invoice_model->get_sum('tbl_payments', 'amount', $array = array('paid_by' => $client_details->client_id));
$client_payable = $client_payments + $client_outstanding;
$client_currency = $this->invoice_model->client_currency_symbol($client_details->client_id);
if (!empty($client_currency)) {
    $cur = $client_currency->symbol;
} else {
    $currency = get_row('tbl_currencies', array('code' => config_item('default_currency')));
    $cur = $currency->symbol;
}
if ($client_payable > 0 and $client_payments > 0) {
    $perc_paid = round(($client_payments / $client_payable) * 100, 1);
    if ($perc_paid > 100) {
        $perc_paid = '100';
    }
} else {
    $perc_paid = 0;
}
$client_transactions = $this->db->where('paid_by', $client_details->client_id)->get('tbl_transactions')->result();
$all_proposals_info = $this->db->where(array('module' => 'client', 'module_id' => $client_details->client_id))->order_by('proposals_id', 'DESC')->get('tbl_proposals')->result();
$edited = can_action('4', 'edited');
$notified_reminder = count(array($this->db->where(array('module' => 'client', 'module_id' => $client_details->client_id, 'notified' => 'No'))->get('tbl_reminders')->result()));
?>
<?php
// Shared client asset pipeline (client_modern.css) — same pattern as manage_client.
$this->load->view('admin/client/_client_assets');
?>
<div class="cl-root cl-details">
    <div class="cl-kpi-row" style="grid-column: 1 / -1;">
        <div class="cl-kpi">
            <div class="cl-ico cl-ico-mint"><i class="fa fa-money"></i></div>
            <div class="cl-body">
                <div class="cl-v"><?php
                                            if (!empty($client_payments)) {
                                                echo display_money($client_payments, client_currency($client_details->client_id));
                                            } else {
                                                echo '0.00';
                                            }
                ?></div>
                <div class="cl-l"><?= lang('paid_amount') ?></div>
            </div>
        </div>
        <div class="cl-kpi">
            <div class="cl-ico cl-ico-cyan"><i class="fa fa-usd"></i></div>
            <div class="cl-body">
                <div class="cl-v"><?php
                                            if ($client_outstanding > 0) {
                                                echo display_money($client_outstanding, client_currency($client_details->client_id));
                                            } else {
                                                echo '0.00';
                                            }
                ?></div>
                <div class="cl-l"><?= lang('due_amount') ?></div>
            </div>
        </div>
        <div class="cl-kpi">
            <div class="cl-ico cl-ico-amber"><i class="fa fa-file-text-o"></i></div>
            <div class="cl-body">
                <div class="cl-v">
                            <?php
                            if ($client_payable > 0) {
                                echo display_money($client_payable, client_currency($client_details->client_id));
                            } else {
                                echo '0.00';
                            }
                            ?></div>
                <div class="cl-l"><?= lang('invoice_amount') ?></div>
            </div>
        </div>
        <div class="cl-kpi">
            <div class="cl-ico cl-ico-purple"><i class="fa fa-percent"></i></div>
            <div class="cl-body">
                <div class="cl-v"><?= $perc_paid ?>%</div>
                <div class="cl-l"><?= lang('paid') . ' ' . lang('percentage') ?></div>
            </div>
        </div>
    </div>
    <?php
    // Client-specific rail + pane — replaces admin/common/tabs (shared rail untouched).
    $this->load->view('admin/client/_client_tabs');
    ?>
</div>