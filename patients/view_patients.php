<?php
include("../config/session.php");
include("../config/database.php");

// ── FILTERS ───────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$gender = $_GET['gender'] ?? '';
$blood  = $_GET['blood']  ?? '';

$where = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where[] = "(full_name LIKE '%$s%' OR patient_code LIKE '%$s%' OR phone LIKE '%$s%')";
}
if ($gender !== '') {
    $g = mysqli_real_escape_string($conn, $gender);
    $where[] = "gender = '$g'";
}
if ($blood !== '') {
    $b = mysqli_real_escape_string($conn, $blood);
    $where[] = "blood_group = '$b'";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// ── PAGINATION ────────────────────────────────────────────
$per_page    = 15;
$page        = max(1, intval($_GET['page'] ?? 1));
$offset      = ($page - 1) * $per_page;

$total       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM patients $where_sql"))['t'];
$total_pages = max(1, ceil($total / $per_page));

$result = mysqli_query($conn, "SELECT * FROM patients $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset");

$qs_parts = [];
if ($search) $qs_parts[] = "search=" . urlencode($search);
if ($gender) $qs_parts[] = "gender=" . urlencode($gender);
if ($blood)  $qs_parts[] = "blood="  . urlencode($blood);
$qs_base = $qs_parts ? '&' . implode('&', $qs_parts) : '';
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <div class="page-title">Patients</div>
                <div class="page-subtitle">
                    <?php echo number_format($total); ?> patient<?php echo $total != 1 ? 's' : ''; ?> registered
                </div>
            </div>
            <a href="add_patient.php" class="btn btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Patient
            </a>
        </div>

        <!-- Filters -->
        <div class="card card-body" style="margin-bottom:20px;">
            <form method="GET" class="filter-row">
                <div class="filter-group" style="flex:2;">
                    <label class="form-label">Search</label>
                    <div class="search-wrap">
                        <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="search" class="form-control search-input" placeholder="Name, code or phone…" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="filter-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">All</option>
                        <option value="Male"   <?php if($gender==='Male')   echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if($gender==='Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="form-label">Blood Group</label>
                    <select name="blood" class="form-control">
                        <option value="">All</option>
                        <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                        <option value="<?php echo $bg; ?>" <?php if($blood===$bg) echo 'selected'; ?>><?php echo $bg; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group filter-actions">
                    <label class="form-label">&nbsp;</label>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="view_patients.php" class="btn btn-outline">Clear</a>
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
                        <th>Gender</th>
                        <th>DOB</th>
                        <th>Phone</th>
                        <th>Blood</th>
                        <th>Emergency</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <h3><?php echo $search||$gender||$blood ? 'No patients match your filters' : 'No patients yet'; ?></h3>
                            <p><?php echo $search||$gender||$blood ? 'Try adjusting your search' : 'Register the first patient to get started'; ?></p>
                        </div>
                    </td></tr>
                <?php else: ?>
                <?php while($p = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <div class="patient-name"><?php echo htmlspecialchars($p['full_name']); ?></div>
                            <div class="patient-code"><?php echo htmlspecialchars($p['patient_code']); ?></div>
                        </td>
                        <td>
                            <span class="badge <?php echo $p['gender']==='Male' ? 'badge-blue' : 'badge-primary'; ?>">
                                <?php echo htmlspecialchars($p['gender']); ?>
                            </span>
                        </td>
                        <td style="font-size:.83rem;color:var(--text-muted);"><?php echo $p['date_of_birth'] ?: '—'; ?></td>
                        <td style="font-family:var(--font-mono);font-size:.82rem;"><?php echo htmlspecialchars($p['phone'] ?: '—'); ?></td>
                        <td>
                            <?php if($p['blood_group']): ?>
                                <span class="badge badge-danger"><?php echo htmlspecialchars($p['blood_group']); ?></span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.8rem;color:var(--text-muted);"><?php echo htmlspecialchars($p['emergency_contact'] ?: '—'); ?></td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <a href="view_patient.php?id=<?php echo $p['id']; ?>" class="btn btn-outline btn-sm">Profile</a>
                                <a href="edit_patient.php?id=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="../medical_records/patient_history.php?id=<?php echo $p['id']; ?>" class="btn btn-outline btn-sm">History</a>
                                <a href="delete_patient.php?id=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this patient and all their records?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?><?php echo $qs_base; ?>" class="page-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            <?php endif; ?>
            <?php
            $start = max(1, $page-2); $end = min($total_pages, $page+2);
            if($start > 1) echo '<span class="page-ellipsis">…</span>';
            for($i=$start;$i<=$end;$i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo $qs_base; ?>" class="page-btn <?php if($i==$page) echo 'active'; ?>"><?php echo $i; ?></a>
            <?php endfor;
            if($end < $total_pages) echo '<span class="page-ellipsis">…</span>'; ?>
            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?><?php echo $qs_base; ?>" class="page-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </main>
</div>
<?php include("../includes/footer.php"); ?>
