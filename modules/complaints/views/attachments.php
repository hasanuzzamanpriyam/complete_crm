<style>
    .pointer {
        cursor: pointer !important;
    }

    .wd-100 {
        width: 100px !important;
    }

    .print-btn {
        padding-top: 0px !important;
        padding-bottom: 8px !important;
    }

    .attachments-border {
        border-bottom: 1px solid #dde6e9
    }

    .mediaMargin {
        margin: 8px 0px !important;
    }

    .attachImg {
        width: 50px !important;
        border-radius: 5px !important;
    }
</style>
<div class="panel panel-custom">
    <div class="panel-heading mb0">
        <?php
        $attach_list = $this->session->userdata($module_name . '_media_view');
        if (empty($attach_list)) {
            $attach_list = 'list_view';
        }
        ?>
        <h3 class="panel-title"><?= lang('attach_file_list') ?>
            <a data-toggle="tooltip" data-placement="top"
               href="<?= base_url('admin/common/download_all_attachment/task_id/' . $task_details->task_id) ?>"
               class="btn btn-default" title="<?= lang('download') . ' ' . lang('all') . ' ' . lang('attachment') ?>"><i
                        class="fa fa-cloud-download"></i></a>
            <a data-toggle="tooltip" data-placement="top"
               class="btn btn-default toggle-media-view <?= (!empty($attach_list) && $attach_list == 'list_view' ? 'hidden' : '') ?>"
               data-type="list_view" title="<?= lang('switch_to') . ' ' . lang('media_view') ?>"><i
                        class="fa fa-image"></i></a>
            <a data-toggle="tooltip" data-placement="top"
               class="btn btn-default toggle-media-view <?= (!empty($attach_list) && $attach_list == 'media_view' ? 'hidden' : '') ?>"
               data-type="media_view" title="<?= lang('switch_to') . ' ' . lang('list_view') ?>"><i
                        class="fa fa-list"></i></a>
            
            <div class="pull-right hidden-print print-btn">
                <a href="<?= base_url('admin/' . $module_name . '/new_attachment/' . $id); ?>"
                   class="text-purple text-sm" data-toggle="modal" data-placement="top" data-target="#myModal_extra_lg">
                    <i class="fa fa-plus "></i> <?= lang('new') . ' ' . lang('attachment') ?></a>
            </div>
        </h3>
    </div>
    <script type="text/javascript">
        (function ($) {
            "use strict";
            $(".toggle-media-view").on("click", function () {
                $(".media-view-container").toggleClass('hidden');
                $(".toggle-media-view").toggleClass('hidden');
                $(".media-list-container").toggleClass('hidden');
                var type = $(this).data('type');
                var module = '<?= $module_name ?>';
                $.get('<?= base_url() ?>admin/common/set_media_view/' + type + '/' + module, function (response) {
                });
            });
        })(jQuery);
    </script>
    <?php
    $this->load->helper('file');
    if (empty($project_files_info)) {
        $project_files_info = array();
    } ?>
    <div class="p media-view-container <?= (!empty($attach_list) && $attach_list == 'media_view' ? 'hidden' : '') ?>">
        <div class="row">
            <?php $this->load->view('admin/tasks/attachment_list', array('project_files_info' => $project_files_info)); ?>
        </div>
    </div>
    <div class="media-list-container <?= (!empty($attach_list) && $attach_list == 'list_view' ? 'hidden' : '') ?>">
        <?php
        if (!empty($project_files_info)) {
            foreach ($project_files_info as $key => $v_files_info) {
                ?>
                <div class="panel-group mediaMargin" id="media_list_container-<?= $files_info[$key]->attachments_id ?>"
                     role="tablist" aria-multiselectable="true">
                    <div class="box box-info border-0">
                        <div class="p pb-sm attachments-border" role="tab" id="headingOne">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $key ?>"
                                   aria-expanded="true" aria-controls="collapseOne">
                                    <strong class="text-alpha-inverse"><?php echo $files_info[$key]->title; ?>
                                    </strong>
                                    <small class="pull-right text-white">
                                        <?php if ($files_info[$key]->user_id == $this->session->userdata('user_id')) { ?>
                                            <?php echo ajax_anchor(base_url("admin/tasks/delete_task_files/" . $files_info[$key]->attachments_id), "<i class='text-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#media_list_container-" . $files_info[$key]->attachments_id)); ?>
                                        <?php } ?></small>
                                </a>
                            </h4>
                        </div>
                        <div id="<?php echo $key ?>" class="panel-collapse collapse <?php
                        if (!empty($in) && $files_info[$key]->files_id == $in) {
                            echo 'in';
                        }
                        ?>" role="tabpanel" aria-labelledby="headingOne">
                            <div class="content p">
                                <div class="table-responsive">
                                    <table id="table-files" class="table table-striped ">
                                        <thead>
                                        <tr>
                                            <th><?= lang('files') ?></th>
                                            <th class=""><?= lang('size') ?></th>
                                            <th><?= lang('date') ?></th>
                                            <th><?= lang('total') . ' ' . lang('comments') ?></th>
                                            <th><?= lang('uploaded_by') ?></th>
                                            <th><?= lang('action') ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $this->load->helper('file');
                                        
                                        if (!empty($v_files_info)) {
                                            foreach ($v_files_info as $v_files) {
                                                $user_info = $this->db->where(array('user_id' => $files_info[$key]->user_id))->get('tbl_users')->row();
                                                $total_file_comment = count($this->db->where(array('uploaded_files_id' => $v_files->uploaded_files_id))->order_by('comment_datetime', 'DESC')->get('tbl_task_comment')->result());
                                                ?>
                                                <tr class="file-item">
                                                    <td data-toggle="tooltip" data-placement="top"
                                                        data-original-title="<?= $files_info[$key]->description ?>">
                                                        <?php if ($v_files->is_image == 1) : ?>
                                                            <div class="file-icon"><a data-toggle="modal"
                                                                                      data-target="#myModal_extra_lg"
                                                                                      href="<?= base_url() ?>admin/tasks/attachment_details/r/<?= $files_info[$key]->attachments_id . '/' . $v_files->uploaded_files_id ?>">
                                                                    <img class="attachImg"
                                                                         src="<?= base_url() . $v_files->files ?>"/></a>
                                                            </div>
                                                        <?php else : ?>
                                                            <div class="file-icon"><i class="fa fa-file-o"></i>
                                                                <a data-toggle="modal" data-target="#myModal_extra_lg"
                                                                   href="<?= base_url() ?>admin/tasks/attachment_details/r/<?= $files_info[$key]->attachments_id . '/' . $v_files->uploaded_files_id ?>"><?= $v_files->file_name ?></a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    
                                                    <td class=""><?= $v_files->size ?>Kb</td>
                                                    <td class="col-date">
                                                        <?= date('Y-m-d' . "<br/> h:m A", strtotime($files_info[$key]->upload_time)); ?>
                                                    </td>
                                                    <td class=""><?= $total_file_comment ?></td>
                                                    <td>
                                                        <?= $user_info->username ?>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-xs btn-dark" data-toggle="tooltip"
                                                           data-placement="top" title="Download"
                                                           href="<?= base_url() ?>admin/tasks/download_files/<?= $v_files->uploaded_files_id ?>"><i
                                                                    class="fa fa-download"></i></a>
                                                    </td>
                                                
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="5">
                                                    <?= lang('nothing_to_display') ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>