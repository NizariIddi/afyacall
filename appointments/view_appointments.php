<?php
include("../config/session.php");
include("../config/database.php");

if (isset($_GET['complete'])) {
    $id = intval($_GET['complete']);
    mysqli_query($conn, "UPDATE appointments SET status='Completed' WHERE id='$id'");
    header("Location: view_appointments.php"); exit();
}
if (isset($_GET['missed'])) {
    $id = intval($_GET['missed']);
    mysqli_query($conn, "UPDATE appointments SET status='Missed' WHERE id='$id'");
    header("Location: view_appointments.php"); exit();
}

// ── FILTERS ───────────────────────────────────────────────
$search    = trim($_GET['search'] ?? '');
$status    = $_GET['status']      ?? '';
$date_from = $_GET['date_from']   ?? '';
$date_to   = $_GET['date_to']     ?? '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where[] = "(patients.full_name LIKE '%$s%' OR patients.patient_code LIKE '%$s%')";
}
if ($status !== '') {
    $st = mysqli_real_escape_string($conn, $status);
    $where[] = "appointments.status = '$st'";
}
if ($date_from !== '') {
    $df = mysqli_real_escape_string($conn, $date_from);
    $where[] = "DATE(appointments.appointment_date) >= '$df'";
}
if ($date_to !== '') {
    $dt = mysqli_real_escape_string($conn, $date_to);
    $where[] = "DATE(appointments.appointment_date) <= '$dt'";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// ── PAGINATION ────────────────────────────────────────────
$per_page    = 15;
$page        = max(1, intval($_GET['page'] ?? 1));
$offset      = ($page - 1) * $per_page;

$base        = "FROM appointments INNER JOIN patients ON appointments.patient_id = patients.id $where_sql";
$total       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t $base"))['t'];
$total_pages = max(1, ceil($total / $per_page));

$result = mysqli_query($conn, "SELECT appointments.*, patients.full_name, patients.patient_code $base ORDER BY appointment_date ASC LIMIT $per_page OFFSET $offset");

// Status summary
$pending   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE status='Pending'"))['t'];
$completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE status='Completed'"))['t'];
$missed    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM appointments WHERE status='Missed'"))['t'];

$qs_parts = [];
if ($search)    $qs_parts[] = "search="    . urlencode($search);
if ($status)    $qs_parts[] = "status="    . urlencode($status);
if ($date_from) $qs_parts[] = "date_from=" . urlencode($date_from);
if ($date_to)   $qs_parts[] = "date_to="   . urlencode($date_to);
$qs_base = $qs_parts ? '&' . implode('&', $qs_parts) : '';
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Appointments</div>
                <div class="page-subtitle">Schedule and manage patient appointments</div>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="calendar.php" class="btn btn-outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Calendar
                </a>
                <a href="add_appointment.php" class="btn btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Schedule
                </a>
            </div>
        </div>

        <!-- Status summary pills -->
        <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
            <a href="?status=" class="status-pill <?php if(!$status) echo 'active'; ?>">All <span><?php echo $pending+$completed+$missed; ?></span></a>
            <a href="?status=Pending" class="status-pill warning <?php if($status==='Pending') echo 'active'; ?>">Pending <span><?php echo $pending; ?></span></a>
            <a href="?status=Completed" class="status-pill success <?php if($status==='Completed') echo 'active'; ?>">Completed <span><?php echo $completed; ?></span></a>
            <a href="?status=Missed" class="status-pill danger <?php if($status==='Missed') echo 'active'; ?>">Missed <span><?php echo $missed; ?></span></a>
        </div>

        <!-- Filters -->
        <div class="card card-body" style="margin-bottom:20px;">
            <form method="GET" class="filter-row">
                <?php if($status): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>"><?php endif; ?>
                <div class="filter-group" style="flex:2;">
                    <label class="form-label">Search Patient</label>
                    <div class="search-wrap">
                        <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" class="form-control search-input" placeholder="Name or patient code…" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="filter-group">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="filter-group">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="filter-group filter-actions">
                    <label class="form-label">&nbsp;</label>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="view_appointments.php" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <?php if($total > 0): ?>
        <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:10px;">
            Showing <?php echo number_format(min($offset+1,$total)); ?>–<?php echo number_format(min($offset+$per_page,$total)); ?> of <?php echo number_format($total); ?>
        </div>
        <?php endif; ?>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Date &amp; Time</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/></svg>
                            <h3><?php echo $search||$status||$date_from||$date_to ? 'No appointments match your filters' : 'No appointments yet'; ?></h3>
                            <p>Try adjusting your filters or schedule a new appointment</p>
                        </div>
                    </td></tr>
                <?php else: ?>
                <?php while($a = mysqli_fetch_assoc($result)):
                    $s = $a['status'];
                    $cls = $s==='Completed' ? 'badge-success' : ($s==='Missed' ? 'badge-danger' : 'badge-warning');
                    $is_past = strtotime($a['appointment_date']) < time();
                ?>
                    <tr <?php if($is_past && $s==='Pending') echo 'style="background:#fffdf0;"'; ?>>
                        <td>
                            <a href="../patients/view_patient.php?id=<?php echo $a['patient_id']; ?>" style="text-decoration:none;">
                                <div class="patient-name"><?php echo htmlspecialchars($a['full_name']); ?></div>
                                <div class="patient-code"><?php echo htmlspecialchars($a['patient_code']); ?></div>
                            </a>
                        </td>
                        <td style="font-size:.875rem;white-space:nowrap;">
                            <?php echo htmlspecialchars($a['appointment_date']); ?>
                            <?php if($is_past && $s==='Pending'): ?>
                                <div style="font-size:.7rem;color:var(--danger);margin-top:2px;">Overdue</div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars($s); ?></span></td>
                        <td style="max-width:200px;font-size:.83rem;color:var(--text-muted);"><?php echo $a['remarks'] ? htmlspecialchars($a['remarks']) : '—'; ?></td>
                        <td>
                            <?php if($s === 'Pending'): ?>
                            <div style="display:flex;gap:6px;">
                                <a href="?complete=<?php echo $a['id']; ?><?php echo $qs_base ? '&'.ltrim($qs_base,'&') : ''; ?>" class="btn btn-success btn-sm">✓ Done</a>
                                <a href="?missed=<?php echo $a['id']; ?><?php echo $qs_base ? '&'.ltrim($qs_base,'&') : ''; ?>" class="btn btn-danger btn-sm">✗ Miss</a>
                            </div>
                            <?php else: ?>
                            <span style="color:var(--text-muted);font-size:.82rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?><a href="?page=<?php echo $page-1;?><?php echo $qs_base;?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></a><?php endif; ?>
            <?php $start=max(1,$page-2);$end=min($total_pages,$page+2);
            if($start>1) echo '<span class="page-ellipsis">…</span>';
            for($i=$start;$i<=$end;$i++): ?><a href="?page=<?php echo $i;?><?php echo $qs_base;?>" class="page-btn <?php if($i==$page) echo 'active';?>"><?php echo $i;?></a><?php endfor;
            if($end<$total_pages) echo '<span class="page-ellipsis">…</span>'; ?>
            <?php if($page < $total_pages): ?><a href="?page=<?php echo $page+1;?><?php echo $qs_base;?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></a><?php endif; ?>
        </div>
        <?php endif; ?>

    </main>
</div>
<?php include("../includes/footer.php"); ?>
