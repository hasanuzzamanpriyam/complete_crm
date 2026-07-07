<?php include_once 'assets/admin-ajax.php'; ?>

<?php echo form_open('', array('id' => 'form', 'class' => 'form-horizontal', 'role' => 'form', 'data-parsley-validate' => '', 'novalidate' => '', 'enctype' => 'multipart/form-data', 'method' => 'post')); ?>
    <style>
        .wd-100 {
            width: 100px !important;
        }

        .w-100 {
            width: 100% !important;
        }
    </style>
    <div class="form-group">
        <label class="col-lg-3 control-label"></label>
        <div class="col-lg-5">
            <div class="alert-danger">
                <?php
                if (!empty(validation_errors())) {
                    echo validation_errors();
                } ?>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="subject" class="col-lg-3 control-label"><?= lang('subject') ?> <span
                    class="text-danger">*</span></label>
        <div class="col-lg-5">
            <input type="text" class="form-control" value="<?= set_value('subject', $subject) ?>" name="subject"
                   id="subject" required="">
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('client') ?> <span class="text-danger">*</span>
        </label>
        <div class="col-lg-5">
            <select class="form-control select_box w-100" required name="client_id"
                    onchange="get_project_by_id(this.value)">
                <option value=""><?= lang('select') . ' ' . lang('client') ?></option>
                <?php
                $client_id = set_value('client_id', $client_id);
                if (!empty($all_client)) {
                    foreach ($all_client as $v_client) {
                        ?>
                        <option value="<?= $v_client->client_id ?>" <?php
                        if (!empty($client_id)) {
                            echo $client_id == $v_client->client_id ? 'selected' : '';
                        }
                        ?>><?= ucfirst($v_client->name) ?></option>
                        <?php
                    }
                }
                ?>
            </select>
        
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('project') ?></label>
        <div class="col-lg-5">
            <select class="form-control wd-100" name="project_id" id="client_project">
                <option value=""><?= lang('none') ?></option>
                <?php
                if (!empty($client_id)) {
                    $project_id = set_value('project_id', $project_id);
                    $all_project = $this->db->where('client_id', $client_id)->get('tbl_project')->result();
                    if (!empty($all_project)) {
                        foreach ($all_project as $v_cproject) {
                            ?>
                            <option value="<?= $v_cproject->project_id ?>" <?php
                            if (!empty($project_id)) {
                                echo $v_cproject->project_id == $project_id ? 'selected' : '';
                            }
                            ?>><?= $v_cproject->project_name ?></option>
                            <?php
                        }
                    }
                }
                ?>
            </select>
        </div>
    </div>
    
    
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('contract_type') ?> </label>
        <div class="col-lg-5">
            <div class="input-group">
                <select name="contract_type" class="form-control select_box w-100">
                    <option value=""><?= lang('select') . ' ' . lang('contract_type') ?></option>
                    <?php
                    $contract_type_info = $this->db->order_by('id', 'DESC')->get('tbl_contracts_types')->result();
                    if (!empty($contract_type_info)) {
                        foreach ($contract_type_info as $v) {
                            ?>
                            <option value="<?= $v->id ?>" <?= (!empty($contract_type) && $contract_type == $v->id ? 'selected' : '') ?>><?= $v->name; ?></option>
                            <?php
                        }
                    }
                    $_created = can_action_by_label('contracts', 'created');
                    ?>
                </select>
                <?php if (!empty($_created)) {
                    ?>
                    <div class="input-group-addon" title="<?= lang('new') . ' ' . lang('contract_type') ?>"
                         data-toggle="tooltip" data-placement="top">
                        <a data-toggle="modal" data-target="#myModal"
                           href="<?= base_url() ?>admin/contracts/new_contract_type"><i class="fa fa-plus"></i></a>
                    </div>
                <?php }
                ?>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="date_start" class="col-lg-3 control-label"><?= lang('start_date') ?> <span
                    class="text-danger">*</span></label>
        <div class="col-lg-5">
            <div class="input-group">
                <input required type="text" name="start_date" class="form-control datepicker"
                       value="<?= set_value('start_date', $start_date) ?>"
                       data-date-format="<?= config_item('date_picker_format'); ?>">
                <div class="input-group-addon">
                    <a href="#"><i class="fa fa-calendar"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="end_date" class="col-lg-3 control-label"><?= lang('end_date') ?></label>
        <div class="col-lg-5">
            <div class="input-group">
                <input type="text" name="end_date" class="form-control datepicker"
                       value="<?= set_value('end_date', $end_date) ?>"
                       data-date-format="<?= config_item('date_picker_format'); ?>">
                <div class="input-group-addon">
                    <a href="#"><i class="fa fa-calendar"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="contract_value" class="col-lg-3 control-label"><?= lang('contract_value') ?></label>
        <div class="col-lg-5">
            <input value="<?= set_value('contract_value', $contract_value) ?>" name="contract_value" id="contract_value"
                   type="text" class="form-control">
        </div>
    </div>
    
    
    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('description') ?> </label>
        <div class="col-lg-5">
            <textarea name="description"
                      class=" textarea form-control"><?= set_value('description', $description) ?></textarea>
        </div>
    </div>
    
    
    <div class="form-group">
        <label for="visible_to_client" class="col-sm-3 control-label"><?= lang('visible_to_client') ?>
            <span class="required">*</span></label>
        <div class="col-sm-6">
            <input data-toggle="toggle" name="visible_to_client" id="visible_to_client" value="Yes" <?php
            if (!empty($visible_to_client) && $visible_to_client == 'Yes') {
                echo 'checked';
            }
            ?> data-on="<?= lang('yes') ?>" data-off="<?= lang('no') ?>" data-onstyle="success" data-offstyle="danger"
                   type="checkbox">
        </div>
    </div>
    
    
    <div class="form-group">
        <label for="submit" class="col-lg-3 control-label"></label>
        <div class="col-lg-5">
            <div class="btn-bottom-toolbar text-right">
                <button type="submit" id="submit" class="btn btn-sm btn-primary"><?= lang('save') ?></button>
            </div>
        </div>
    </div>

<?php echo form_close(); ?>