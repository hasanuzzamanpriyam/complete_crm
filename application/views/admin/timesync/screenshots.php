<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <h3 class="panel-title"><?= $title ?? 'Screenshots' ?></h3>
            </header>
            <div class="panel-body">
                <div class="row mb-lg">
                    <div class="col-md-3">
                        <div class="panel panel-info">
                            <div class="panel-body text-center" id="screenshot_count">
                                <h2><?= $screenshot_count ?? 0 ?></h2>
                                <p class="text-muted">Current View</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-success">
                            <div class="panel-body text-center">
                                <h2><?= $total_screenshots ?? 0 ?></h2>
                                <p class="text-muted">Total All Time</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="get" class="form-inline mb-lg">
                    <div class="form-group">
                        <label>User: </label>
                        <select name="user_id" class="form-control">
                            <option value="">All Users</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u->user_id ?>" <?= $this->input->get('user_id') == $u->user_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u->fullname ?? $u->user_id) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group ml-sm">
                        <label>Task ID: </label>
                        <input type="number" name="task_id" class="form-control" value="<?= $this->input->get('task_id') ?>" placeholder="Task #" style="width:100px;">
                    </div>
                    <div class="form-group ml-sm">
                        <label>From: </label>
                        <input type="date" name="from" class="form-control" value="<?= $this->input->get('from') ?>">
                    </div>
                    <div class="form-group ml-sm">
                        <label>To: </label>
                        <input type="date" name="to" class="form-control" value="<?= $this->input->get('to') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary ml-sm">Filter</button>
                </form>

                <div class="row">
                    <?php if (!empty($screenshots)): ?>
                        <?php foreach ($screenshots as $s): ?>
                            <div class="col-md-3 col-sm-4 col-xs-6 mb-sm">
                                <div class="panel panel-default">
                                    <a href="<?= base_url('admin/timesync/view_image/' . $s->id) ?>" target="_blank" rel="noopener">
                                        <img src="<?= base_url('admin/timesync/view_image/' . $s->id) ?>" class="img-responsive" style="width:100%;height:160px;object-fit:cover;">
                                    </a>
                                    <div class="panel-body" style="padding:8px;">
                                        <p class="small" style="margin:0;">
                                            <strong><?= htmlspecialchars($s->fullname ?? 'User') ?></strong><br>
                                            <?= date('M d, Y H:i', strtotime($s->captured_at)) ?>
                                        </p>
                                        <?php if (!empty($s->task_id)): ?>
                                            <a href="<?= base_url('admin/tasks/view/' . $s->task_id) ?>" class="small"><?= htmlspecialchars($s->task_name ?? 'Task #' . $s->task_id) ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-md-12"><p class="text-center">No screenshots found</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>
