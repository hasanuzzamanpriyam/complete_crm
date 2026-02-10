<div class="modal fade" id="RelatedModal" role="dialog">
    
    <div class="modal-dialog">
        
        <div class="modal-content">
            
            <div class="modal-header">
                
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                
                <h4 class="modal-title add-new"><?php echo lang('add_new_related') ?></h4>
            
            </div>
            
            <?php $task = [
                
                ['rel_type' => 'project', 'name' => lang('project')],
                
                ['rel_type' => 'opportunities', 'name' => lang('opportunities')],
                
                ['rel_type' => 'leads', 'name' => lang('leads')],
                
                ['rel_type' => 'bug', 'name' => lang('bugs')],
                
                ['rel_type' => 'goal', 'name' => lang('goal_tracking')],
                
                ['rel_type' => 'sub_task', 'name' => lang('tasks')],
                
                ['rel_type' => 'expenses', 'name' => lang('expenses')],
            
            ];
            
            $rel_id = [
                
                ['id' => '', 'name' => ''],
            
            ];
            
            ?>
            
            <?php echo form_open_multipart(admin_url('spreadsheet/update_related_spreadsheet'), array('id' => 'related-form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="list_information_fields_review_related">
                    <div id="item_information_fields_review_related">
                        
                        <div class="col-md-11">
                            
                            <div class="col-md-6">
                                
                                <?php
                                
                                $selected = '';
                                
                                echo render_select('rel_type[0]', $task, array('rel_type', array('name')), 'related_to', $selected, array());
                                
                                ?>
                            
                            </div>
                            
                            <div class="col-md-6">
                                
                                <?php
                                
                                $selected = '';
                                
                                echo render_select('rel_id[0]', $rel_id, array('id', array('name')), 'value', $selected, array());
                                
                                ?>
                            
                            </div>
                        
                        
                        </div>
                        
                        <div class="col-md-1">

							<span class="pull-bot">

								<button name="add"
                                        class="new-btn-clone1 btn new_box_information_review_related btn-info"
                                        data-ticket="true" type="button">

									<i class="fa fa-plus"></i>

								</button>

							</span>
                        
                        </div>
                    
                    </div>
                
                </div>
            
            </div>
            
            <div class="modal-footer">
                
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('close'); ?></button>
                
                <button type="submit" class="btn btn-info"><?php echo lang('submit'); ?></button>
            
            </div>
            
            <?php echo form_close(); ?>
        
        </div>
    
    </div>

</div>