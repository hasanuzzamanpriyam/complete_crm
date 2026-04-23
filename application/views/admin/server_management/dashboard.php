<?php 
// Calculate combined totals for the unified cards
$total_all    = ($stats['total_hostings'] ?? 0) + ($stats['total_domains'] ?? 0);
$active_all   = ($stats['active_hostings'] ?? 0) + ($stats['active_domains'] ?? 0);
$pending_all  = ($stats['pending_hostings'] ?? 0) + ($stats['pending_domains'] ?? 0);
$expiring_all = ($stats['expiring_hostings'] ?? 0) + ($stats['expiring_domains'] ?? 0);
?>

<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<style>
    /* Clean, Corporate Card Styles */
    .stat-box {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px 20px 15px 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        border: 1px solid #eaeaea;
        margin-bottom: 20px;
        transition: all 0.2s ease-in-out;
        display: flex;
        flex-direction: column;
    }
    
    .stat-box:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }

    /* Top Section containing Icon and Main Number */
    .stat-main {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .stat-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-right: 15px;
        flex-shrink: 0;
    }

    /* Theme-matched colors */
    .icon-theme-red { background-color: rgba(244, 67, 54, 0.1); color: #f44336; }
    .icon-theme-blue { background-color: rgba(23, 162, 184, 0.1); color: #17a2b8; }
    .icon-theme-green { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
    .icon-theme-yellow { background-color: rgba(255, 193, 7, 0.15); color: #e0a800; }

    .stat-info {
        flex-grow: 1;
    }

    .stat-info .stat-title {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        display: block;
        margin-bottom: 4px;
    }

    .stat-info h3 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #333;
        line-height: 1;
    }

    /* Bottom Section for Breakdown */
    .stat-footer {
        border-top: 1px solid #f0f0f0;
        padding-top: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-sub-item {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        align-items: center;
    }

    .stat-sub-item i {
        margin-right: 6px;
        opacity: 0.7;
    }

    .stat-sub-item strong {
        color: #333;
        margin-right: 4px;
        font-size: 13px;
    }

    /* Panel/Card Overrides to match your tables */
    .dashboard-panel {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        border: 1px solid #eaeaea;
        margin-bottom: 20px;
    }
    
    .dashboard-panel .panel-heading {
        padding: 15px 20px;
        border-bottom: 1px solid #eaeaea;
        background-color: #f8f9fa;
        border-radius: 8px 8px 0 0;
    }
    
    .dashboard-panel .panel-heading h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Refined Activity List */
    .activity-feed {
        padding: 0;
        margin: 0;
        list-style: none;
    }
    
    .activity-feed li {
        padding: 15px 20px;
        border-bottom: 1px solid #f4f4f4;
        display: flex;
        align-items: center;
    }
    
    .activity-feed li:last-child {
        border-bottom: none;
    }
    
    .activity-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background-color: #f4f4f4;
        color: #888;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .activity-details {
        flex-grow: 1;
        font-size: 13px;
        color: #495057;
    }
    
    .activity-time {
        font-size: 12px;
        color: #aaa;
        white-space: nowrap;
        margin-left: 15px;
    }

    /* Empty States */
    .empty-state-box {
        padding: 40px 20px;
        text-align: center;
    }
    .empty-state-box i {
        font-size: 40px;
        color: #e0e0e0;
        margin-bottom: 15px;
    }
    .empty-state-box p {
        color: #888;
        margin: 0;
        font-size: 14px;
    }
</style>

<div class="row">
    
    <div class="col-md-3 col-sm-6">
        <div class="stat-box">
            <div class="stat-main">
                <div class="stat-icon-wrapper icon-theme-blue">
                    <i class="fa fa-cubes"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-title">Total Services</span>
                    <h3><?= $total_all ?></h3>
                </div>
            </div>
            <div class="stat-footer">
                <div class="stat-sub-item">
                    <i class="fa fa-server"></i> <strong><?= $stats['total_hostings'] ?? 0 ?></strong> Hostings
                </div>
                <div class="stat-sub-item">
                    <i class="fa fa-globe"></i> <strong><?= $stats['total_domains'] ?? 0 ?></strong> Domains
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="stat-box">
            <div class="stat-main">
                <div class="stat-icon-wrapper icon-theme-green">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-title">Active Services</span>
                    <h3><?= $active_all ?></h3>
                </div>
            </div>
            <div class="stat-footer">
                <div class="stat-sub-item">
                    <i class="fa fa-server text-success"></i> <strong><?= $stats['active_hostings'] ?? 0 ?></strong> Hostings
                </div>
                <div class="stat-sub-item">
                    <i class="fa fa-globe text-success"></i> <strong><?= $stats['active_domains'] ?? 0 ?></strong> Domains
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="stat-box">
            <div class="stat-main">
                <div class="stat-icon-wrapper icon-theme-yellow">
                    <i class="fa fa-clock-o"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-title">Pending Services</span>
                    <h3><?= $pending_all ?></h3>
                </div>
            </div>
            <div class="stat-footer">
                <div class="stat-sub-item">
                    <i class="fa fa-server text-warning"></i> <strong><?= $stats['pending_hostings'] ?? 0 ?></strong> Hostings
                </div>
                <div class="stat-sub-item">
                    <i class="fa fa-globe text-warning"></i> <strong><?= $stats['pending_domains'] ?? 0 ?></strong> Domains
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="stat-box" style="border-top: 3px solid #f44336;">
            <div class="stat-main">
                <div class="stat-icon-wrapper icon-theme-red">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-title">Expiring (30 Days)</span>
                    <h3><?= $expiring_all ?></h3>
                </div>
            </div>
            <div class="stat-footer">
                <div class="stat-sub-item">
                    <i class="fa fa-server text-danger"></i> <strong><?= $stats['expiring_hostings'] ?? 0 ?></strong> Hostings
                </div>
                <div class="stat-sub-item">
                    <i class="fa fa-globe text-danger"></i> <strong><?= $stats['expiring_domains'] ?? 0 ?></strong> Domains
                </div>
            </div>
        </div>
    </div>
    
</div>

<div class="row">
    <div class="col-md-6">
        <div class="dashboard-panel">
            <div class="panel-heading">
                <h5><i class="fa fa-exclamation-triangle mr-2 text-danger"></i> Expiring Soon (30 Days)</h5>
            </div>
            
            <?php if (!empty($expiring_items)): ?>
                <ul class="activity-feed">
                    <?php foreach ($expiring_items as $item): ?>
                        <li>
                            <div class="activity-avatar" style="background-color: <?= $item['type'] === 'domain' ? 'rgba(23, 162, 184, 0.1)' : 'rgba(40, 167, 69, 0.1)' ?>; color: <?= $item['type'] === 'domain' ? '#17a2b8' : '#28a745' ?>;">
                                <i class="fa <?= $item['type'] === 'domain' ? 'fa-globe' : 'fa-server' ?>"></i>
                            </div>
                            <div class="activity-details">
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                <br>
                                <small class="text-muted">
                                    <span class="badge badge-<?= $item['type'] === 'domain' ? 'info' : 'success' ?>" style="padding: 2px 6px; font-size: 10px;"><?= ucfirst($item['type']) ?></span>
                                    Expires: <?= date('M j, Y', strtotime($item['expiry_date'])) ?>
                                </small>
                            </div>
                            <div class="activity-time">
                                <?php
                                $badge_class = 'badge-danger';
                                if ($item['days_left'] > 21) {
                                    $badge_class = 'badge-warning';
                                } elseif ($item['days_left'] > 7) {
                                    $badge_class = 'badge-info';
                                }
                                ?>
                                <span class="badge <?= $badge_class ?>" style="padding: 4px 8px; font-size: 11px;">
                                    <?= $item['days_left'] ?> days
                                </span>
                                <br>
                                <a href="<?= base_url($item['link']) ?>" class="btn btn-sm btn-link text-primary mt-1" style="font-size: 11px;">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state-box">
                    <i class="fa fa-check-circle text-success"></i>
                    <p>No items expiring within 30 days!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="dashboard-panel">
            <div class="panel-heading">
                <h5><i class="fa fa-history mr-2 text-muted"></i> Recent Activities</h5>
            </div>
            
            <?php if (!empty($recent_activities)): ?>
                <ul class="activity-feed">
                    <?php foreach ($recent_activities as $activity): ?>
                        <li>
                            <div class="activity-avatar">
                                <i class="fa <?= !empty($activity['icon']) ? htmlspecialchars($activity['icon']) : 'fa-user' ?>"></i>
                            </div>
                            <div class="activity-details">
                                <strong><?= htmlspecialchars($activity['action']) ?></strong>
                                <br>
                                <small class="text-muted">by <span class="text-info"><?= htmlspecialchars($activity['user']) ?></span></small>
                            </div>
                            <div class="activity-time">
                                <i class="fa fa-clock-o"></i> <?= htmlspecialchars($activity['time']) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state-box">
                    <i class="fa fa-inbox"></i>
                    <p>No recent activities recorded.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>