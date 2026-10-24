<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<?php
$this->load->view('admin/client/_client_assets');
$data['assign_user'] = $this->client_model->allowed_user('4');
?>
<div class="cl-root cl-manage">
    <div class="cl-card">
        <div class="cl-tabs">
            <a href="<?= base_url('admin/client/manage_client') ?>"><?= lang('client_list') ?></a>
            <a href="<?= base_url('admin/client/create_client') ?>"><?= lang('new_client') ?></a>
            <a class="active" href="<?= base_url('admin/client/import') ?>"><?= lang('import') . ' ' . lang('client') ?></a>
        </div>

        <div class="cl-form-card">
            <form role="form" enctype="multipart/form-data" id="form" data-parsley-validate="" novalidate=""
                  action="<?= base_url('admin/client/save_imported') ?>" method="post">
                <div class="cl-form-body">
                    <div class="cl-frow">
                        <label><?= lang('choose_file') ?> <span class="cl-req">*</span></label>
                        <div class="cl-dropzone" id="import-dropzone">
                            <div class="cl-dz-ico">⬆</div>
                            Drag & drop file here, or <b>browse files</b>
                            <input type="file" name="upload_file" id="import-file" hidden accept=".xlsx,.xls,.csv">
                        </div>
                    </div>

                    <div class="cl-frow">
                        <label><?= lang('customer_group') ?></label>
                        <div class="cl-input-group">
                            <select name="customer_group_id" class="cl-input" style="width: 100%">
                                <?php
                                $all_customer_group = $this->db->where('type', 'client')->get('tbl_customer_group')->result();
                                if (!empty($all_customer_group)) {
                                    foreach ($all_customer_group as $customer_group) : ?>
                                        <option value="<?= $customer_group->customer_group_id ?>"
                                                <?php
                                                if (!empty($client_info->customer_group_id) && $client_info->customer_group_id == $customer_group->customer_group_id) {
                                                    echo 'selected';
                                                }
                                                ?>><?= $customer_group->customer_group; ?></option>
                                    <?php endforeach;
                                }
                                $created = can_action('125', 'created');
                                ?>
                            </select>
                            <?php if (!empty($created)) { ?>
                                <div class="cl-plus-inline"
                                     title="<?= lang('new') . ' ' . lang('customer_group') ?>"
                                     data-toggle="tooltip" data-placement="top">
                                    <a data-toggle="modal" data-target="#myModal"
                                       href="<?= base_url() ?>admin/client/customer_group"><i class="fa fa-plus"></i></a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="cl-frow">
                        <label><?= lang('language') ?></label>
                        <select name="language" class="cl-input" style="width: 100%">
                            <?php
                            foreach ($languages as $lang) : ?>
                                <option value="<?= $lang->name ?>" <?php
                                if (!empty($client_info->language) && $client_info->language == $lang->name) {
                                    echo 'selected';
                                } elseif (empty($client_info->language) && $this->config->item('language') == $lang->name) {
                                    echo 'selected';
                                } ?>><?= ucfirst($lang->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cl-frow">
                        <label><?= lang('currency') ?></label>
                        <select name="currency" class="cl-input" style="width: 100%">
                            <?php if (!empty($currencies)) : foreach ($currencies as $currency) : ?>
                                <option value="<?= $currency->code ?>" <?php
                                if (!empty($client_info->currency) && $client_info->currency == $currency->code) {
                                    echo 'selected';
                                } elseif (empty($client_info->currency) && $this->config->item('default_currency') == $currency->code) {
                                    echo 'selected';
                                } ?>><?= $currency->name ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="cl-frow">
                        <label><?= lang('country') ?></label>
                        <select name="country" class="cl-input" style="width: 100%">
                            <optgroup label="Default Country">
                                <option value="<?= $this->config->item('company_country') ?>">
                                    <?= $this->config->item('company_country') ?></option>
                            </optgroup>
                            <optgroup label="<?= lang('other_countries') ?>">
                                <?php if (!empty($countries)) : foreach ($countries as $country) : ?>
                                    <option value="<?= $country->value ?>"
                                            <?= (!empty($client_info->country) && $client_info->country == $country->value ? 'selected' : NULL) ?>>
                                        <?= $country->value ?></option>
                                <?php endforeach; endif; ?>
                            </optgroup>
                        </select>
                    </div>

                    <?php
                    if (!empty($client_info->permission)) {
                        $permissionL = $client_info->permission;
                    } else {
                        $permissionL = null;
                    }
                    ?>
                    <?= get_permission(3, 9, $assign_user, $permissionL, ''); ?>

                </div>
                <div class="cl-form-foot">
                    <button type="submit" class="cl-btn cl-btn-primary"><?= lang('upload') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>