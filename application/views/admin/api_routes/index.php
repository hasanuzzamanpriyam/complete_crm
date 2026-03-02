<?php echo message_box('success'); ?>
<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <?= isset($title) ? $title : 'API Routes' ?>
            </header>
            <div class="panel-body">
                <p class="text-muted">
                    Below are all API routes registered in the application (from main config and modules).
                </p>
                <div class="table-responsive">
                    <table class="table table-striped table-hover DataTables">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Route URI</th>
                            <th>Full URL</th>
                            <th>Destination (controller/method)</th>
                            <th>Source</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (!empty($api_routes)) {
                            $i = 1;
                            foreach ($api_routes as $r) {
                                $full_url = rtrim($base_url, '/') . '/' . $r['uri'];
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><code><?= htmlspecialchars($r['uri']) ?></code></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($full_url) ?>" target="_blank" rel="noopener">
                                            <?= htmlspecialchars($full_url) ?>
                                        </a>
                                    </td>
                                    <td><code><?= htmlspecialchars($r['destination']) ?></code></td>
                                    <td><?= htmlspecialchars($r['source']) ?></td>
                                </tr>
                            <?php }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center">No API routes found.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
