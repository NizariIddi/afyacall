<?php
include("../config/session.php");
require_role(['admin']);
include("../config/database.php");
include("../config/sanitize.php");

// ── Filters ───────────────────────────────────────────────────────────────
$search   = clean($_GET['search']   ?? '');
$role_f   = clean($_GET['role']     ?? '');
$module_f = clean($_GET['module']   ?? '');
$date_f   = clean_date($_GET['date'] ?? '');

$where = [];
if ($search)   { $s = db_clean($conn,$search);   $where[] = "(user_name LIKE '%$s%' OR action LIKE '%$s%')"; }
if ($role_f)   { $r = db_clean($conn,$role_f);   $where[] = "user_role='$r'"; }
if ($module_f) { $m = db_clean($conn,$module_f); $where[] = "module='$m'"; }
if ($date_f)   { $d = db_clean($conn,$date_f);   $where[] = "DATE(created_at)='$d'"; }
$wsql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Pagination ─────────────────────────────────────────────────────────────
$per   = 30;
$page  = max(1, intval($_GET['page'] ?? 1));
$off   = ($page-1)*$per;
$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM activity_logs $wsql"))['t'];
$pages = max(1, ceil($total/$per));
$logs  = mysqli_query($conn,"SELECT * FROM activity_logs $wsql ORDER BY created_at DESC LIMIT $per OFFSET $off");

// Distinct modules for filter dropdown
$modules_res = mysqli_query($conn,"SELECT DISTINCT module FROM activity_logs WHERE module!='' ORDER BY module");

// Build QS for pagination
$qs = http_build_query(array_filter(['search'=>$search,'role'=>$role_f,'module'=>$module_f,'date'=>$date_f]));
$qs = $qs ? '&'.$qs : '';
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Activity Log</div>
                <div class="page-subtitle">Full audit trail of all system actions</div>
            </div>
            <div class="actions">
                <span style="font-size:.82rem;color:var(--text-muted);"><?php echo number_format($total); ?> entries</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="card card-body" style="margin-bottom:20px;">
            <form method="GET" class="filter-row">
                <div class="filter-group">
                    <label class="form-label">Search</label>
                    <div class="search-wrap">
                        <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" class="form-control search-input" placeholder="Name or action…" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="filter-group" style="min-width:130px;">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="">All roles</option>
                        <option value="admin"  <?php if($role_f==='admin')  echo 'selected'; ?>>Admin</option>
                        <option value="doctor" <?php if($role_f==='doctor') echo 'selected'; ?>>Doctor</option>
                        <option value="nurse"  <?php if($role_f==='nurse')  echo 'selected'; ?>>Nurse</option>
                    </select>
                </div>
                <div class="filter-group" style="min-width:150px;">
                    <label class="form-label">Module</label>
                    <select name="module" class="form-control">
                        <option value="">All modules</option>
                        <?php while($m = mysqli_fetch_assoc($modules_res)): ?>
                        <option value="<?php echo htmlspecialchars($m['module']); ?>" <?php if($module_f===$m['module']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars(ucfirst($m['module'])); ?>
                        </option>
                        <?php endwhile; ?>
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
                        <a href="activity_log.php" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($logs) === 0): ?>
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                            <h3>No log entries found</h3>
                            <p>Adjust your filters or check back later</p>
                        </div>
                    </td></tr>
                <?php else: ?>
                <?php while($log = mysqli_fetch_assoc($logs)):
                    $role_cls = ['admin'=>'badge-danger','doctor'=>'badge-primary','nurse'=>'badge-success'];
                    $module_colors = [
                        'auth'        => 'badge-warning',
                        'patients'    => 'badge-primary',
                        'records'     => 'badge-success',
                        'appointments'=> 'badge-blue',
                        'admin'       => 'badge-danger',
                    ];
                    $mod_cls = $module_colors[$log['module']] ?? 'badge-primary';
                ?>
                    <tr>
                        <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;font-family:var(--font-mono);">
                            <?php echo htmlspecialchars($log['created_at']); ?>
                        </td>
                        <td>
                            <div class="patient-name"><?php echo htmlspecialchars($log['user_name']); ?></div>
                        </td>
                        <td><span class="badge <?php echo $role_cls[$log['user_role']] ?? 'badge-primary'; ?>"><?php echo ucfirst(htmlspecialchars($log['user_role'])); ?></span></td>
                        <td style="font-size:.875rem;"><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php if($log['module']): ?><span class="badge <?php echo $mod_cls; ?>"><?php echo htmlspecialchars(ucfirst($log['module'])); ?></span><?php else: echo '—'; endif; ?></td>
                        <td style="font-family:var(--font-mono);font-size:.78rem;color:var(--text-muted);"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($pages > 1): ?>
        <div class="pagination">
            <?php if($page>1): ?><a href="?page=<?php echo $page-1; ?><?php echo $qs; ?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></a><?php endif; ?>
            <?php for($i=max(1,$page-2); $i<=min($pages,$page+2); $i++): ?>
            <a href="?page=<?php echo $i; ?><?php echo $qs; ?>" class="page-btn <?php if($i==$page) echo 'active'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if($page<$pages): ?><a href="?page=<?php echo $page+1; ?><?php echo $qs; ?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></a><?php endif; ?>
        </div>
        <?php endif; ?>

    </main>
</div>
<?php include("../includes/footer.php"); ?>
