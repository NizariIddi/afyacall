<?php
/**
 * view_schedule.php — Phase 4: Doctor Scheduling
 * Shows all doctor shifts. Admins can add/edit/delete shifts.
 */
include("../config/session.php");
include("../config/database.php");

// ── Filters ────────────────────────────────────────────────
$filter_doctor = intval($_GET['doctor'] ?? 0);
$filter_date   = $_GET['date'] ?? '';
$filter_week   = $_GET['week'] ?? '';

$where = [];
if ($filter_doctor) {
    $where[] = "ds.doctor_id = '$filter_doctor'";
}
if ($filter_date !== '') {
    $fd = mysqli_real_escape_string($conn, $filter_date);
    $where[] = "ds.shift_date = '$fd'";
}
if ($filter_week !== '') {
    // filter_week is a date — show the Mon–Sun week it belongs to
    $fw = mysqli_real_escape_string($conn, $filter_week);
    $where[] = "YEARWEEK(ds.shift_date, 1) = YEARWEEK('$fw', 1)";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Pagination ─────────────────────────────────────────────
$per_page    = 20;
$page        = max(1, intval($_GET['page'] ?? 1));
$offset      = ($page - 1) * $per_page;

$total       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM doctor_shifts ds $where_sql"))['t'] ?? 0;
$total_pages = max(1, ceil($total / $per_page));

$shifts = mysqli_query($conn, "
    SELECT ds.*,
           a.full_name AS doctor_name
    FROM doctor_shifts ds
    INNER JOIN admins a ON ds.doctor_id = a.id
    $where_sql
    ORDER BY ds.shift_date DESC, ds.shift_start ASC
    LIMIT $per_page OFFSET $offset
");

// Doctors list for filter dropdown
$doctors = mysqli_query($conn, "SELECT id, full_name FROM admins WHERE role IN ('doctor','admin') AND is_active=1 ORDER BY full_name ASC");

// Quick stats
$today       = date('Y-m-d');
$on_duty     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM doctor_shifts WHERE shift_date='$today'"))['t'] ?? 0;
$this_week   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT doctor_id) AS t FROM doctor_shifts WHERE YEARWEEK(shift_date,1)=YEARWEEK(CURDATE(),1)"))['t'] ?? 0;
$total_shifts= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM doctor_shifts"))['t'] ?? 0;

// Querystring base for pagination
$qs_parts = [];
if ($filter_doctor) $qs_parts[] = 'doctor=' . $filter_doctor;
if ($filter_date)   $qs_parts[] = 'date='   . urlencode($filter_date);
if ($filter_week)   $qs_parts[] = 'week='   . urlencode($filter_week);
$qs_base = $qs_parts ? '&' . implode('&', $qs_parts) : '';

// Shift colour helper
function shift_badge(string $type): string {
    return match($type) {
        'morning'   => '<span class="badge badge-success">Morning</span>',
        'afternoon' => '<span class="badge badge-warning">Afternoon</span>',
        'night'     => '<span class="badge badge-primary">Night</span>',
        default     => '<span class="badge">' . htmlspecialchars($type) . '</span>',
    };
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Doctor Schedule</div>
                <div class="page-subtitle">Shift management and rota</div>
            </div>
            <?php if(has_role(['admin'])): ?>
            <a href="add_shift.php" class="btn btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Shift
            </a>
            <?php endif; ?>
        </div>

        <!-- Stats -->
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-icon teal">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <div class="stat-label">On Duty Today</div>
                    <div class="stat-value"><?php echo $on_duty; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div class="stat-label">Doctors This Week</div>
                    <div class="stat-value"><?php echo $this_week; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <div class="stat-label">Total Shifts Logged</div>
                    <div class="stat-value"><?php echo number_format($total_shifts); ?></div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card card-body" style="margin-bottom:20px;">
            <form method="GET" class="filter-row">
                <div class="filter-group">
                    <label class="form-label">Doctor</label>
                    <select name="doctor" class="form-control">
                        <option value="">All doctors</option>
                        <?php
                        mysqli_data_seek($doctors, 0);
                        while($d = mysqli_fetch_assoc($doctors)):
                        ?>
                        <option value="<?php echo $d['id']; ?>" <?php if($filter_doctor==$d['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($d['full_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="form-label">Specific Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>
                <div class="filter-group">
                    <label class="form-label">Week of</label>
                    <input type="date" name="week" class="form-control" value="<?php echo htmlspecialchars($filter_week); ?>">
                </div>
                <div class="filter-group filter-actions">
                    <label class="form-label">&nbsp;</label>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="view_schedule.php" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <?php if($total > 0): ?>
        <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:10px;">
            Showing <?php echo number_format(min($offset+1,$total)); ?>–<?php echo number_format(min($offset+$per_page,$total)); ?> of <?php echo number_format($total); ?> shifts
        </div>
        <?php endif; ?>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Time</th>
                        <th>Department</th>
                        <th>Notes</th>
                        <?php if(has_role(['admin'])): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if(!$shifts || mysqli_num_rows($shifts) == 0): ?>
                    <tr><td colspan="<?php echo has_role(['admin']) ? 7 : 6; ?>">
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <h3>No shifts found</h3>
                            <p><?php echo ($filter_doctor||$filter_date||$filter_week) ? 'Try adjusting your filters' : 'Add the first shift to begin scheduling'; ?></p>
                        </div>
                    </td></tr>
                <?php else: ?>
                <?php while($s = mysqli_fetch_assoc($shifts)):
                    $is_today = ($s['shift_date'] === $today);
                ?>
                    <tr style="<?php echo $is_today ? 'background:#f0f9f6;' : ''; ?>">
                        <td>
                            <div class="patient-name"><?php echo htmlspecialchars($s['doctor_name']); ?></div>
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:.875rem;"><?php echo date('D, d M Y', strtotime($s['shift_date'])); ?></div>
                            <?php if($is_today): ?><span class="badge badge-success" style="margin-top:3px;">Today</span><?php endif; ?>
                        </td>
                        <td><?php echo shift_badge($s['shift_type']); ?></td>
                        <td style="font-family:var(--font-mono);font-size:.82rem;">
                            <?php echo htmlspecialchars(substr($s['shift_start'],0,5)); ?> – <?php echo htmlspecialchars(substr($s['shift_end'],0,5)); ?>
                        </td>
                        <td style="font-size:.85rem;"><?php echo $s['department'] ? htmlspecialchars($s['department']) : '<span style="color:var(--text-muted);">—</span>'; ?></td>
                        <td style="font-size:.82rem;color:var(--text-muted);max-width:180px;"><?php echo $s['notes'] ? htmlspecialchars(substr($s['notes'],0,60)) : '—'; ?></td>
                        <?php if(has_role(['admin'])): ?>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="edit_shift.php?id=<?php echo $s['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_shift.php?id=<?php echo $s['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this shift?')">Delete</a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?><a href="?page=<?php echo $page-1; ?><?php echo $qs_base; ?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></a><?php endif; ?>
            <?php $start=max(1,$page-2);$end=min($total_pages,$page+2);
            if($start>1) echo '<span class="page-ellipsis">…</span>';
            for($i=$start;$i<=$end;$i++): ?><a href="?page=<?php echo $i;?><?php echo $qs_base;?>" class="page-btn <?php if($i==$page) echo 'active';?>"><?php echo $i;?></a><?php endfor;
            if($end<$total_pages) echo '<span class="page-ellipsis">…</span>'; ?>
            <?php if($page < $total_pages): ?><a href="?page=<?php echo $page+1; ?><?php echo $qs_base; ?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></a><?php endif; ?>
        </div>
        <?php endif; ?>

    </main>
</div>
<?php include("../includes/footer.php"); ?>
