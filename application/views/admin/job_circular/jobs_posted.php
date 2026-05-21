<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<?php
$created = can_action('103', 'created');
$edited = can_action('103', 'edited');
$deleted = can_action('103', 'deleted');
?>
<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('job_posted_list') ?></strong>
            <?php if (!empty($created)) { ?>
                <div class="pull-right hidden-print" style="padding-top: 0px;padding-bottom: 8px">
                    <a href="<?= base_url() ?>admin/job_circular/new_jobs_posted" class="btn btn-xs btn-info"
                       data-toggle="modal"
                       data-placement="top" data-target="#myModal_lg">
                        <i class="fa fa-plus "></i> <?= ' ' . lang('new') . ' ' . lang('jobs_posted') ?></a>
                </div>
            <?php } ?>
        </div>
    </div>
    <!-- Table -->
    <div class="panel-body">
        <table class="table table-striped DataTables " id="DataTables" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('job_title') ?></th>
                <th><?= lang('designation') ?></th>
                <th><?= lang('vacancy_no') ?></th>
                <th><?= lang('last_date') ?></th>
                <?php $show_custom_fields = custom_form_table(14, null);
                if (!empty($show_custom_fields)) {
                    foreach ($show_custom_fields as $c_label => $v_fields) {
                        if (!empty($c_label)) {
                            ?>
                            <th><?= $c_label ?> </th>
                        <?php }
                    }
                }
                ?>
                <th><?= lang('status') ?></th>
                <th><?= lang('action') ?></th>
            </tr>
            </thead>
            <tbody>
            <script type="text/javascript">
                $(document).ready(function () {
                    list = base_url + "admin/job_circular/jobs_postedList";
                });
            </script>

            </tbody>
        </table>
    </div>
</div>

<!-- Job Skills Modal -->
<div class="modal fade" id="jobSkillsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?= lang('manage_job_skills') ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="job_skills_circular_id" value="">
                <div id="job_skills_list"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
                <button type="button" class="btn btn-primary" id="saveJobSkillsBtn" onclick="saveJobSkills()"><?= lang('save') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
function openJobSkillsModal(jobCircularId) {
    $('#job_skills_circular_id').val(jobCircularId);
    $('#job_skills_list').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</p>');
    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/get_job_skills_ajax/' + jobCircularId,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                var html = '<div class="row">';
                var categories = {};
                res.all_skills.forEach(function(skill) {
                    var cat = skill.skill_category || 'Uncategorized';
                    if (!categories[cat]) categories[cat] = [];
                    categories[cat].push(skill);
                });
                for (var cat in categories) {
                    html += '<div class="col-md-6"><div class="panel panel-default"><div class="panel-heading"><strong>' + cat + '</strong></div><div class="panel-body">';
                    categories[cat].forEach(function(skill) {
                        var checked = res.assigned_ids.indexOf(skill.skill_id) !== -1 ? 'checked' : '';
                        var mandatory = '';
                        if (checked) {
                            res.assigned.forEach(function(a) {
                                if (a.skill_id == skill.skill_id && a.is_mandatory == 1) mandatory = 'checked';
                            });
                        }
                        html += '<label style="display:block;margin:5px 0;">';
                        html += '<input type="checkbox" name="skills[]" value="' + skill.skill_id + '" ' + checked + ' onchange="toggleMandatory(this, ' + skill.skill_id + ')"> ';
                        html += skill.skill_name;
                        html += ' <label style="margin-left:10px;font-size:11px;"><input type="checkbox" name="mandatory[' + skill.skill_id + ']" id="mandatory_' + skill.skill_id + '" ' + mandatory + ' ' + (!checked ? 'disabled' : '') + '> Mandatory</label>';
                        html += '</label>';
                    });
                    html += '</div></div></div>';
                }
                html += '</div>';
                $('#job_skills_list').html(html);
                $('#jobSkillsModal').modal('show');
            } else {
                alert('Failed to load skills');
            }
        },
        error: function(xhr, status, error) {
            alert('Error loading skills: ' + error);
        }
    });
}

function toggleMandatory(checkbox, skillId) {
    $('#mandatory_' + skillId).prop('disabled', !checkbox.checked);
    if (!checkbox.checked) $('#mandatory_' + skillId).prop('checked', false);
}

function saveJobSkills() {
    var circularId = $('#job_skills_circular_id').val();
    var $btn = $('#saveJobSkillsBtn');
    var originalText = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

    var postData = { job_circular_id: circularId };

    // Collect all selected skills and their mandatory status
    $('#job_skills_list').find('input[name="skills[]"]').each(function() {
        var skillId = $(this).val();
        var isChecked = $(this).is(':checked');
        var isMandatory = $('#mandatory_' + skillId).is(':checked');

        if (isChecked) {
            if (!postData.skills) postData.skills = [];
            postData.skills.push(skillId);
            if (isMandatory) {
                postData['mandatory_' + skillId] = 1;
            }
        }
    });

    $.ajax({
        url: '<?= base_url() ?>admin/job_circular/save_job_skills/' + circularId,
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#jobSkillsModal').modal('hide');
                location.reload();
            } else {
                alert('Failed to save skills');
                $btn.html(originalText).prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            alert('Error saving skills: ' + error);
            $btn.html(originalText).prop('disabled', false);
        }
    });
}
</script>