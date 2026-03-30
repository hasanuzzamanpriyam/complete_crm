<?php echo message_box('success'); ?>
<div class="row">
    <div class="col-lg-12">
        <section class="panel panel-custom">
            <header class="panel-heading">
                <?= isset($title) ? $title : 'API Documentation' ?>
            </header>
            <div class="panel-body">
                <p class="text-muted">
                    Welcome to the API Documentation. Here you can find details about available API endpoints.
                </p>

                <?php if (!empty($api_endpoints)): ?>
                    <div class="panel-group" id="api-docs-accordion">
                        <?php foreach ($api_endpoints as $index => $api): ?>
                            <div class="panel panel-default border-radius-5">
                                <div class="panel-heading" style="background-color: #f8f9fa;">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#api-docs-accordion" href="#collapse<?= $index ?>" style="display: block; width: 100%; text-decoration: none;">
                                            <span class="label label-success"><?= $api['method'] ?></span>
                                            <span class="text-bold" style="color: #333; margin-left: 10px;"><?= $api['name'] ?></span>
                                            <code class="pull-right" style="font-size: 13px;"><?= $api['endpoint'] ?></code>
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapse<?= $index ?>" class="panel-collapse collapse <?= $index === 0 ? 'in' : '' ?>">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p><strong>Description:</strong> <?= $api['description'] ?></p>
                                                <p><strong>Method:</strong> <span class="label label-success"><?= $api['method'] ?></span></p>
                                                <p><strong>URL:</strong> <code><?= $base_url . $api['endpoint'] ?></code></p>
                                            </div>
                                        </div>

                                        <?php if (!empty($api['parameters'])): ?>
                                            <div class="row mt-lg">
                                                <div class="col-md-12">
                                                    <h5><strong>Parameters</strong></h5>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th>Name</th>
                                                            <th>Type</th>
                                                            <th>Description</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ($api['parameters'] as $param): ?>
                                                            <tr>
                                                                <td><code><?= $param['name'] ?></code></td>
                                                                <td><?= $param['type'] ?></td>
                                                                <td><?= $param['description'] ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="row mt-lg">
                                            <div class="col-md-12">
                                                <h5><strong>Response Example (JSON)</strong></h5>
                                                <pre style="background: #2d2d2d; color: #ccc; border-radius: 5px; padding: 15px;"><code><?= json_encode($api['response_example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></code></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center">No API documentation available.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<style>
    .border-radius-5 {
        border-radius: 5px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
    }
    .mt-lg {
        margin-top: 20px;
    }
    .text-bold {
        font-weight: 600;
    }
    pre code {
        color: #f8f8f2;
    }
</style>
