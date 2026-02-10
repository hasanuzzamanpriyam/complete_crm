<nav id="context-menu" class="context-menu" data-share="false">
    <ul class="context-menu__items">
        <li class="context-menu__item">
            <a href="#" class="context-menu__link" data-action="edit"><i
                        class="fa fa-cog"></i> <?php echo lang('edit') ?></a>
        </li>
        <li class="context-menu__item">
            <a href="#" class="context-menu__link" data-action="delete"><i
                        class="fa fa-trash"></i> <?php echo lang('delete') ?></a>
        </li>
        <li class="context-menu__item">
            <a href="#" class="context-menu__link" data-action="share"><i
                        class="fa fa-share"></i> <?php echo lang('share') ?></a>
        </li>
        <li class="context-menu__item">
            <a href="#" class="context-menu__link" data-action="related"><i
                        class="fa fa-user"></i> <?php echo lang('related') ?></a>
        </li>
        <li class="context-menu__item">
            <a href="#" class="context-menu__link" data-action="d_file"><i
                        class="fa fa-download"></i> <?php echo lang('download') ?></a>
        </li>
        <li class="context-menu__item">
            <a href="#" class="context-menu__link" data-action="create_file"><i
                        class="fa fa-plus"></i> <?php echo lang('create_file') ?></a>
        </li>
        
        <li class="context-menu__item">
            <a href="#" class="context-menu__link" data-action="create_folder"><i
                        class="fa fa-plus"></i> <?php echo lang('create_folder') ?></a>
        </li>
    </ul>
</nav>


<div class="popup-overlay">
    <div class="popup-content">
        <header role="banner">
            <nav class="nav-class" role="navigation">
                <ul class="nav__list button-group__mono-colors" data-share="false">
                    <li class="select-option-choose" data-option="edit">
                        <input id="group-1" type="checkbox" hidden/>
                        <label for="group-1"><span class="fa fa-angle-right"></span><i
                                    class="fa fa-crosshairs"></i> <?php echo lang('edit') ?></label>
                    </li>
                    <li class="select-option-choose" data-option="delete">
                        <input id="group-2" type="checkbox" hidden/>
                        <label for="group-2"><span class="fa fa-angle-right"></span><i
                                    class="fa fa-trash-o"></i> <?php echo lang('delete') ?></label>
                    </li>
                    
                    <li class="select-option-choose" data-option="share">
                        <input id="group-3" type="checkbox" hidden/>
                        <label for="group-3"><span class="fa fa-angle-right"></span><i class="fa fa-user-plus"
                                                                                       aria-hidden="true"></i>
                            <?php echo lang('share') ?></label>
                    </li>
                    <li class="select-option-choose" data-option="related">
                        <input id="group-4" type="checkbox" hidden/>
                        <label for="group-4"><span class="fa fa-angle-right"></span> <i class="fa fa-user"
                                                                                        aria-hidden="true"></i>
                            <?php echo lang('related') ?></label>
                    </li>
                    <li class="select-option-choose" data-option="d_file">
                        <input id="group-5" type="checkbox" hidden/>
                        <label for="group-5"><span class="fa fa-angle-right"></span><i class="fa fa-download"
                                                                                       aria-hidden="true"></i> <?php echo lang('download') ?>
                        </label>
                    </li>
                    <li class="select-option-choose" data-option="create_file">
                        <input id="group-6" type="checkbox" hidden/>
                        <label for="group-6"><span class="fa fa-angle-right"></span><i class="fa fa-plus"
                                                                                       aria-hidden="true"></i> <?php echo lang('create_file') ?>
                        </label>
                    </li>
                    
                    <li class="select-option-choose" data-option="create_folder">
                        <input id="group-7" type="checkbox" hidden/>
                        <label for="group-7"><span class="fa fa-angle-right"></span><i class="fa fa-plus"
                                                                                       aria-hidden="true"></i> <?php echo lang('create_folder') ?>
                        </label>
                    </li>
                </ul>
            </nav>
        </header>
    </div>
</div>

<?php
$this->load->view('RelatedModal');
?>

<div class="modal fade" id="relateDetailModal" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title add-new"><?php echo lang('related_detail') ?></h4>
            </div>
            <div class="modal-body">
                <h4>List name: </h4>
                <ul class="content-related"></ul>
            
            </div>
        </div>
    </div>
