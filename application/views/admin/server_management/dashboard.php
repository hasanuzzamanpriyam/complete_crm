<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<style>
    .stat-card {
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        height: 100%;
    }
    .stat-card .stat-icon {
        font-size: 36px;
        color: #6c757d;
        opacity: 0.6;
    }
    .stat-card .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #212529;
        line-height: 1;
    }
    .stat-card .stat-label {
        font-size: 13px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .stat-card.expiring {
        border-left: 4px solid #ffc107;
    }
    .activity-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    .activity-item:last-child {
        border-bottom: none;
    }
    .activity-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }
    .activity-avatar i {
        color: #6c757d;
        font-size: 18px;
    }
    .activity-content {
        flex-grow: 1;
    }
    .activity-text {
        color: #212529;
        margin-bottom: 2px;
    }
    .activity-time {
        font-size: 12px;
        color: #6c757d;
    }
</style>

<div class="row mb-lg">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Hostings</div>
                    <div class="stat-number"><?= $stats['total_hostings'] ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-server"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Active Hostings</div>
                    <div class="stat-number"><?= $stats['active_hostings'] ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Domains</div>
                    <div class="stat-number"><?= $stats['total_domains'] ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-globe"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Active Domains</div>
                    <div class="stat-number"><?= $stats['active_domains'] ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-lg">
    <div class="col-md-3">
        <div class="stat-card expiring">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Expiring Hostings <i class="fa fa-question-circle text-muted" data-toggle="tooltip" title="Expiring within 30 days"></i></div>
                    <div class="stat-number"><?= $stats['expiring_hostings'] ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-exclamation-triangle text-warning"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card expiring">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Expiring Domains <i class="fa fa-question-circle text-muted" data-toggle="tooltip" title="Expiring within 30 days"></i></div>
                    <div class="stat-number"><?= $stats['expiring_domains'] ?></div>
                </div>
                <div class="stat-icon"><i class="fa fa-exclamation-triangle text-warning"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-lg">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Expiring Hostings</h5>
            </div>
            <div class="card-body text-center py-5">
                <i class="fa fa-check-circle text-success" style="font-size: 48px;"></i>
                <p class="mt-3 mb-0 text-muted">No hostings expiring in the next 30 days</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Expiring Domains</h5>
            </div>
            <div class="card-body text-center py-5">
                <i class="fa fa-check-circle text-success" style="font-size: 48px;"></i>
                <p class="mt-3 mb-0 text-muted">No domains expiring in the next 30 days</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_activities)): ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-avatar">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text"><?= $activity['action'] ?> By <?= $activity['user'] ?></div>
                                <div class="activity-time"><?= $activity['time'] ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">No recent activities</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>