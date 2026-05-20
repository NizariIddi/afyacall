<?php
/**
 * exports.php — Phase 5
 * Central export hub: patients CSV, records CSV, appointments CSV.
 */
include("../config/session.php");
include("../config/database.php");

$doctors = mysqli_query($conn, "SELECT id, full_name FROM admins WHERE is_active=1 ORDER BY full_name ASC");
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
<?php include("../includes/sidebar.php"); ?>
<main class="main-content">

<div class="page-header">
    <div>
        <div class="page-title">Export Data</div>
        <div class="page-subtitle">Download records as Excel-compatible CSV files</div>
    </div>
</div>

<!-- Export: Patients -->
<div class="card card-body" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
        <div style="width:40px;height:40px;background:#e6f4f4;border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
            <div style="font-weight:700;font-size:.95rem;">Patient List</div>
            <div style="font-size:.8rem;color:var(--text-muted);">All registered patients with demographics</div>
        </div>
    </div>
    <form action="export_patients.php" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="filter-group">
            <label class="form-label">Registered From</label>
            <input type="date" name="date_from" class="form-control">
        </div>
        <div class="filter-group">
            <label class="form-label">Registered To</label>
            <input type="date" name="date_to" class="form-control">
        </div>
        <div class="filter-group">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-control">
                <option value="">All genders</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="form-label">Blood Group</label>
            <select name="blood" class="form-control">
                <option value="">All</option>
                <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                <option value="<?php echo $bg; ?>"><?php echo $bg; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group" style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Patients CSV
            </button>
        </div>
    </form>
</div>

<!-- Export: Medical Records -->
<div class="card card-body" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
        <div style="width:40px;height:40px;background:#e6f9f1;border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2db57a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div>
            <div style="font-weight:700;font-size:.95rem;">Medical Records</div>
            <div style="font-size:.8rem;color:var(--text-muted);">Diagnoses, medications, visit history</div>
        </div>
    </div>
    <form action="export_records.php" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="filter-group">
            <label class="form-label">Visit From</label>
            <input type="date" name="date_from" class="form-control">
        </div>
        <div class="filter-group">
            <label class="form-label">Visit To</label>
            <input type="date" name="date_to" class="form-control">
        </div>
        <div class="filter-group">
            <label class="form-label">Doctor</label>
            <select name="doctor" class="form-control">
                <option value="">All doctors</option>
                <?php while($d = mysqli_fetch_assoc($doctors)): ?>
                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['full_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="filter-group" style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Records CSV
            </button>
        </div>
    </form>
</div>

<!-- Export: Appointments -->
<div class="card card-body" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
        <div style="width:40px;height:40px;background:#fff4e0;border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f4a235" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div>
            <div style="font-weight:700;font-size:.95rem;">Appointments</div>
            <div style="font-size:.8rem;color:var(--text-muted);">All appointments with status</div>
        </div>
    </div>
    <form action="export_appointments.php" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="filter-group">
            <label class="form-label">Date From</label>
            <input type="date" name="date_from" class="form-control">
        </div>
        <div class="filter-group">
            <label class="form-label">Date To</label>
            <input type="date" name="date_to" class="form-control">
        </div>
        <div class="filter-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">All statuses</option>
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
                <option value="Missed">Missed</option>
            </select>
        </div>
        <div class="filter-group" style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Appointments CSV
            </button>
        </div>
    </form>
</div>

<!-- Info note -->
<div class="alert" style="background:#f0f4f8;border:1px solid var(--border);color:var(--text-muted);font-size:.83rem;display:flex;gap:10px;align-items:flex-start;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <span>Files download as <strong>.csv</strong> — open directly in Microsoft Excel, Google Sheets, or LibreOffice Calc. Leave all filters empty to export the full dataset.</span>
</div>

</main>
</div>
<?php include("../includes/footer.php"); ?>
