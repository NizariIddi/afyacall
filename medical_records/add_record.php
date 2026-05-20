<?php
include("../config/session.php");
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

if (!can('add_record')) { include("../includes/403.php"); exit(); }

$success = "";
$patients_result = mysqli_query($conn, "SELECT * FROM patients ORDER BY full_name ASC");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $patient_id       = clean_int($_POST['patient_id'] ?? 0);
    $diagnosis        = db_clean($conn, $_POST['diagnosis'] ?? '');
    $medication       = db_clean($conn, $_POST['medication'] ?? '');
    $allergies        = db_clean($conn, $_POST['allergies'] ?? '');
    $notes            = db_clean($conn, $_POST['notes'] ?? '');
    $visit_date       = clean_date($_POST['visit_date'] ?? '');
    $next_appointment = clean_date($_POST['next_appointment'] ?? '');
    $recorded_by      = intval($_SESSION['admin_id']);

    if (!$patient_id || !$diagnosis || !$visit_date) {
        $error = "Patient, diagnosis, and visit date are required.";
    } else {
        mysqli_query($conn, "INSERT INTO medical_records (patient_id,diagnosis,medication,allergies,notes,visit_date,next_appointment,recorded_by)
            VALUES ('$patient_id','$diagnosis','$medication','$allergies','$notes','$visit_date','$next_appointment','$recorded_by')");
        $rid = mysqli_insert_id($conn);
        log_activity($conn, "Added medical record for patient #$patient_id", 'records', $rid);
        $success = "Medical record saved successfully.";
    }
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Add Medical Record</div>
                <div class="page-subtitle">Record a new clinical visit</div>
            </div>
            <a href="view_records.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Records
            </a>
        </div>
        <?php if(!empty($error)): ?><div class="alert alert-danger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo $success; ?></div><?php endif; ?>

        <div class="card card-body">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label">Patient <span style="color:var(--danger);">*</span></label>
                    <select name="patient_id" class="form-control" required>
                        <option value="">— Select patient —</option>
                        <?php while($p = mysqli_fetch_assoc($patients_result)): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?> (<?php echo htmlspecialchars($p['patient_code']); ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Diagnosis <span style="color:var(--danger);">*</span></label>
                    <textarea name="diagnosis" class="form-control" rows="3" placeholder="Describe the diagnosis…" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Medication Prescribed</label>
                    <textarea name="medication" class="form-control" rows="3" placeholder="List medications and dosages…"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Known Allergies</label>
                        <textarea name="allergies" class="form-control" rows="2" placeholder="e.g. Penicillin, Sulfa drugs…"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Clinical Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Additional observations…"></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Visit Date <span style="color:var(--danger);">*</span></label>
                        <input type="date" name="visit_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Next Appointment</label>
                        <input type="date" name="next_appointment" class="form-control">
                    </div>
                </div>
                <div style="border-top:1px solid var(--border);padding-top:20px;display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Record
                    </button>
                    <a href="view_records.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
