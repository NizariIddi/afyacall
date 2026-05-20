<?php
include("../config/session.php");
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

if (!can('add_appointment')) { include("../includes/403.php"); exit(); }

$success = "";
$patients_result = mysqli_query($conn, "SELECT * FROM patients ORDER BY full_name ASC");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $patient_id       = clean_int($_POST['patient_id'] ?? 0);
    $appointment_date = clean_datetime($_POST['appointment_date'] ?? '');
    $remarks          = db_clean($conn, $_POST['remarks'] ?? '');

    if (!$patient_id || !$appointment_date) {
        $error = "Patient and appointment date/time are required.";
    } else {
        mysqli_query($conn, "INSERT INTO appointments (patient_id,appointment_date,remarks,status) VALUES ('$patient_id','$appointment_date','$remarks','Pending')");
        $aid = mysqli_insert_id($conn);
        log_activity($conn, "Scheduled appointment for patient #$patient_id", 'appointments', $aid);
        $success = "Appointment scheduled successfully.";
    }
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Schedule Appointment</div>
                <div class="page-subtitle">Book a new patient appointment</div>
            </div>
            <a href="view_appointments.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Appointments
            </a>
        </div>
        <?php if(!empty($error)): ?><div class="alert alert-danger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo $success; ?></div><?php endif; ?>

        <div class="card card-body" style="max-width:680px;">
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
                    <label class="form-label">Date &amp; Time <span style="color:var(--danger);">*</span></label>
                    <input type="datetime-local" name="appointment_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Remarks / Reason for Visit</label>
                    <textarea name="remarks" class="form-control" rows="4" placeholder="Describe the reason for this appointment…"></textarea>
                </div>
                <div style="border-top:1px solid var(--border);padding-top:20px;display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Appointment
                    </button>
                    <a href="view_appointments.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
