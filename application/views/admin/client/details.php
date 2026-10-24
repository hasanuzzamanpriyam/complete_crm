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
$this->session->unset_userdata('error'); ?>

<?php
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
$edited = can_action('4', 'edited');
?>

<!-- Profile Hero -->
<div class="cl-profile-hero">
        <div class="cl-pav cl-av-mint">
            <?php
            // Initials from company name
            $words = preg_split('/\s+/', trim($client_details->name));
            $initials = '';
            foreach ($words as $w) {
                if ($w !== '') $initials .= mb_substr($w, 0, 1);
            }
            $initials = strtoupper(mb_substr($initials, 0, 2));
            echo $initials ?: 'CL';
            ?>
        </div>
        <div>
            <h2><?php echo html_escape($client_details->name); ?></h2>
            <div class="cl-sub">
                <?php
                // Group + location + email
                $group_name = '';
                if (!empty($client_details->customer_group_id)) {
                    $grp = $this->db->where('customer_group_id', $client_details->customer_group_id)->get('tbl_customer_group')->row();
                    if ($grp) $group_name = $grp->name;
                }
                $location_parts = array_filter([$client_details->city, $client_details->country], fn($v) => !empty($v));
                $location = !empty($location_parts) ? implode(', ', $location_parts) : lang('not_available');
                echo lang('customer_group') . ': ' . ($group_name ?: lang('not_available')) . ' · ' . $location . ' · ' . html_escape($client_details->email);
                ?>
            </div>
        </div>
        <div class="cl-spacer"></div>
        <?php if ($edited): ?>
            <a class="cl-pbtn" href="<?php echo base_url() ?>admin/client/create_client/<?= $client_details->client_id ?>">
                <i class="fa fa-edit"></i> <?= lang('edit') ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Info Grid: Contact details / Address & hosting -->
    <div class="cl-info-grid">
        <!-- Column 1: Contact details -->
        <div class="cl-info-col">
            <h4><?= lang('contact_details') ?></h4>

            <div class="cl-info-row">
                <span class="cl-k"><?= lang('name') ?></span>
                <span class="cl-v"><?= html_escape($client_details->name) ?></span>
            </div>

            <div class="cl-info-row">
                <span class="cl-k"><?= lang('contact_person') ?></span>
                <span class="cl-v">
                    <?php
                    if ($client_details->primary_contact != 0) {
                        $contacts = $client_details->primary_contact;
                    } else {
                        $contacts = NULL;
                    }
                    $primary_contact = $this->client_model->check_by(array('user_id' => $contacts), 'tbl_account_details');
                    if ($primary_contact) {
                        echo html_escape($primary_contact->fullname);
                    }
                    ?>
                </span>
            </div>

            <div class="cl-info-row">
                <span class="cl-k"><?= lang('email') ?></span>
                <span class="cl-v"><?= html_escape($client_details->email) ?></span>
            </div>

            <div class="cl-info-row">
                <span class="cl-k"><?= lang('city') ?></span>
                <span class="cl-v"><?= html_escape($client_details->city) ?></span>
            </div>

            <div class="cl-info-row">
                <span class="cl-k"><?= lang('zipcode') ?></span>
                <span class="cl-v"><?= html_escape($client_details->zipcode) ?></span>
            </div>

            <?php $show_custom_fields = custom_form_label(12, $client_details->client_id);
            if (!empty($show_custom_fields)) {
                foreach ($show_custom_fields as $c_label => $v_fields) {
                    if (!empty($v_fields)) {
                        ?>
                        <div class="cl-info-row">
                            <span class="cl-k"><?= html_escape($c_label) ?></span>
                            <span class="cl-v"><?= $v_fields ?></span>
                        </div>
                        <?php
                    }
                }
            } ?>

            <?php if (!empty($client_details->website)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('website') ?></span>
                <span class="cl-v"><a href="<?= html_escape($client_details->website) ?>" target="_blank" rel="noopener"><?= html_escape($client_details->website) ?></a></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($client_details->skype_id)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('skype_id') ?></span>
                <span class="cl-v"><?= html_escape($client_details->skype_id) ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($client_details->facebook)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('facebook_profile_link') ?></span>
                <span class="cl-v"><a href="<?= html_escape($client_details->facebook) ?>" target="_blank" rel="noopener"><?= html_escape($client_details->facebook) ?></a></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($client_details->twitter)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('twitter_profile_link') ?></span>
                <span class="cl-v"><a href="<?= html_escape($client_details->twitter) ?>" target="_blank" rel="noopener"><?= html_escape($client_details->twitter) ?></a></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($client_details->linkedin)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('linkedin_profile_link') ?></span>
                <span class="cl-v"><a href="<?= html_escape($client_details->linkedin) ?>" target="_blank" rel="noopener"><?= html_escape($client_details->linkedin) ?></a></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Column 2: Address & hosting -->
        <div class="cl-info-col">
            <h4><?= lang('address_and_hosting') ?></h4>

            <div class="cl-info-row">
                <span class="cl-k"><?= lang('address') ?></span>
                <span class="cl-v"><?= html_escape($client_details->address) ?></span>
            </div>

            <div class="cl-info-row">
                <span class="cl-k"><?= lang('phone') ?></span>
                <span class="cl-v"><a href="tel:<?= html_escape($client_details->phone) ?>"><?= html_escape($client_details->phone) ?></a></span>
            </div>

            <?php if (!empty($client_details->mobile)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('mobile') ?></span>
                <span class="cl-v"><a href="tel:<?= html_escape($client_details->mobile) ?>"><?= html_escape($client_details->mobile) ?></a></span>
            </div>
            <?php endif; ?>

            <div class="cl-info-row">
                <span class="cl-k"><?= lang('fax') ?></span>
                <span class="cl-v"><?= html_escape($client_details->fax) ?></span>
            </div>

            <?php if (!empty($client_details->hosting_company)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('hosting_company') ?></span>
                <span class="cl-v"><?= html_escape($client_details->hosting_company) ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($client_details->hostname)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('hostname') ?></span>
                <span class="cl-v"><?= html_escape($client_details->hostname) ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($client_details->username)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('username') ?></span>
                <span class="cl-v"><?= html_escape($client_details->username) ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($client_details->password)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('password') ?></span>
                <span class="cl-v">
                    <span id="show_password">
                        <?php
                        $hosting_password = strlen(decrypt($client_details->password));
                        if (!empty($hosting_password)) {
                            for ($p = 1; $p <= $hosting_password; $p++) {
                                echo '*';
                            }
                        }
                        ?>
                    </span>
                    <a data-toggle="modal" data-target="#myModal"
                       href="<?= base_url('admin/client/see_password/c_' . $client_details->client_id) ?>"
                       id="see_password"><?= lang('see_password') ?></a>
                    <strong id="hosting_password" class="required"></strong>
                </span>
            </div>
            <?php endif; ?>

            <?php if (!empty($client_details->port)): ?>
            <div class="cl-info-row">
                <span class="cl-k"><?= lang('port') ?></span>
                <span class="cl-v"><?= html_escape($client_details->port) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer: Totals summary (clean, no legacy chart) -->
    <div class="cl-panel-foot">
        <div class="cl-info-row">
            <span class="cl-k"><?= lang('invoice_amount') ?></span>
            <span class="cl-v"><strong class="cl-tag cl-tag-mint"><?= display_money($client_payable, client_currency($client_details->client_id)) ?></strong></span>
        </div>
        <div class="cl-info-row">
            <span class="cl-k"><?= lang('paid_amount') ?></span>
            <span class="cl-v"><strong class="cl-money-pos"><?= display_money($client_payments, client_currency($client_details->client_id)) ?></strong></span>
        </div>
        <div class="cl-info-row">
            <span class="cl-k"><?= lang('outstanding') ?></span>
            <span class="cl-v"><strong class="cl-money-neg"><?= display_money($client_outstanding, client_currency($client_details->client_id)) ?></strong></span>
        </div>
        <div class="cl-info-row">
            <span class="cl-k"><?= lang('paid_percentage') ?></span>
            <span class="cl-v"><strong><?= $perc_paid ?>%</strong></span>
        </div>
    </div>