</div>


<a href="#" data-toggle="modal" class="btn add_file_button btn-info">
    <i class="fa fa-plus-circle"></i> <?php echo lang('add_file'); ?>
</a>
<a href="#AddFolderModal" data-toggle="modal" class="btn add_folder_button btn-info">
    <i class="fa fa-plus-square-o"></i> <?php echo lang('add_folder'); ?>
</a>
<a href="#ShareModal" data-toggle="modal" class="btn add_share_button btn-info">
    <i class="fa fa-share-square-o"></i> <?php echo lang('share'); ?>
</a>
<a href="#RelatedModal" data-toggle="modal" class="btn add_related_button btn-info">
    <i class="fa fa-paw"></i> <?php echo lang('related'); ?>
</a>


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
                
                <th><?php echo lang('related_to') ?></th>
            
            </tr>
            
            </thead>
            
            <?php echo html_entity_decode($folder_my_tree); ?>
        
        </table>
    
    </div>

</div>


<div class="modal fade" id="AddFileModal" role="dialog">
    
    <div class="modal-dialog">
        
        <div class="modal-content">
            
            <div class="modal-header">
                
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                
                <h4 class="modal-title add-new"><?php echo lang('add_new_action') ?></h4>
                
                <h4 class="modal-title update-new hide"><?php echo lang('update_action') ?></h4>
            
            </div>
            
            <?php echo form_open_multipart(admin_url('hira/update_observations_action'), array('id' => 'observations-action-form')); ?>
            
            <?php echo form_hidden('id'); ?>
            
            <?php echo form_hidden('type', 'observations'); ?>
            
            <div class="modal-body">
            
            </div>
            
            <div class="modal-footer">
                
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('close'); ?></button>
                
                <button type="submit" class="btn btn-info"><?php echo lang('submit'); ?></button>
            
            </div>
            
            <?php echo form_close(); ?>
        
        </div>
    
    </div>

</div>


<div id="fsModal" class="modal animated bounceIn" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    
    <div class="modal-dialog">
        
        <div class="modal-content">
            
            <div class="modal-body">
            
            
            </div>
            
            <div class="modal-footer">
                
                <button class="btn btn-secondary" data-dismiss="modal">
                    
                    close
                
                </button>
                
                <button class="btn btn-default">
                    
                    Default
                
                </button>
                
                <button class="btn btn-primary">
                    
                    Primary
                
                </button>
            
            </div>
        
        </div>
    
    </div>

</div>


<div class="modal fade" id="sharedetailModal" role="dialog">
    
    <div class="modal-dialog">
        
        <div class="modal-content">
            
            <div class="modal-header">
                
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                
                <h4 class="modal-title add-new"><?php echo lang('share_detail') ?></h4>
            
            </div>
            
            <div class="modal-body">
                
                <?php echo form_hidden('data_share'); ?>
                
                <div class="row">
                    
                    <ul class="tabs">
                        
                        <li class="tab-link current" data-tab="tab-1"><?php echo lang('staff') ?></li>
                        
                        <li class="tab-link" data-tab="tab-2"><?php echo lang('clients') ?></li>
                    
                    </ul>
                    
                    
                    <div id="tab-1" class="tab-content current">
                        
                        <table class="content-table">
                            
                            <thead>
                            
                            <tr>
                                
                                <th>NAME</th>
                                
                                <th>PERMISSION</th>
                            
                            </tr>
                            
                            </thead>
                            
                            <tbody>
                            
                            </tbody>
                        
                        </table>
                    
                    </div>
                    
                    <div id="tab-2" class="tab-content">
                        
                        
                        <table class="content-table">
                            
                            <thead>
                            
                            <tr>
                                
                                <th>NAME</th>
                                
                                <th>PERMISSION</th>
                            
                            </tr>
                            
                            </thead>
                            
                            <tbody>
                            
                            </tbody>
                        
                        </table>
                    
                    </div>
                
                </div>
            
            </div>
        
        </div>
    
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


<?php
$rata['staffs'] = $staffs;
$rata['clients'] = $clients;
$this->load->view('shareModal', $rata);
?>