<?php
include("../config/session.php");
include("../config/database.php");
include("../config/csrf.php");
include("../config/sanitize.php");
include("../config/activity_log.php");

if (!can('edit_record')) { include("../includes/403.php"); exit(); }

$id     = clean_int($_GET['id'] ?? 0);
$result = mysqli_query($conn, "SELECT * FROM medical_records WHERE id='$id' LIMIT 1");
if (!mysqli_num_rows($result)) { header("Location: view_records.php"); exit(); }
$record = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $diagnosis        = db_clean($conn, $_POST['diagnosis'] ?? '');
    $medication       = db_clean($conn, $_POST['medication'] ?? '');
    $allergies        = db_clean($conn, $_POST['allergies'] ?? '');
    $notes            = db_clean($conn, $_POST['notes'] ?? '');
    $next_appointment = clean_date($_POST['next_appointment'] ?? '');

    mysqli_query($conn, "UPDATE medical_records SET diagnosis='$diagnosis',medication='$medication',allergies='$allergies',notes='$notes',next_appointment='$next_appointment' WHERE id='$id'");
    log_activity($conn, "Updated medical record #$id", 'records', $id);
    header("Location: view_records.php");
    exit();
}
?>
<?php include("../includes/header.php"); ?>
<div class="app-wrapper">
    <?php include("../includes/sidebar.php"); ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Edit Medical Record</div>
                <div class="page-subtitle">Update clinical information</div>
            </div>
            <a href="view_records.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Records
            </a>
        </div>
        <div class="card card-body">
            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label">Diagnosis</label>
                    <textarea name="diagnosis" class="form-control" rows="3"><?php echo htmlspecialchars($record['diagnosis']); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Medication Prescribed</label>
                    <textarea name="medication" class="form-control" rows="3"><?php echo htmlspecialchars($record['medication']); ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Known Allergies</label>
                        <textarea name="allergies" class="form-control" rows="2"><?php echo htmlspecialchars($record['allergies']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Clinical Notes</label>
                        <textarea name="notes" class="form-control" rows="2"><?php echo htmlspecialchars($record['notes']); ?></textarea>
                    </div>
                </div>
                <div class="form-group" style="max-width:320px;">
                    <label class="form-label">Next Appointment</label>
                    <input type="date" name="next_appointment" class="form-control" value="<?php echo htmlspecialchars($record['next_appointment']); ?>">
                </div>
                <div style="border-top:1px solid var(--border);padding-top:20px;display:flex;gap:10px;">
                    <button type="submit" class="btn btn-success btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Update Record
                    </button>
                    <a href="view_records.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include("../includes/footer.php"); ?>
