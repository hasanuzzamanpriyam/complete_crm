<style>
    .team-card { margin-bottom: 20px; }
    .team-card .panel-heading { cursor: pointer; }
    .member-badge-manager { background-color: #5bc0de; }
    .member-badge-member { background-color: #777; }
    .status-badge-approved { background-color: #5cb85c; }
    .status-badge-pending { background-color: #f0ad4e; }
    .member-row td { vertical-align: middle; }
</style>

<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'Team Management' ?></h3>
            </header>
            <div class="panel-body">
                <?php echo message_box('success'); ?>

                <div class="mb-sm">
                    <button class="btn btn-primary" onclick="openAssignModal()">
                        <i class="fa fa-users"></i> Assign Member to Teams
                    </button>
                </div>

                <?php if (empty($teams)): ?>
                    <div class="alert alert-info">No teams have been created yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Team Name</th>
                                    <th>Description</th>
                                    <th>Members</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teams as $team): ?>
                                    <?php $members = $team_members[$team->id] ?? []; ?>
                                    <tr class="team-card">
                                        <td colspan="3" style="padding:0;border:none;">
                                            <div class="panel panel-default" style="margin:5px 0;">
                                                <div class="panel-heading" onclick="$(this).next().toggle()">
                                                    <strong>
                                                        <i class="fa fa-users"></i> <?= htmlspecialchars($team->name) ?>
                                                    </strong>
                                                    <span class="badge pull-right"><?= count($members) ?> members</span>
                                                </div>
                                                <div class="panel-body" style="display:<?= $team === reset($teams) ? 'block' : 'none' ?>">
                                                    <p><em><?= htmlspecialchars($team->description ?: 'No description') ?></em></p>
                                                    <p class="text-muted small">Created by user #<?= (int)$team->created_by ?></p>

                                                    <?php if (empty($members)): ?>
                                                        <p class="text-muted">No members in this team.</p>
                                                    <?php else: ?>
                                                        <table class="table table-condensed table-hover" style="margin-bottom:0;">
                                                            <thead>
                                                                <tr>
                                                                    <th>Member</th>
                                                                    <th>Role</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($members as $m): ?>
                                                                    <tr class="member-row">
                                                                        <td><?= htmlspecialchars($m->fullname ?? 'User #' . $m->user_id) ?></td>
                                                                        <td>
                                                                            <?php if ($m->is_manager): ?>
                                                                                <span class="label member-badge-manager">Manager</span>
                                                                            <?php else: ?>
                                                                                <span class="label member-badge-member">Member</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php if ($m->status === 'approved'): ?>
                                                                                <span class="label status-badge-approved">Approved</span>
                                                                            <?php elseif ($m->status === 'pending'): ?>
                                                                                <span class="label status-badge-pending">Pending</span>
                                                                            <?php else: ?>
                                                                                <span class="label label-default"><?= htmlspecialchars($m->status) ?></span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open('admin/timesync_teams/save_member', ['class' => 'form-horizontal']); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Assign Teams to User</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-lg-3 control-label">User</label>
                        <div class="col-lg-8">
                            <select name="user_id" id="assign-user" class="form-control" required>
                                <option value="">-- Select User --</option>
                                <?php foreach ($all_users as $u): ?>
                                    <option value="<?= (int)$u->user_id ?>">
                                        <?= htmlspecialchars($u->fullname ?? 'User #' . $u->user_id) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label">Teams</label>
                        <div class="col-lg-8">
                            <div id="teams-checkbox-list">
                                <p class="text-muted">Select a user first to load available teams.</p>
                            </div>
                        </div>
                    </div>
                    <div id="manager-warning" class="alert alert-warning hide">
                        <i class="fa fa-exclamation-triangle"></i>
                        Members marked as <strong>Manager</strong> cannot be removed here. Demote them first via the desktop app or API.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
    function openAssignModal() {
        $('#assignModal').modal('show');
    }

    $(document).ready(function () {
        $('#assign-user').on('change', function () {
            var userId = $(this).val();
            var list = $('#teams-checkbox-list');

            if (!userId) {
                list.html('<p class="text-muted">Select a user first to load available teams.</p>');
                return;
            }

            $.get('<?= base_url('admin/timesync_teams/edit_member') ?>/' + userId, function (data) {
                var html = '<div class="checkbox"><label><input type="checkbox" name="team_ids[]" value="0" disabled> Loading...</label></div>';

                if (data.teams && data.teams.length > 0) {
                    html = '';
                    $.each(data.teams, function (i, team) {
                        var checked = data.user_team_ids.indexOf(team.id) !== -1;
                        html += '<div class="checkbox">';
                        html += '<label>';
                        html += '<input type="checkbox" name="team_ids[]" value="' + team.id + '"' + (checked ? ' checked' : '') + '> ';
                        html += htmlspecialchars(team.name);
                        html += '</label>';
                        html += '</div>';
                    });
                } else {
                    html = '<p class="text-muted">No teams available.</p>';
                }

                list.html(html);
            });
        });
    });

    function htmlspecialchars(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
</script>
