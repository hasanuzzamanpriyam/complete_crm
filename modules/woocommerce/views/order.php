<?php
$orders = isset($orders) ? $orders : null;
$row = isset($row) ? $row : null;
$apiOk = is_object($orders);

$e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
$addr = function ($a) {
    if (!is_object($a)) {
        return '';
    }
    $bits = array();
    foreach (array('address_1', 'address_2', 'city', 'state', 'postcode', 'country') as $f) {
        if (!empty($a->{$f})) {
            $bits[] = $a->{$f};
        }
    }
    return implode(', ', $bits);
};

$bill = ($apiOk && !empty($orders->billing) && is_object($orders->billing)) ? $orders->billing : null;
$ship = ($apiOk && !empty($orders->shipping) && is_object($orders->shipping)) ? $orders->shipping : null;
$billAddr = $addr($bill);
$shipAddr = $addr($ship);
$custName = $bill ? trim($bill->first_name . ' ' . $bill->last_name) : '';
$currencySymbol = ($apiOk && !empty($orders->currency_symbol)) ? $orders->currency_symbol : '';

$lineItems = ($apiOk && !empty($orders->line_items) && is_array($orders->line_items)) ? $orders->line_items : array();
$subTotal = 0.0;
foreach ($lineItems as $item) {
    $subTotal += ((float) $item->quantity) * ((float) $item->price);
}
if (empty($lineItems) && $apiOk && isset($orders->total)) {
    $subTotal = (float) $orders->total;
}

$shipMethod = '';
if ($apiOk && !empty($orders->shipping_lines) && is_array($orders->shipping_lines)
    && isset($orders->shipping_lines[0]->method_title)) {
    $shipMethod = $orders->shipping_lines[0]->method_title;
}
?>
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/woocommerce_modern.css?v=1">
<div class="woo-modern" id="sales_details">
    <div class="dt-bar">
        <a href="<?= base_url('admin/woocommerce/orders') ?>" class="mini-btn">&lsaquo; <?= lang('back_to_orders') ?></a>
        <span class="dt-title">#<?= $e($row->order_number) ?></span>
        <?= woo_pill($row->status) ?>
        <span class="dt-when"><?= display_date($row->date_created) ?><?= !empty($store_name) ? ' &middot; ' . $e($store_name) : '' ?></span>
        <div class="tb-right">
            <button type="button" class="btn-ghost" onclick="window.print()"><i class="fa fa-print"></i> <?= lang('print') ?></button>
            <a class="btn-accent" href="<?= base_url('admin/woocommerce/edit_orders/' . $row->id) ?>"
               data-toggle="modal" data-target="#myModal_lg"><?= lang('edit_status') ?></a>
        </div>
    </div>

    <div class="dt-grid">
        <section class="card">
            <div class="card-h">
                <div>
                    <h3><?= lang('customer') ?></h3>
                    <div class="sub"><?= !empty($store_name) ? $e($store_name) : '&mdash;' ?></div>
                </div>
            </div>
            <div class="card-b">
                <div class="kv"><span class="k"><?= lang('name') ?></span><span class="val"><?= $e($custName !== '' ? $custName : ($bill && !empty($bill->email) ? $bill->email : '—')) ?></span></div>
                <div class="kv"><span class="k"><?= lang('email') ?></span><span class="val"><?= $e($bill && !empty($bill->email) ? $bill->email : '—') ?></span></div>
                <div class="kv"><span class="k"><?= lang('phone') ?></span><span class="val"><?= $e($bill && !empty($bill->phone) ? $bill->phone : '—') ?></span></div>
            </div>
        </section>
        <section class="card">
            <div class="card-h"><div><h3><?= lang('billing_details') ?></h3></div></div>
            <div class="card-b">
                <div class="kv"><span class="k"><?= lang('billing_address') ?></span><span class="val"><?= $e($billAddr !== '' ? $billAddr : '—') ?></span></div>
                <div class="kv"><span class="k"><?= lang('payment') ?></span><span class="val"><?= lang('woocommerce') ?></span></div>
                <div class="kv"><span class="k"><?= lang('currency') ?></span><span class="val"><?= $e($row->currency) ?></span></div>
            </div>
        </section>
        <section class="card">
            <div class="card-h"><div><h3><?= lang('shipping_details') ?></h3></div></div>
            <div class="card-b">
                <div class="kv"><span class="k"><?= lang('shipping_address') ?></span><span class="val"><?= $e($shipAddr !== '' ? $shipAddr : '—') ?></span></div>
                <?php if ($shipMethod !== '') { ?>
                    <div class="kv"><span class="k"><?= lang('shipping') ?></span><span class="val"><?= $e($shipMethod) ?></span></div>
                <?php } ?>
            </div>
        </section>
    </div>

    <div class="dt-2">
        <section class="card">
            <div class="card-h">
                <div>
                    <h3><?= lang('line_items') ?></h3>
                    <div class="sub"><?= lang('woocommerce') ?></div>
                </div>
            </div>
            <div class="card-b" style="padding:12px 0 0">
                <?php if (!empty($lineItems)) { ?>
                    <div class="tbl-wrap">
                        <table class="tbl" style="min-width:520px">
                            <thead>
                            <tr>
                                <th><?= lang('name') ?></th>
                                <th class="t-r"><?= lang('qty') ?></th>
                                <th class="t-r"><?= lang('price') ?></th>
                                <th class="t-r"><?= lang('total') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($lineItems as $item) { ?>
                                <tr>
                                    <td><div class="nm" style="max-width:420px"><?= $e($item->name) ?></div></td>
                                    <td class="num"><?= (int) $item->quantity ?></td>
                                    <td class="num"><?= display_money($item->price, $currencySymbol) ?></td>
                                    <td class="num"><?= display_money($item->total, $currencySymbol) ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="empty">
                        <div class="big">&#9776;</div>
                        <b><?= lang('line_items') ?></b>
                        <?= $apiOk ? lang('nothing_found_sorry') : lang('woo_details_unavailable') ?>
                    </div>
                <?php } ?>
            </div>
        </section>
        <section class="card">
            <div class="card-h"><div><h3><?= lang('order_totals') ?></h3></div></div>
            <div class="card-b">
                <div class="tot-row"><span><?= lang('sub_total') ?></span><span class="tv"><?= display_money($subTotal, $currencySymbol) ?></span></div>
                <div class="tot-row"><span><?= lang('discount') ?></span><span class="tv"><?= display_money(($apiOk && isset($orders->discount_total)) ? $orders->discount_total : 0, $currencySymbol) ?></span></div>
                <div class="tot-row"><span><?= lang('shipping') ?></span><span class="tv"><?= display_money(($apiOk && isset($orders->shipping_total)) ? $orders->shipping_total : 0, $currencySymbol) ?></span></div>
                <div class="tot-row"><span><?= lang('tax') ?></span><span class="tv"><?= display_money(($apiOk && isset($orders->total_tax)) ? $orders->total_tax : 0, $currencySymbol) ?></span></div>
                <div class="tot-row grand"><span><?= lang('total') ?></span><span class="tv"><?= display_money(($apiOk && isset($orders->total)) ? $orders->total : 0, $currencySymbol) ?></span></div>
            </div>
        </section>
    </div>

    <?php if ($apiOk && !empty($orders->customer_note)) { ?>
        <div class="note-box"><b><?= lang('customer_note') ?></b><?= $e($orders->customer_note) ?></div>
    <?php } ?>
</div>

<?php include_once 'assets/js/sales.php'; ?>
