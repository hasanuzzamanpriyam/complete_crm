<?php
$client_lang = 'english';
?>

<div class="panel" id="sales_details">
    <div class="panel-body ">
        
        <div class="row mb-lg">
            <div class="col-lg-6 col-xs-6">
                <h5 class="p-md bg-items mr-15">
                    <?= lang('customer_details') ?>:
                </h5>
                <div class="pl-sm">
                    <?= lang('woocommerce_id') ?> : <?= $orders->customer_id ?>
                    <br/>
                    
                    <?= $orders->billing->first_name . " " . $orders->billing->last_name ?>
                    <br/><?= $orders->billing->company ?>
                    <br/><?= $orders->billing->phone ?>
                    <br/><?= $orders->billing->email ?>
                
                
                </div>
            </div>
            <div class="col-lg-3 col-xs-3 ">
                <h5 class="p-md bg-items ml-13">
                    <?= lang('billing_details') ?>:
                </h5>
                <div class="pl-sm">
                    <?php echo $orders->billing->address_1;
                    echo $orders->billing->address_2 . ",<br />";
                    echo $orders->billing->city . "<br />";
                    echo $orders->billing->state . ",<br />";
                    echo $orders->billing->country . "," . $orders->billing->postcode;
                    ?><br/>
                </div>
            </div>
            <div class="col-lg-3 col-xs-3 p-l-0 ">
                <h5 class="p-md bg-items ml-13">
                    <?= lang('shipping_details') ?>:
                </h5>
                <div class="pl-sm">
                    <?php echo $orders->shipping->address_1 . ",";
                    echo $orders->shipping->address_2 . "<br />";
                    echo $orders->shipping->city . ",<br />";
                    echo $orders->shipping->state . ",<br />";
                    echo $orders->shipping->country . "," . $orders->shipping->postcode;
                    ?><br/>
                </div>
            </div>
        
        </div>
        <style type="text/css">
            .dragger {
                background: url(../../../../assets/img/dragger.png) 0px 11px no-repeat;
                cursor: pointer;
            }

            .table > tbody > tr > td {
                vertical-align: initial;
            }
        </style>
        
        <div class="table-responsive mb-lg">
            <table class="table items invoice-items-preview" page-break-inside: auto;>
                <thead class="bg-items">
                <tr>
                    <th><?= lang('id') ?> #</th>
                    <th><?= lang('name') ?></th>
                    <?php
                    $invoice_view = config_item('invoice_view');
                    if (!empty($invoice_view) && $invoice_view == '2') {
                        ?>
                        <th><?= lang('hsn_code') ?></th>
                    <?php } ?>
                    <?php
                    $qty_heading = lang('qty');
                    if (isset($orders->show_quantity_as) && $orders->show_quantity_as == 'hours' || isset($hours_quantity)) {
                        $qty_heading = lang('hours');
                    } else if (isset($orders->show_quantity_as) && $orders->show_quantity_as == 'qty_hours') {
                        $qty_heading = lang('qty') . '/' . lang('hours');
                    }
                    ?>
                    <th><?php echo $qty_heading; ?></th>
                    <th class="col-sm-1"><?= lang('price') ?></th>
                    <th class="col-sm-2"><?= lang('tax') ?></th>
                    <th class="col-sm-1"><?= lang('total') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders->line_items as $item) {
                    echo '<tr><td>' . $item->id . '</td>';
                    echo '<td>' . $item->name . '</td>';
                    echo '<td>' . $item->quantity . '</td>';
                    echo '<td>' . $item->price . '</td>';
                    echo '<td>' . $item->total_tax . '</td>';
                    echo '<td>' . $item->total . '</td></tr>';
                } ?>
                </tbody>
            </table>
        </div>
        <div class="row" style="margin-top: 35px">
            <div class="col-xs-8">
                <?= $orders->customer_note ?>
            </div>
            <div class="col-sm-4 pv">
                <div class="clearfix">
                    <p class="pull-left"><?= lang('sub_total') ?></p>
                    <p class="pull-right mr">
                        <?= $orders->total ? display_money($orders->total) : '0.00' ?>
                    </p>
                </div>
                <?php if ($orders->discount_total > 0) : ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('discount') ?></p>
                        <p class="pull-right mr">
                            <?= $orders->discount_total ? display_money($orders->discount_total) : '0.00' ?>
                        </p>
                    </div>
                <?php endif ?>
                <?php if ($orders->discount_tax > 0) : ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('discount_tax') ?></p>
                        <p class="pull-right mr">
                            <?= $orders->discount_tax ? display_money($orders->discount_tax) : '0.00' ?>
                        </p>
                    </div>
                <?php endif ?>
                <?php if ($orders->shipping_total > 0) : ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('discount') ?></p>
                        <p class="pull-right mr">
                            <?= $orders->shipping_total ? display_money($orders->shipping_total) : '0.00' ?>
                        </p>
                    </div>
                <?php endif ?>
                <?php if ($orders->total_tax > 0) : ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('total') . ' ' . lang('tax') ?></p>
                        <p class="pull-right mr">
                            <?= display_money($orders->total_tax); ?>
                        </p>
                    </div>
                <?php endif;
                ?>
                <div class="clearfix">
                    <p class="pull-left"><?= lang('total') ?></p>
                    <p class="pull-right mr">
                        <?= display_money($orders->total, $orders->currency_symbol) ?>
                    </p>
                </div>
                
                <?php if (config_item('amount_to_words') == 'Yes') { ?>
                    <div class="clearfix">
                        <p class="pull-right h4"><strong class="h3"><?= lang('num_word') ?>
                                : </strong> <?= number_to_word($orders->currency, $orders->total); ?></p>
                    </div>
                <?php } ?>
            </div>
        
        </div>
    </div>
</div>

<?php include_once 'assets/js/sales.php'; ?>
