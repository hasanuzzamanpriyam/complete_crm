<div class="row">
    <div class="col-sm-12">
        <table id="spreadsheet-advanced">
            <caption>
                <a href="#" class="btn btn-info caption-a"
                   onclick="jQuery('#spreadsheet-advanced').treetable('expandAll'); return false;"><span
                            class="expand-all"></span><?php echo lang('expand_all') ?></a>
                <a href="#" class="btn btn-info caption-a"
                   onclick="jQuery('#spreadsheet-advanced').treetable('collapseAll'); return false;"><span
                            class="collapse-all"></span><?php echo lang('collapse_all') ?></a>
            </caption>
            <thead>
            <tr>
                <th><?php echo lang('name') ?></th>
                <th><?php echo lang('kind') ?></th>
                <th><?php echo lang('size') ?></th>
            </tr>
            </thead>
            <?php echo html_entity_decode($folder_my_share_tree); ?>
        </table>
    </div>
</div>

<div class="modal fade" id="AddFolderModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title add-new"><?php echo lang('add_new_folder') ?></h4>
                <h4 class="modal-title update-new hide"><?php echo lang('update_folder') ?></h4>
            </div>
            <?php echo form_open_multipart(admin_url('spreadsheet/add_edit_folder'), array('id' => 'add-edit-folder-form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <?php echo render_input('name', 'name_folder'); ?>
                    </div>
                </div>
                <?php echo form_hidden('parent_id'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo lang('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<div class="hide">
    <ul class="button-group__mono-colors" data-share="true">
</div>
<!-- / The Context Menu -->
<nav id="context-menu" class="context-menu" data-share="true">
    <ul class="context-menu__items">
        <li class="context-menu__item">
            <a href="#" class="context-menu__link" data-action="edit"><i
                        class="fa fa-empire"></i> <?php echo lang('edit') ?></a>
        </li>
        <li class="context-menu__item">
            <a href="#" class="context-menu__link" data-action="view"><i
                        class="fa fa-envira"></i> <?php echo lang('view') ?></a>
        </li>
    </ul>
</nav>