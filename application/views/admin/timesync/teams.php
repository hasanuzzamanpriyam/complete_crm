<style>
  .teams-stat-card { border-radius: 8px; transition: transform .2s; }
  .teams-stat-card:hover { transform: translateY(-2px); }
  .teams-stat-card .stat-value { font-size: 28px; font-weight: 700; line-height: 1; }
  .teams-stat-card .stat-label { font-size: 12px; opacity: .8; margin-top: 4px; }
  .chart-panel .panel-body { padding: 12px; }
  .chart-panel canvas { max-height: 200px; }
  .team-card { border-radius: 8px; margin-bottom: 16px; overflow: hidden; }
  .team-card .card-header {
    padding: 16px 20px; cursor: pointer; display: flex; align-items: center;
    justify-content: space-between; border-bottom: 1px solid #eee; transition: background .15s;
  }
  .team-card .card-header:hover { background: #f8f9fa; }
  .team-card .card-header .team-name { font-size: 15px; font-weight: 600; color: #001737; }
  .team-card .card-header .team-name i { color: #23b7e5; width: 22px; }
  .team-card .card-header .badge-count {
    background: #edf2f9; color: #001737; font-weight: 600; padding: 4px 12px; border-radius: 20px; font-size: 12px;
  }
  .team-card .card-header .toggle-arrow { color: #bcc4d0; font-size: 12px; transition: transform .2s; }
  .team-card .card-header .toggle-arrow.open { transform: rotate(90deg); }
  .team-card .card-body { padding: 0; }
  .member-avatar {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 12px; font-weight: 600;
    margin-right: 10px; flex-shrink: 0;
  }
  .member-row {
    display: flex; align-items: center; padding: 10px 20px; border-bottom: 1px solid #f0f2f5;
    transition: background .15s;
  }
  .member-row:last-child { border-bottom: none; }
  .member-row:hover { background: #f8f9fa; }
  .member-row .member-info { flex: 1; }
  .member-row .member-name { font-size: 14px; font-weight: 500; color: #001737; }
  .member-row .member-badges { display: flex; gap: 6px; }
  .role-badge {
    display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;
  }
  .role-manager { background: #e1f0fa; color: #23b7e5; }
  .role-member { background: #e9ecef; color: #6c757d; }
  .status-badge {
    display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;
  }
  .status-approved { background: #d4edda; color: #28a745; }
  .status-pending { background: #fff3cd; color: #ffc107; }
  .status-left { background: #f8d7da; color: #dc3545; }
  .teams-search {
    border-radius: 20px; border: 1px solid #dde1e7; padding: 8px 16px 8px 36px;
    font-size: 13px; width: 260px; transition: border-color .2s;
  }
  .teams-search:focus { border-color: #23b7e5; outline: none; }
  .search-wrap { position: relative; display: inline-block; }
  .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #bcc4d0; font-size: 14px; }
  .empty-teams { text-align: center; padding: 60px 20px; color: #6c757d; }
  .empty-teams i { font-size: 48px; color: #dde1e7; margin-bottom: 16px; display: block; }
  .assign-user-select { border-radius: 6px; }
  .team-checkbox-item {
    padding: 6px 0; border-bottom: 1px solid #f0f2f5; display: flex; align-items: center;
  }
  .team-checkbox-item:last-child { border-bottom: none; }
  .team-checkbox-item label { margin: 0 0 0 8px; font-weight: 400; cursor: pointer; }
  .pagination-wrap { display: flex; justify-content: center; margin-top: 20px; }
</style>

<div class="row">
  <div class="col-lg-12">
    <section class="panel panel-custom">
      <header class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-users"></i> <?= $title ?? 'Team Management' ?></h3>
        <span class="pull-right">
          <div class="search-wrap">
            <i class="fa fa-search"></i>
            <input type="text" id="teamSearch" class="teams-search" placeholder="Search teams...">
          </div>
          <button class="btn btn-primary btn-sm ml-sm" onclick="openAssignModal()" style="margin-left:10px;">
            <i class="fa fa-user-plus"></i> Assign Member
          </button>
        </span>
      </header>
      <div class="panel-body">

        <?php echo message_box('success'); ?>

        <!-- Stats Row -->
        <div class="row mb-lg" style="margin-bottom:24px;">
          <div class="col-sm-4">
            <div class="panel panel-info teams-stat-card">
              <div class="panel-body text-center">
                <div class="stat-value"><?= $total_teams_all ?></div>
                <div class="stat-label">Total Teams</div>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="panel panel-success teams-stat-card">
              <div class="panel-body text-center">
                <div class="stat-value"><?= $total_members_all ?></div>
                <div class="stat-label">Total Members</div>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="panel panel-warning teams-stat-card">
              <div class="panel-body text-center">
                <div class="stat-value"><?= $pending_count_all ?></div>
                <div class="stat-label">Pending Approvals</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart Row -->
        <div class="row mb-lg" style="margin-bottom:24px;">
          <div class="col-md-6">
            <div class="panel panel-custom chart-panel">
              <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-bar-chart"></i> Top 10 Teams by Size</h4></div>
              <div class="panel-body">
                <canvas id="teamSizeChart"></canvas>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="panel panel-custom chart-panel">
              <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-pie-chart"></i> Member Status</h4></div>
              <div class="panel-body">
                <canvas id="statusChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <?php if (empty($teams)): ?>
          <div class="empty-teams">
            <i class="fa fa-users"></i>
            <h4>No Teams Yet</h4>
            <p class="text-muted">Teams are created from the TimeSync desktop app.</p>
          </div>
        <?php else: ?>
          <div id="teamsList">
            <?php foreach ($teams as $team): ?>
              <?php $members = $team_members[$team->id] ?? []; ?>
              <div class="team-card panel panel-default" data-name="<?= strtolower(htmlspecialchars($team->name)) ?>">
                <div class="card-header" onclick="toggleTeam(this)">
                  <div>
                    <span class="team-name"><i class="fa fa-users"></i> <?= htmlspecialchars($team->name) ?></span>
                    <?php if ($team->description): ?>
                      <br><small class="text-muted"><?= htmlspecialchars($team->description) ?></small>
                    <?php endif; ?>
                  </div>
                  <div style="display:flex;align-items:center;gap:12px;">
                    <span class="badge-count"><?= count($members) ?> member<?= count($members) !== 1 ? 's' : '' ?></span>
                    <i class="fa fa-chevron-right toggle-arrow"></i>
                  </div>
                </div>
                <div class="card-body" style="display:none;">
                  <?php if (empty($members)): ?>
                    <div style="padding:20px;text-align:center;color:#6c757d;">
                      <i class="fa fa-user-slash" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                      No members in this team
                    </div>
                  <?php else: ?>
                    <?php foreach ($members as $m): ?>
                      <?php $avatar_colors = ['#23b7e5','#27ae60','#e74c3c','#f39c12','#9b59b6','#1abc9c','#e67e22','#3498db']; ?>
                      <?php $acolor = $avatar_colors[abs(crc32($m->user_id)) % count($avatar_colors)]; ?>
                      <div class="member-row">
                        <div class="member-avatar" style="background:<?= $acolor ?>">
                          <?= strtoupper(substr($m->fullname ?? 'U' . $m->user_id, 0, 2)) ?>
                        </div>
                        <div class="member-info">
                          <div class="member-name"><?= htmlspecialchars($m->fullname ?? 'User #' . $m->user_id) ?></div>
                        </div>
                        <div class="member-badges">
                          <?php if ($m->is_manager): ?>
                            <span class="role-badge role-manager">Manager</span>
                          <?php else: ?>
                            <span class="role-badge role-member">Member</span>
                          <?php endif; ?>
                          <?php if ($m->status === 'approved'): ?>
                            <span class="status-badge status-approved">Approved</span>
                          <?php elseif ($m->status === 'pending'): ?>
                            <span class="status-badge status-pending">Pending</span>
                          <?php elseif ($m->status === 'left'): ?>
                            <span class="status-badge status-left">Left</span>
                          <?php else: ?>
                            <span class="status-badge" style="background:#e9ecef;color:#6c757d;"><?= htmlspecialchars($m->status) ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($total_pages > 1): ?>
            <div class="pagination-wrap">
              <ul class="pagination">
                <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
                  <a href="<?= base_url('admin/timesync_teams?page=1') ?>">&laquo; First</a>
                </li>
                <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
                  <a href="<?= base_url('admin/timesync_teams?page=' . ($page - 1)) ?>">&lsaquo; Prev</a>
                </li>
                <?php
                  $start_p = max(1, $page - 2);
                  $end_p = min($total_pages, $page + 2);
                  for ($p = $start_p; $p <= $end_p; $p++):
                ?>
                  <li class="<?= $p === $page ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/timesync_teams?page=' . $p) ?>"><?= $p ?></a>
                  </li>
                <?php endfor; ?>
                <li class="<?= $page >= $total_pages ? 'disabled' : '' ?>">
                  <a href="<?= base_url('admin/timesync_teams?page=' . ($page + 1)) ?>">Next &rsaquo;</a>
                </li>
                <li class="<?= $page >= $total_pages ? 'disabled' : '' ?>">
                  <a href="<?= base_url('admin/timesync_teams?page=' . $total_pages) ?>">Last &raquo;</a>
                </li>
              </ul>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <?php echo form_open('admin/timesync_teams/save_member', ['class' => 'form-horizontal']); ?>
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-user-plus"></i> Assign Teams to User</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="col-lg-3 control-label">User</label>
            <div class="col-lg-8">
              <select name="user_id" id="assign-user" class="form-control assign-user-select" required>
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
              <div id="teams-checkbox-list" style="max-height:300px;overflow-y:auto;border:1px solid #e9ecef;border-radius:6px;padding:10px;">
                <p class="text-muted" style="margin:0;">Select a user first to load available teams.</p>
              </div>
            </div>
          </div>
          <div id="manager-warning" class="alert alert-warning hide" style="margin:0 15px 15px;">
            <i class="fa fa-exclamation-triangle"></i>
            Members marked as <strong>Manager</strong> cannot be removed here. Demote them first via the desktop app or API.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
        </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<script>
  function toggleTeam(el) {
    var body = el.nextElementSibling;
    var arrow = el.querySelector('.toggle-arrow');
    if (body.style.display === 'none' || body.style.display === '') {
      body.style.display = 'block';
      if (arrow) arrow.classList.add('open');
    } else {
      body.style.display = 'none';
      if (arrow) arrow.classList.remove('open');
    }
  }

  function openAssignModal() {
    $('#assignModal').modal('show');
  }

  $(document).ready(function () {
    $('#teamSearch').on('keyup', function () {
      var q = this.value.toLowerCase().trim();
      $('#teamsList > .team-card').each(function () {
        var name = $(this).data('name') || '';
        $(this).toggle(name.indexOf(q) !== -1);
      });
    });

    $('#assign-user').on('change', function () {
      var userId = $(this).val();
      var list = $('#teams-checkbox-list');
      if (!userId) {
        list.html('<p class="text-muted" style="margin:0;">Select a user first to load available teams.</p>');
        return;
      }
      $.get('<?= base_url('admin/timesync_teams/edit_member') ?>/' + userId, function (data) {
        var html = '';
        if (data.teams && data.teams.length > 0) {
          $.each(data.teams, function (i, team) {
            var checked = data.user_team_ids.indexOf(team.id) !== -1;
            html += '<div class="team-checkbox-item">';
            html += '<input type="checkbox" name="team_ids[]" value="' + team.id + '" id="team_' + team.id + '"' + (checked ? ' checked' : '') + '>';
            html += '<label for="team_' + team.id + '">' + escHtml(team.name) + '</label>';
            html += '</div>';
          });
        } else {
          html = '<p class="text-muted" style="margin:0;">No teams available.</p>';
        }
        list.html(html);
      });
    });

    // Charts
    requestAnimationFrame(function () {
      var teamLabels = <?= $chart_team_labels ?? '[]' ?>;
      var teamValues = <?= $chart_team_values ?? '[]' ?>;
      var statusLabels = <?= $chart_status_labels ?? '[]' ?>;
      var statusValues = <?= $chart_status_values ?? '[]' ?>;

      var tsCanvas = document.getElementById('teamSizeChart');
      if (teamLabels.length > 0 && tsCanvas && tsCanvas.parentElement.offsetWidth > 0) {
        new Chart(tsCanvas, {
          type: 'bar',
          data: {
            labels: teamLabels,
            datasets: [{
              label: 'Members',
              data: teamValues,
              backgroundColor: 'rgba(35, 183, 229, 0.6)',
              borderColor: 'rgba(35, 183, 229, 1)',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
              y: { beginAtZero: true, ticks: { stepSize: 1 } },
              x: { ticks: { maxRotation: 45, font: { size: 10 } } }
            }
          }
        });
      }

      var stCanvas = document.getElementById('statusChart');
      var stSum = statusValues.reduce(function (a, b) { return a + b; }, 0);
      if (stSum > 0 && stCanvas && stCanvas.parentElement.offsetWidth > 0) {
        new Chart(stCanvas, {
          type: 'doughnut',
          data: {
            labels: statusLabels,
            datasets: [{
              data: statusValues,
              backgroundColor: ['rgba(40, 167, 69, 0.7)', 'rgba(255, 193, 7, 0.7)', 'rgba(220, 53, 69, 0.7)'],
              borderColor: ['#28a745', '#ffc107', '#dc3545'],
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
          }
        });
      }
    });
  });

  function escHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }
</script>
