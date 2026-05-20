<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('skills_management') ?></strong>
            <div class="pull-right hidden-print">
                <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#skillModal" onclick="resetSkillForm()">
                    <i class="fa fa-plus"></i> <?= lang('add_skill') ?>
                </button>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <strong><?= $cat['skill_category'] ?></strong>
                            </div>
                            <div class="panel-body" style="padding:5px;">
                                <?php foreach ($all_skills as $skill): ?>
                                    <?php if ($skill->skill_category == $cat['skill_category']): ?>
                                        <span class="label label-<?= $skill->status == 'active' ? 'success' : 'default' ?>" style="margin:3px;padding:6px 10px;font-size:13px;">
                                            <?= $skill->skill_name ?>
                                            <a href="#" onclick="editSkill(<?= $skill->skill_id ?>, '<?= addslashes($skill->skill_name) ?>', '<?= addslashes($skill->skill_category) ?>', <?= $skill->status == 'active' ? 'true' : 'false' ?>)" style="color:#fff;margin-left:5px;"><i class="fa fa-pencil"></i></a>
                                            <a href="#" onclick="deleteSkill(<?= $skill->skill_id ?>)" style="color:#fff;margin-left:3px;"><i class="fa fa-times"></i></a>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php
            $uncategorized = array_filter($all_skills, function($s) { return empty($s->skill_category); });
            if (!empty($uncategorized)): ?>
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong><?= lang('uncategorized') ?></strong>
                        </div>
                        <div class="panel-body" style="padding:5px;">
                            <?php foreach ($uncategorized as $skill): ?>
                                <span class="label label-<?= $skill->status == 'active' ? 'success' : 'default' ?>" style="margin:3px;padding:6px 10px;font-size:13px;">
                                    <?= $skill->skill_name ?>
                                    <a href="#" onclick="editSkill(<?= $skill->skill_id ?>, '<?= addslashes($skill->skill_name) ?>', '', <?= $skill->status == 'active' ? 'true' : 'false' ?>)" style="color:#fff;margin-left:5px;"><i class="fa fa-pencil"></i></a>
                                    <a href="#" onclick="deleteSkill(<?= $skill->skill_id ?>)" style="color:#fff;margin-left:3px;"><i class="fa fa-times"></i></a>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Skill Modal -->
<div class="modal fade" id="skillModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="skillForm" onsubmit="saveSkillForm(event)">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="skillModalTitle"><?= lang('add_skill') ?></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="skill_id" name="skill_id" value="">
                    <div class="form-group">
                        <label><?= lang('skill_name') ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="skill_name" name="skill_name" required>
                    </div>
                    <div class="form-group">
                        <label><?= lang('skill_category') ?></label>
                        <input type="text" class="form-control" id="skill_category" name="skill_category" list="categoryList" placeholder="e.g., Technical, Soft, Language, Tool">
                        <datalist id="categoryList">
                            <option value="Technical">
                            <option value="Soft">
                            <option value="Language">
                            <option value="Tool">
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="skill_status" name="status" checked> <?= lang('active') ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
                    <button type="submit" class="btn btn-primary"><?= lang('save') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetSkillForm() {
    $('#skill_id').val('');
    $('#skill_name').val('');
    $('#skill_category').val('');
    $('#skill_status').prop('checked', true);
    $('#skillModalTitle').text('<?= lang('add_skill') ?>');
}

function editSkill(id, name, category, active) {
    $('#skill_id').val(id);
    $('#skill_name').val(name);
    $('#skill_category').val(category);
    $('#skill_status').prop('checked', active);
    $('#skillModalTitle').text('<?= lang('edit_skill') ?>');
    $('#skillModal').modal('show');
}

function saveSkillForm(e) {
    e.preventDefault();
    var formData = $('#skillForm').serialize();
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/save_skill/' + ($('#skill_id').val() || ''),
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                location.reload();
            }
        }
    });
}

function deleteSkill(id) {
    if (confirm('<?= lang('confirm_delete') ?>')) {
        $.ajax({
            url: '<?= base_url() ?>admin/job_circular/delete_skill/' + id,
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.success) location.reload();
            }
        });
    }
}
</script>
