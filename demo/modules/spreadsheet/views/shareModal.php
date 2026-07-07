<div class="modal fade" id="ShareModal" role="dialog">
    <?php echo form_hidden('value-hidden'); ?>
    <?php echo form_open_multipart(admin_url('spreadsheet/update_share_spreadsheet'), array('id' => 'share-form')) ?>
    <?php echo form_hidden('id'); ?>
    <?php echo form_hidden('update', "false"); ?>
    <?php echo form_hidden('parent_id'); ?>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close test_class" data-dismiss="modal">&times;</button>
                <h4 class="modal-title add-new"><?php echo lang('add_new_share') ?></h4>
            </div>
            <div class="modal-body">
                
                <div class="row share-row">
                    
                    <div class="col-md-12 choosee-staff">
                        
                        <div class="col-md-12">
                            
                            <strong><?php echo lang('staff') ?> </strong>
                        
                        </div>
                        
                        <div class="list_information_fields_review">
                            
                            <?php if (!isset($medical_visit->review_result)) { ?>
                                
                                <div id="item_information_fields_review">
                                    
                                    <div class="col-md-11 content-share">
                                        
                                        <div class="col-md-6">
                                            
                                            <?php
                                            
                                            $selected = '';
                                            echo render_select('staffs_share[0]', $staffs, array('user_id', array('fullname', 'username')), 'staff_share', $selected, array());
                                            ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php $permission = [['id' => 1, 'name' => lang('view')], ['id' => 2, 'name' => lang('edit')]] ?>
                                            <?php echo render_select('role_staff[0]', $permission, array('id', 'name'), 'permission', 1); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-1">

                                        <span class="pull-bot">

                                            <button name="add"
                                                    class="new-btn-clone btn new_box_information_review btn-info"
                                                    data-ticket="true" type="button">

                                                <i class="fa fa-plus"></i>

                                            </button>

                                        </span>
                                    
                                    </div>
                                
                                </div>
                            
                            <?php } else { ?>
                                
                                <?php foreach ($medical_visit->review_result as $key => $review_result) { ?>
                                    
                                    <div id="item_information_fields_review">
                                        
                                        
                                        <div class="col-md-11 content-share">
                                            
                                            <div class="col-md-4">
                                                
                                                <?php $selected = ''; ?>
                                                
                                                <?php echo render_select('departments_share[$key]', $departments, array('departments_id', 'deptname'), 'department_share', $selected, array()); ?>
                                            
                                            </div>
                                            
                                            <div class="col-md-4">
                                                
                                                <?php
                                                
                                                $selected = $review_result['exam_result'];
                                                
                                                echo render_select('staffs_share[$key]', $staffs, array('staffid', array('fullname', 'username')), 'staff_share', $selected, array());
                                                
                                                ?>
                                            
                                            </div>
                                            
                                            <div class="col-md-4">
                                                
                                                <?php $permission = [['id' => 1, 'name' => lang('view')], ['id' => 2, 'name' => lang('edit')]] ?>
                                                
                                                <?php echo render_select('role_staff[$key]', $permission, array('id', 'name'), 'permission', $review_result['exam_result'] != '' ? $review_result['exam_result'] : ''); ?>
                                            
                                            </div>
                                        
                                        </div>
                                        
                                        
                                        <div class="col-md-1">

                                            <span class="pull-bot">

                                                <button name="add" class="new-btn-clone btn <?php if ($key == 0) {
    
                                                    echo 'new_box_information_review btn-info';
                                                } else {
    
                                                    echo 'remove_box_information_review btn-danger';
                                                } ?>" data-ticket="true" type="button"><i
                                                            class="fa <?php if ($key == 0) {
                
                                                                echo 'fa-plus';
                                                            } else {
                
                                                                echo 'fa-minus';
                                                            } ?>"></i>

                                                </button>

                                            </span>
                                        
                                        </div>
                                    
                                    </div>
                                
                                <?php }
                            } ?>
                        
                        </div>
                    
                    </div>
                    
                    
                    <div class="col-md-12 choosee-customer">
                        
                        <div class="col-md-12">
                            
                            <strong><?php echo lang('client') ?> </strong>
                            
                            
                            <div class="list_information_fields_review_client">
                                
                                <?php if (!isset($medical_visit->review_result)) { ?>
                                    
                                    <div id="item_information_fields_review_client">
                                        
                                        <div class="col-md-11 content-share">
                                            
                                            <div class="col-md-6">
                                                
                                                <?php
                                                
                                                $selected = '';
                                                
                                                echo render_select('clients_share[0]', $clients, array('client_id', array('name')), 'client_share', $selected, array());
                                                
                                                ?>
                                            
                                            </div>
                                            
                                            
                                            <div class="col-md-6">
                                                
                                                <?php echo render_select('role_client[0]', $permission, array('id', 'name'), 'permission', 1); ?>
                                            
                                            </div>
                                        
                                        
                                        </div>
                                        
                                        
                                        <div class="col-md-1">

                                            <span class="pull-bot">

                                                <button name="add"
                                                        class="new-btn-clone btn new_box_information_review_client btn-info"
                                                        data-ticket="true" type="button">

                                                    <i class="fa fa-plus"></i>

                                                </button>

                                            </span>
                                        
                                        </div>
                                    
                                    </div>
                                
                                <?php } else { ?>
                                    
                                    <?php foreach ($medical_visit->review_result as $key => $review_result) { ?>
                                        
                                        <div id="item_information_fields_review">
                                            
                                            
                                            <div class="col-md-11 content-share">
                                                
                                                
                                                <div class="col-md-4">
                                                    
                                                    <?php echo render_select('client_groups_share[$key]', $client_groups, array('customer_group_id', 'customer_group'), 'client_groups_share', '', array()); ?>
                                                
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    
                                                    <?php
                                                    
                                                    $selected = $review_result['exam_result'];
                                                    
                                                    echo render_select('clients_share[$key]', $clients, array('client_id', array('name')), 'client_share', $selected, array());
                                                    
                                                    ?>
                                                
                                                </div>
                                                
                                                
                                                <div class="col-md-4">
                                                    
                                                    <?php echo render_select('role_client[$key]', $permission, array('id', 'name'), 'permission', $review_result['exam_result'] != '' ? $review_result['exam_result'] : ''); ?>
                                                
                                                </div>
                                            
                                            </div>
                                            
                                            
                                            <div class="col-md-1">

                                                <span class="pull-bot">

                                                    <button name="add" class="new-btn-clone btn <?php if ($key == 0) {
    
                                                        echo 'new_box_information_review_client btn-info';
                                                    } else {
    
                                                        echo 'remove_box_information_review_client btn-danger';
                                                    } ?>" data-ticket="true" type="button"><i
                                                                class="fa <?php if ($key == 0) {
                                                                    echo 'fa-plus';
                                                                } else {
                
                                                                    echo 'fa-minus';
                                                                } ?>"></i>

                                                    </button>

                                                </span>
                                            
                                            </div>
                                        
                                        </div>
                                    
                                    <?php }
                                } ?>
                            
                            </div>
                        
                        </div>
                    
                    </div>
                
                </div>
                
                <div class="modal-footer">
                    
                    <button type="button" class="btn btn-default test_class"
                            data-dismiss="modal"><?php echo lang('close'); ?></button>
                    
                    <button type="submit" class="btn btn-info"><?php echo lang('submit'); ?></button>
                
                </div>
            
            </div>
        
        </div>
        
        <?php echo form_close(); ?>
    
    </div>