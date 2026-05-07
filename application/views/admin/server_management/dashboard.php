<?php 
// Calculate combined totals for the unified cards
$total_all    = ($stats['total_hostings'] ?? 0) + ($stats['total_domains'] ?? 0) + ($stats['total_providers'] ?? 0);
$active_all   = ($stats['active_hostings'] ?? 0) + ($stats['active_domains'] ?? 0) + ($stats['active_providers'] ?? 0);
$pending_all  = ($stats['pending_hostings'] ?? 0) + ($stats['pending_domains'] ?? 0);
$expiring_all = ($stats['expiring_hostings'] ?? 0) + ($stats['expiring_domains'] ?? 0);
$expired_all = ($stats['expired_hostings'] ?? 0) + ($stats['expired_domains'] ?? 0);
$inactive_all = ($stats['inactive_providers'] ?? 0);
?>

<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<style>
    /* Custom 5-Column Grid for exactly 5 cards */
    .five-column-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }
    @media (max-width: 1200px) { .five-column-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .five-column-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) { .five-column-grid { grid-template-columns: 1fr; } }

    /* Clean Unified Card Styles */
    .stat-box {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        border: 1px solid #eaeaea;
        transition: all 0.2s ease-in-out;
        display: flex;
        flex-direction: column;
        height: 100%;
        cursor: pointer;
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
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-right: 15px;
        flex-shrink: 0;
    }

    /* Theme Colors */
    .bg-light-blue { background-color: rgba(23, 162, 184, 0.1); color: #17a2b8; }
    .bg-light-green { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
    .bg-light-yellow { background-color: rgba(255, 193, 7, 0.15); color: #e0a800; }
    .bg-light-orange { background-color: rgba(253, 126, 20, 0.1); color: #fd7e14; }
    .bg-light-red { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }

    .stat-info .stat-title {
        font-size: 13px;
        color: #6c757d;
        font-weight: 600;
        display: block;
        margin-bottom: 2px;
    }

    .stat-info h3 {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #333;
        line-height: 1;
    }

    /* Bottom Breakdown Pills */
    .stat-breakdown {
        display: flex;
        gap: 8px;
        margin-top: auto; /* Pushes to bottom */
        border-top: 1px dashed #eaeaea;
        padding-top: 12px;
    }
    
    .mini-pill {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        background: #f8f9fa;
        color: #495057;
        flex: 1;
        text-align: center;
        border: 1px solid #eee;
        transition: all 0.2s;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mini-pill:hover {
        background: #e9ecef;
        border-color: #ddd;
        color: #212529;
    }
    .mini-pill i { margin-right: 3px; opacity: 0.7; }

    /* Panels for Lists */
    .dashboard-panel {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        border: 1px solid #eaeaea;
        margin-bottom: 25px;
        height: 100%;
    }
    .dashboard-panel .panel-heading {
        padding: 15px 20px;
        border-bottom: 1px solid #eaeaea;
        background-color: #fcfcfc;
        border-radius: 8px 8px 0 0;
    }
    .dashboard-panel .panel-heading h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }

    /* Activity Lists */
    .activity-feed { padding: 0; margin: 0; list-style: none; }
    .activity-feed li {
        padding: 15px 20px;
        border-bottom: 1px solid #f4f4f4;
        display: flex;
        align-items: center;
    }
    .activity-feed li:last-child { border-bottom: none; }
    
    .activity-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .activity-details { flex-grow: 1; font-size: 13px; color: #495057; }
    .activity-time { font-size: 12px; text-align: right; margin-left: 15px; }
    
    /* Empty State */
    .empty-state-box {
        padding: 30px 20px;
        text-align: center;
        color: #888;
    }
    .empty-state-box i { font-size: 30px; color: #e0e0e0; margin-bottom: 10px; display: block; }
</style>

<div class="five-column-grid">
    
    <div class="stat-box" onclick="location.href='<?= base_url('admin/server_management/hosting') ?>'">
        <div class="stat-main">
            <div class="stat-icon-wrapper bg-light-blue"><i class="fa fa-cubes"></i></div>
            <div class="stat-info">
                <span class="stat-title">Total Services</span>
                <h3><?= $total_all ?></h3>
            </div>
        </div>
        <div class="stat-breakdown">
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/hosting') ?>'"><i class="fa fa-server text-info"></i> <?= $stats['total_hostings'] ?? 0 ?> Host</div>
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/domain') ?>'"><i class="fa fa-globe text-info"></i> <?= $stats['total_domains'] ?? 0 ?> Dom</div>
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/provider') ?>'"><i class="fa fa-briefcase text-info"></i> <?= $stats['total_providers'] ?? 0 ?> Prov</div>
        </div>
    </div>
    
    <div class="stat-box" onclick="location.href='<?= base_url('admin/server_management/hosting?status=Active') ?>'">
        <div class="stat-main">
            <div class="stat-icon-wrapper bg-light-green"><i class="fa fa-check-circle"></i></div>
            <div class="stat-info">
                <span class="stat-title">Active</span>
                <h3><?= $active_all ?></h3>
            </div>
        </div>
        <div class="stat-breakdown">
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/hosting?status=Active') ?>'"><i class="fa fa-server text-success"></i> <?= $stats['active_hostings'] ?? 0 ?> Host</div>
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/domain?status=Active') ?>'"><i class="fa fa-globe text-success"></i> <?= $stats['active_domains'] ?? 0 ?> Dom</div>
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/provider?status=Active') ?>'"><i class="fa fa-briefcase text-success"></i> <?= $stats['active_providers'] ?? 0 ?> Prov</div>
        </div>
    </div>
    
    <div class="stat-box" onclick="location.href='<?= base_url('admin/server_management/hosting?status=Pending') ?>'">
        <div class="stat-main">
            <div class="stat-icon-wrapper bg-light-yellow"><i class="fa fa-clock-o"></i></div>
            <div class="stat-info">
                <span class="stat-title">Pending</span>
                <h3><?= $pending_all ?></h3>
            </div>
        </div>
        <div class="stat-breakdown">
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/hosting?status=Pending') ?>'"><i class="fa fa-server text-warning"></i> <?= $stats['pending_hostings'] ?? 0 ?> Host</div>
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/domain?status=Pending') ?>'"><i class="fa fa-globe text-warning"></i> <?= $stats['pending_domains'] ?? 0 ?> Dom</div>
        </div>
    </div>
    
    <div class="stat-box" style="border-top: 3px solid #fd7e14;" onclick="location.href='<?= base_url('admin/server_management/hosting?status=Expiring') ?>'">
        <div class="stat-main">
            <div class="stat-icon-wrapper bg-light-orange"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <span class="stat-title">Expiring (30 Days)</span>
                <h3><?= $expiring_all ?></h3>
            </div>
        </div>
        <div class="stat-breakdown">
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/hosting?status=Expiring') ?>'"><i class="fa fa-server text-warning"></i> <?= $stats['expiring_hostings'] ?? 0 ?> Host</div>
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/domain?status=Expiring') ?>'"><i class="fa fa-globe text-warning"></i> <?= $stats['expiring_domains'] ?? 0 ?> Dom</div>
        </div>
    </div>
    
    <div class="stat-box" style="border-top: 3px solid #dc3545;" onclick="location.href='<?= base_url('admin/server_management/hosting?status=Expired') ?>'">
        <div class="stat-main">
            <div class="stat-icon-wrapper bg-light-red"><i class="fa fa-times-circle"></i></div>
            <div class="stat-info">
                <span class="stat-title">Expired/Inactive</span>
                <h3><?= $expired_all + $inactive_all ?></h3>
            </div>
        </div>
        <div class="stat-breakdown">
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/hosting?status=Expired') ?>'"><i class="fa fa-server text-danger"></i> <?= $stats['expired_hostings'] ?? 0 ?> Host</div>
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/domain?status=Expired') ?>'"><i class="fa fa-globe text-danger"></i> <?= $stats['expired_domains'] ?? 0 ?> Dom</div>
            <div class="mini-pill" onclick="event.stopPropagation(); location.href='<?= base_url('admin/server_management/provider?status=Inactive') ?>'"><i class="fa fa-briefcase text-danger"></i> <?= $stats['inactive_providers'] ?? 0 ?> Prov</div>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="dashboard-panel">
            <div class="panel-heading">
                <h5><i class="fa fa-exclamation-triangle mr-2 text-warning"></i> Expiring Within 30 Days</h5>
            </div>
            
            <?php if (!empty($expiring_items)): ?>
                <ul class="activity-feed">
                    <?php foreach ($expiring_items as $item): ?>
                        <li>
                            <div class="activity-avatar bg-light">
                                <i class="fa <?= $item['type'] === 'domain' ? 'fa-globe text-info' : 'fa-server text-success' ?>"></i>
                            </div>
                            <div class="activity-details">
                                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                <small class="text-muted">Expires: <?= date('M j, Y', strtotime($item['expiry_date'])) ?></small>
                            </div>
                            <div class="activity-time">
                                <?php
                                $badge_class = 'badge-danger';
                                if ($item['days_left'] > 21) { $badge_class = 'badge-warning'; } 
                                elseif ($item['days_left'] > 7) { $badge_class = 'badge-info'; }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= $item['days_left'] ?> days left</span><br>
                                <a href="<?= base_url($item['link']) ?>" class="text-primary" style="font-size: 11px;"><i class="fa fa-pencil"></i> edit</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state-box">
                    <i class="fa fa-check-circle text-success"></i>
                    <p>All clear! No items expiring soon.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="dashboard-panel">
            <div class="panel-heading">
                <h5><i class="fa fa-times-circle mr-2 text-danger"></i> Expired / Inactive Items (<?= count($expired_items ?? []) ?> expired, <?= count($inactive_providers ?? []) ?> inactive)</h5>
            </div>
            
            <?php $all_inactive = array_merge($expired_items ?? [], $inactive_providers ?? []); ?>
            <?php if (is_array($all_inactive) && count($all_inactive) > 0): ?>
                <ul class="activity-feed">
                    <?php foreach ($all_inactive as $item): ?>
                        <li>
                            <div class="activity-avatar bg-light-red">
                                <i class="fa <?= ($item['type'] ?? '') === 'provider' ? 'fa-briefcase' : (($item['type'] ?? '') === 'domain' ? 'fa-globe' : 'fa-server') ?>"></i>
                            </div>
                            <div class="activity-details">
                                <strong><?= htmlspecialchars($item['name'] ?? '') ?></strong><br>
                                <small class="text-muted">
                                    <?php if (($item['type'] ?? '') === 'provider'): ?>
                                        Status: <?= $item['status'] ?? 'Inactive' ?>
                                    <?php else: ?>
                                        Expired: <?= date('M j, Y', strtotime($item['expiry_date'] ?? date('Y-m-d'))) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="activity-time">
                                <?php if (($item['type'] ?? '') !== 'provider'): ?>
                                    <span class="badge badge-danger"><?= intval($item['days_expired'] ?? 0) ?> days ago</span><br>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span><br>
                                <?php endif; ?>
                                <a href="<?= base_url($item['link'] ?? '#') ?>" class="text-primary" style="font-size: 11px;"><i class="fa fa-pencil"></i> View</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state-box">
                    <i class="fa fa-check-circle text-success"></i>
                    <p>Great! All items are active.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="dashboard-panel">
            <div class="panel-heading">
                <h5><i class="fa fa-play-circle mr-2 text-success"></i> Currently Running Services (<?= count($running_items ?? []) ?>)</h5>
            </div>
            
            <?php if (!empty($running_items)): ?>
                <ul class="activity-feed">
                    <?php foreach ($running_items as $item): ?>
                        <li>
                            <div class="activity-avatar bg-light-green">
                                <i class="fa <?= $item['type'] === 'domain' ? 'fa-globe text-success' : 'fa-server text-success' ?>"></i>
                            </div>
                            <div class="activity-details">
                                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                <small class="text-muted">
                                    Purchased: <?= date('M j, Y', strtotime($item['purchase_date'])) ?> • 
                                    Expires: <?= date('M j, Y', strtotime($item['expiry_date'])) ?>
                                </small>
                            </div>
                            <div class="activity-time">
                                <span class="badge badge-success" style="color: #17a2b8;"><?= $item['running_for'] ?></span><br>                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state-box">
                    <i class="fa fa-stop-circle text-muted"></i>
                    <p>No running services at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="dashboard-panel">
            <div class="panel-heading">
                <h5><i class="fa fa-history mr-2 text-muted"></i> Recent Activities</h5>
            </div>
            
            <?php if (!empty($recent_activities)): ?>
                <ul class="activity-feed">
                    <?php foreach ($recent_activities as $activity): ?>
                        <li>
                            <div class="activity-avatar bg-light">
                                <i class="fa <?= !empty($activity['icon']) ? htmlspecialchars($activity['icon']) : 'fa-user text-muted' ?>"></i>
                            </div>
                            <div class="activity-details">
                                <strong><?= htmlspecialchars($activity['action']) ?></strong><br>
                                <small class="text-muted">Action by <span class="text-info"><?= htmlspecialchars($activity['user']) ?></span></small>
                            </div>
                            <div class="activity-time text-muted">
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