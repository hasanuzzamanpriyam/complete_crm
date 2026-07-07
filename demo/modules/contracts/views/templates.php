<div class="panel panel-custom">
    <div class="panel-heading mb0">
        <h3 class="panel-title"><?= lang('templates') ?>
            <div class="pull-right hidden-print">
                <a href="<?= base_url('admin/' . $module . '/new_template/contracts/' . $module_field_id); ?>"
                   class="text-purple text-sm" data-toggle="modal" data-placement="top" data-target="#myModal_extra_lg">
                    <i class="fa fa-plus "></i> <?= lang('add_template') ?></a>
            </div>
        </h3>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            
            <table class="table table-hover" id="">
                <thead>
                <tr>
                    <th class="col-sm-8">Name</th>
                    <th class="col-sm-2">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($module_templates_info)) : foreach ($module_templates_info as $key => $val) :
                    ?>
                    <tr id="template_table_<?= $val->template_id ?>">
                        
                        <td>
                            <?php echo $val->template_name; ?>
                        </td>
                        <td>
                            <button title="insert into description" class="btn btn-xs btn-warning"
                                    onclick="ins_templ(<?= $val->template_id ?>, '<?= $val->module ?>',  '<?= $module_field_id ?>');return false;">
                                <i class="fa fa-plus-circle"></i>
                            </button>
                            <a href="<?= base_url('admin/' . $module . '/new_template/' . $val->template_id . '/' . $module_field_id); ?>"
                               class="btn btn-primary btn-xs" data-toggle="modal" data-placement="top"
                               data-target="#myModal_extra_lg" title="<?= lang('edit'); ?>"
                               data-original-title="<?= lang('edit'); ?>">
                                <i class="fa fa-pencil-square-o"></i></a>
                            <?php echo ajax_anchor(base_url('admin/' . $module . '/delete_template/' . $val->template_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#template_table_" . $val->template_id)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    function ins_templ(template_id, module, module_field_id) {
        var send_url = "<?= base_url('admin/' . $module . '/ins'); ?>";
        var redirect_url = "<?= base_url('admin/' . $module . '/details/'); ?>" + module_field_id;
        $.ajax({
            type: 'POST',
            url: send_url,
            data: {
                'template_id': template_id,
                'module': module,
                'module_field_id': module_field_id
            }
        }).done(function (res) {
            res = JSON.parse(res);
            toastr[res.status](res.message);
            if (res.status == 'success') {
                if (res.status == 'success') {
                    window.location.href = redirect_url;
                }
            }
        }).fail(function () {
            alert('There was a problem with AJAX');
        });
    }
</script>