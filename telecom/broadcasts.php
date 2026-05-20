<?php
include("../config/session.php");
require_role(['admin','doctor']);
include("../config/database.php");
include("../config/sanitize.php");

// ── Filters ────────────────────────────────────────────────────────────────
$type_f    = clean_enum($_GET['type']??'', ['alert','tip',''], '');
$sev_f     = clean_enum($_GET['severity']??'', ['critical','warning','info',''], '');
$date_f    = clean_date($_GET['date'] ?? '');
$search_f  = db_clean($conn, $_GET['search'] ?? '');

$where = [];
if ($type_f)   $where[] = "b.type='$type_f'";
if ($sev_f)    $where[] = "b.severity='$sev_f'";
if ($date_f)   $where[] = "DATE(b.sent_at)='$date_f'";
if ($search_f) $where[] = "(b.title LIKE '%$search_f%' OR b.message LIKE '%$search_f%')";
$wsql = $where ? 'WHERE '.implode(' AND ',$where) : '';

// ── Stats ──────────────────────────────────────────────────────────────────
$total_b     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM broadcast_logs"))['t']??0;
$total_reach = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(recipients_count) AS t FROM broadcast_logs"))['t']??0;
$alerts_cnt  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM broadcast_logs WHERE type='alert'"))['t']??0;
$tips_cnt    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM broadcast_logs WHERE type='tip'"))['t']??0;

// ── Pagination ─────────────────────────────────────────────────────────────
$per   = 25;
$page  = max(1, intval($_GET['page']??1));
$off   = ($page-1)*$per;
$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM broadcast_logs b $wsql"))['t']??0;
$pages = max(1, ceil($total/$per));
$logs  = mysqli_query($conn,"SELECT b.*, a.full_name AS sender FROM broadcast_logs b LEFT JOIN admins a ON b.sent_by=a.id $wsql ORDER BY b.sent_at DESC LIMIT $per OFFSET $off");
$qs = http_build_query(array_filter(['type'=>$type_f,'severity'=>$sev_f,'date'=>$date_f,'search'=>$_GET['search']??'']));
$qs = $qs ? '&'.$qs : '';
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Broadcast Log</div>
                <div class="page-subtitle">All sent alerts and health tip broadcasts</div>
            </div>
            <div class="actions">
                <a href="alerts.php" class="btn btn-outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Alerts
                </a>
                <a href="health_tips.php" class="btn btn-outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    Health Tips
                </a>
            </div>
        </div>

        <!-- Stats row -->
        <div class="stats-grid" style="margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-icon teal"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg></div>
                <div><div class="stat-label">Total Broadcasts</div><div class="stat-value"><?php echo number_format($total_b); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <div><div class="stat-label">Total Patients Reached</div><div class="stat-value"><?php echo number_format($total_reach); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
                <div><div class="stat-label">Emergency Alerts</div><div class="stat-value"><?php echo number_format($alerts_cnt); ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
                <div><div class="stat-label">Health Tips Sent</div><div class="stat-value"><?php echo number_format($tips_cnt); ?></div></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card card-body" style="margin-bottom:20px;">
            <form method="GET" class="filter-row">
                <div class="filter-group">
                    <label class="form-label">Search</label>
                    <div class="search-wrap">
                        <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" class="form-control search-input" placeholder="Title or message…" value="<?php echo htmlspecialchars($_GET['search']??''); ?>">
                    </div>
                </div>
                <div class="filter-group" style="min-width:130px;">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control">
                        <option value="">All types</option>
                        <option value="alert" <?php if($type_f==='alert') echo 'selected'; ?>>Alerts</option>
                        <option value="tip"   <?php if($type_f==='tip')   echo 'selected'; ?>>Health Tips</option>
                    </select>
                </div>
                <div class="filter-group" style="min-width:140px;">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-control">
                        <option value="">All</option>
                        <option value="critical" <?php if($sev_f==='critical') echo 'selected'; ?>>Critical</option>
                        <option value="warning"  <?php if($sev_f==='warning')  echo 'selected'; ?>>Warning</option>
                        <option value="info"     <?php if($sev_f==='info')     echo 'selected'; ?>>Info</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_f); ?>">
                </div>
                <div class="filter-group filter-actions">
                    <label class="form-label">&nbsp;</label>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="broadcasts.php" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Title</th>
                        <th>Audience</th>
                        <th>Lang</th>
                        <th>Reached</th>
                        <th>Sent By</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($logs)===0): ?>
                    <tr><td colspan="8"><div class="empty-state"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/></svg><h3>No broadcasts found</h3><p>Adjust your filters</p></div></td></tr>
                <?php else: ?>
                <?php while($b = mysqli_fetch_assoc($logs)):
                    $sev_cls = ['critical'=>'badge-danger','warning'=>'badge-warning','info'=>'badge-primary'];
                    $lang_lbl = ['sw'=>'Swahili','en'=>'English','both'=>'Both'];
                ?>
                    <tr>
                        <td style="font-size:.78rem;font-family:var(--font-mono);white-space:nowrap;color:var(--text-muted);"><?php echo htmlspecialchars($b['sent_at']); ?></td>
                        <td>
                            <?php if($b['type']==='alert'): ?>
                            <span class="badge badge-danger">Alert</span>
                            <?php else: ?>
                            <span class="badge badge-success">Tip</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?php echo $sev_cls[$b['severity']]??'badge-primary'; ?>"><?php echo ucfirst($b['severity']); ?></span></td>
                        <td style="font-weight:600;font-size:.875rem;max-width:200px;"><?php echo htmlspecialchars($b['title']); ?></td>
                        <td style="font-size:.82rem;text-transform:capitalize;"><?php echo htmlspecialchars($b['target_audience']); ?></td>
                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($lang_lbl[$b['language']]??$b['language']); ?></span></td>
                        <td style="font-weight:700;color:var(--primary);"><?php echo number_format($b['recipients_count']); ?></td>
                        <td style="font-size:.8rem;color:var(--text-muted);"><?php echo htmlspecialchars($b['sender']??'—'); ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($pages>1): ?>
        <div class="pagination">
            <?php if($page>1): ?><a href="?page=<?php echo $page-1; ?><?php echo $qs; ?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></a><?php endif; ?>
            <?php for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++): ?><a href="?page=<?php echo $i; ?><?php echo $qs; ?>" class="page-btn <?php if($i==$page)echo 'active'; ?>"><?php echo $i; ?></a><?php endfor; ?>
            <?php if($page<$pages): ?><a href="?page=<?php echo $page+1; ?><?php echo $qs; ?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></a><?php endif; ?>
        </div>
        <?php endif; ?>

    </main>
</div>
<?php include("../includes/footer.php"); ?>
