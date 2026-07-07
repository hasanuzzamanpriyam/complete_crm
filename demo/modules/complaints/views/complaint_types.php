<div class="table-responsive ">
    <table class="table table-hover" id="">
        <thead>
        <tr>
            <th class="col-sm-8"><?= lang('name') ?></th>
            <th class="col-sm-8"><?= lang('action') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (!empty($contracts_types)) {
            foreach ($contracts_types as $key => $val) {
                ?>
                <tr id="template_table_<?= $val->id ?>">
                    <td>
                        <?= $val->name ?>
                    </td>
                    
                    <td>
                        <a href="<?= base_url('admin/' . $module . '/new_complaint_type/' . $val->id); ?>"
                           class="btn btn-primary btn-xs" data-toggle="modal" data-placement="top"
                           data-target="#myModal" title="<?= lang('edit'); ?>"
                           data-original-title="<?= lang('edit'); ?>">
                            <i class="fa fa-pencil-square-o"></i></a>
                        <?php echo ajax_anchor(base_url('admin/' . $module . '/delete_complaint_type/' . $val->id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#template_table_" . $val->id)); ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
</div>