<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= lang('renew') . ' ' . lang('contract') ?>
            - <?= $title_suffix ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        
        <?php
        if (!empty($contract->start_date)) {
            $start_date = date('Y-m-d', strtotime($contract->start_date));
            $end_date = date('Y-m-d', strtotime($contract->end_date));
            $contract_value = $contract->contract_value;
        }
        ?>
        
        <form action="<?= base_url('admin/' . $module . '/save_renew/' . $id); ?>"
              class="form-horizontal form-groups-bordered" role="form" method="post" enctype="multipart/form-data"
              accept-charset="utf-8" novalidate="novalidate">
            <div class="modal-body clearfix">
                <div class="form-group">
                    <label for="start_date" class="col-lg-3 control-label"><?= lang('start_date') ?> <span
                                class="text-danger">*</span></label>
                    <div class="col-lg-7">
                        <div class="input-group">
                            <input required type="text" name="start_date" id="start_date"
                                   class="form-control datepicker" value="<?= set_value('start_date', $start_date) ?>"
                                   data-date-format="<?= config_item('date_picker_format'); ?>" required="">
                            <div class="input-group-addon">
                                <a href="#"><i class="fa fa-calendar"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="end_date" class="col-lg-3 control-label"><?= lang('end_date') ?></label>
                    <div class=" col-lg-7">
                        <div class="input-group">
                            <input type="text" name="end_date" id="end_date" class="form-control datepicker"
                                   value="<?= set_value('end_date', $end_date) ?>"
                                   data-date-format="<?= config_item('date_picker_format'); ?>">
                            <div class="input-group-addon">
                                <a href="#"><i class="fa fa-calendar"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contract_value"
                           class="col-lg-3 control-label"><?= lang('new') . ' ' . lang('value') ?></label>
                    <div class="col-lg-7">
                        <input value="<?= set_value('contract_value', $contract_value) ?>" name="contract_value"
                               id="contract_value"
                               type="text" class="form-control">
                    </div>
                </div>
                <?php if ($contract->signed == 1) { ?>
                    <div class="form-group">
                        <label for="signed"
                               class="col-lg-3 control-label"><?= lang('keep_signature') ?></label>
                        <div class="col-lg-7">
                            <div class="checkbox c-checkbox">
                                <label class="needsclick">
                                    <input type="checkbox" name="keep_signature">
                                    <span class="fa fa-check"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <script type="text/javascript">
                        (function ($) {
                            "use strict";
                            // if keep_signature is checked then contract_value will be disabled
                            $('input[name="keep_signature"]').on('change', function () {
                                if ($(this).is(':checked')) {
                                    $('#contract_value').attr('disabled', 'disabled');
                                } else {
                                    $('#contract_value').removeAttr('disabled');
                                }
                            });
                        })(jQuery);
                    </script>
                <?php } ?>
                
                <input type="hidden" name="module" value="<?php echo $module; ?>" class="form-control">
                <input type="hidden" name="module_field_id" value="<?php echo $module_field_id; ?>"
                       class="form-control">
            </div>
            <div id="file-modal-footer"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default cancel-upload" data-dismiss="modal"><span
                            class="fa fa-close"></span> <?php echo lang('close'); ?></button>
                <button type="submit" class="btn btn-primary "><span
                            class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
            </div>
        </form>
    
    
    </div>
</div>