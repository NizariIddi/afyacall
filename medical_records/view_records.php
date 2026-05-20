<?php
include("../config/session.php");
include("../config/database.php");

// ── FILTERS ───────────────────────────────────────────────
$search    = trim($_GET['search']  ?? '');
$date_from = $_GET['date_from']    ?? '';
$date_to   = $_GET['date_to']      ?? '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where[] = "(patients.full_name LIKE '%$s%' OR patients.patient_code LIKE '%$s%' OR medical_records.diagnosis LIKE '%$s%')";
}
if ($date_from !== '') {
    $df = mysqli_real_escape_string($conn, $date_from);
    $where[] = "medical_records.visit_date >= '$df'";
}
if ($date_to !== '') {
    $dt = mysqli_real_escape_string($conn, $date_to);
    $where[] = "medical_records.visit_date <= '$dt'";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// ── PAGINATION ────────────────────────────────────────────
$per_page    = 15;
$page        = max(1, intval($_GET['page'] ?? 1));
$offset      = ($page - 1) * $per_page;

$base_query  = "FROM medical_records INNER JOIN patients ON medical_records.patient_id = patients.id INNER JOIN admins ON medical_records.recorded_by = admins.id $where_sql";
$total       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t $base_query"))['t'];
$total_pages = max(1, ceil($total / $per_page));

$result = mysqli_query($conn, "SELECT medical_records.*, patients.full_name, patients.patient_code, admins.full_name AS doctor_name $base_query ORDER BY medical_records.id DESC LIMIT $per_page OFFSET $offset");

$qs_parts = [];
if ($search)    $qs_parts[] = "search="    . urlencode($search);
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
                <div class="page-title">Medical Records</div>
                <div class="page-subtitle">Clinical history and diagnoses</div>
            </div>
            <a href="add_record.php" class="btn btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Record
            </a>
        </div>

        <!-- Filters -->
        <div class="card card-body" style="margin-bottom:20px;">
            <form method="GET" class="filter-row">
                <div class="filter-group" style="flex:2;">
                    <label class="form-label">Search</label>
                    <div class="search-wrap">
                        <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" class="form-control search-input" placeholder="Patient name, code or diagnosis…" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="filter-group">
                    <label class="form-label">Visit From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="filter-group">
                    <label class="form-label">Visit To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="filter-group filter-actions">
                    <label class="form-label">&nbsp;</label>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="view_records.php" class="btn btn-outline">Clear</a>
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
                        <th>Diagnosis</th>
                        <th>Medication</th>
                        <th>Visit Date</th>
                        <th>Next Appt.</th>
                        <th>Recorded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                            <h3><?php echo $search||$date_from||$date_to ? 'No records match your filters' : 'No medical records yet'; ?></h3>
                            <p><?php echo $search||$date_from||$date_to ? 'Try adjusting your search' : 'Add the first record to begin tracking patient history'; ?></p>
                        </div>
                    </td></tr>
                <?php else: ?>
                <?php while($r = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <a href="../patients/view_patient.php?id=<?php echo $r['patient_id']; ?>" style="text-decoration:none;">
                                <div class="patient-name"><?php echo htmlspecialchars($r['full_name']); ?></div>
                                <div class="patient-code"><?php echo htmlspecialchars($r['patient_code']); ?></div>
                            </a>
                        </td>
                        <td style="font-weight:500;max-width:180px;"><?php echo htmlspecialchars($r['diagnosis']); ?></td>
                        <td style="font-size:.83rem;max-width:150px;color:var(--text-muted);"><?php echo $r['medication'] ? htmlspecialchars($r['medication']) : '—'; ?></td>
                        <td style="font-size:.83rem;white-space:nowrap;"><?php echo htmlspecialchars($r['visit_date']); ?></td>
                        <td style="font-size:.83rem;"><?php echo $r['next_appointment'] ? htmlspecialchars($r['next_appointment']) : '<span style="color:var(--text-muted);">—</span>'; ?></td>
                        <td><span style="font-size:.8rem;color:var(--text-muted);"><?php echo htmlspecialchars($r['doctor_name']); ?></span></td>
                        <td>
                            <a href="edit_record.php?id=<?php echo $r['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="../patients/print_prescription.php?id=<?php echo $r['id']; ?>" class="btn btn-outline btn-sm" target="_blank">Rx</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

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
