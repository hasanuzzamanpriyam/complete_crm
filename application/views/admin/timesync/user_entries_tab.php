<div class="panel panel-custom">
  <div class="panel-heading">
    <h4 class="panel-title">Time Entries</h4>
    <span class="pull-right text-muted small">Page <?= $entry_page ?> of <?= $entry_total_pages ?></span>
  </div>
  <div class="panel-body">
    <table class="table table-striped DataTables">
      <thead>
        <tr>
          <th>Date</th>
          <th>Type</th>
          <th>Started</th>
          <th>Stopped</th>
          <th>Duration</th>
          <th>Task</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($entries)): ?>
          <?php foreach ($entries as $e): ?>
            <tr>
              <td><?= date('Y-m-d', strtotime($e->started_at)) ?></td>
              <td><?= htmlspecialchars($e->type) ?></td>
              <td><?= $e->started_at ? date('H:i:s', strtotime($e->started_at)) : '-' ?></td>
              <td><?= $e->stopped_at ? date('H:i:s', strtotime($e->stopped_at)) : '-' ?></td>
              <td><?= gmdate('H:i:s', $e->total_seconds) ?></td>
              <td><a href="<?= base_url('admin/tasks/view/' . $e->task_id) ?>"><?= htmlspecialchars($e->task_name ?? '#' . $e->task_id) ?></a></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center">No entries found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($entry_total_pages > 1): ?>
      <div class="text-center" style="margin-top:12px;">
        <ul class="pagination" style="margin:0;">
          <li class="<?= $entry_page <= 1 ? 'disabled' : '' ?>">
            <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=entries&from=' . $from . '&to=' . $to . '&entry_page=1') ?>">&laquo;</a>
          </li>
          <li class="<?= $entry_page <= 1 ? 'disabled' : '' ?>">
            <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=entries&from=' . $from . '&to=' . $to . '&entry_page=' . ($entry_page - 1)) ?>">&lsaquo;</a>
          </li>
          <?php
            $s = max(1, $entry_page - 2);
            $e = min($entry_total_pages, $entry_page + 2);
            for ($p = $s; $p <= $e; $p++):
          ?>
            <li class="<?= $p === $entry_page ? 'active' : '' ?>">
              <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=entries&from=' . $from . '&to=' . $to . '&entry_page=' . $p) ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
          <li class="<?= $entry_page >= $entry_total_pages ? 'disabled' : '' ?>">
            <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=entries&from=' . $from . '&to=' . $to . '&entry_page=' . ($entry_page + 1)) ?>">&rsaquo;</a>
          </li>
          <li class="<?= $entry_page >= $entry_total_pages ? 'disabled' : '' ?>">
            <a href="<?= base_url('admin/timesync/user/' . $user_id . '?tab=entries&from=' . $from . '&to=' . $to . '&entry_page=' . $entry_total_pages) ?>">&raquo;</a>
          </li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</div>
